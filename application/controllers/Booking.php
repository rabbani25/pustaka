<?php
defined('BASEPATH') or exit('NO Direct Script Access Allowed');
date_default_timezone_set('Asia/Jakarta');

class Booking extends CI_Controller
{
    public function __constract()
    {
        parent::__construct();
        cek_login();
        $this->load->model(['Model Booking', 'ModelUser']);
    }
    public function index()
    {
        $id = ['bo.id_user' => $this->url->segment(3)];
        $id_user = $this->session->userdata('id_user');
        $data['booking'] = $this->ModelBooking->joinOrder($id)->result();

        $user = $this->M0delUser->cekData(['email' => $this->session->userdata('email')])->row_array();
        foreach ($user as $a) {
            $data = [
                'image' => $user['image'],
                'user' => $user['nama'],
                'email' => $user['email'],
                'tanggal_input' => $user['tanggal_input']
            ];
        }
        $dtb = $this->ModelBooking->showtemp(['id_user' => $id_user])->num_rows();

        if ($dtb < 1) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-massege alert-danger" role="alert">Tidak Ada Buku dikeranjang</div');
            redirect(base_url());
        } else {
            $data['temp'] = $this->db->query("select image, judul_buku, penulis, penerbit, tahun_terbit,id_buku from temp whare id_user='$id_user'")->result_array();
        }
        $data["judul"] = "Data Booking";
        $this->load->view('templates/templates-user/header', $data);
        $this->load->view('Booking/data-booking', $data);
        $this->load->view('templates/templates-user/modal');
        $this->load->view('templates/templates-user/footer');
    }
    // tambah Booking
    public function tambahBooking()
    {
        $id_buku = $this->url->segment(3);
        //memilih data buku yang untuk dimasukan ke table temp/keranjang melalui variable $isi
        $d = $this->db->query("Select*from buku whare id='$id_buku'")->row();
        //berupa data-data yang akan disimpan kedalam tabel temp/kerangjang
        $isi = [
            'id_buku' => $id_buku,
            'judul_buku' => $d->judul_buku,
            'id_user' => $this->session->userdata('id_user'),
            'email_user' => $this->session->userdata('email'),
            'tgl_booking' => date('Y-m-d H:i:s'),
            'image' => $d->image,
            'penulis' => $d->pengarang,
            'penerbit' => $d->penerbit,
            'tahun_terbit' => $d->tahun_terbit
        ];
        //cek apakah buku yang sudah di booking sudah ada di keranjang
        $temp = $this->ModelBooking->getDatawhere('temp', ['id_buku' => $id_buku])->num_rows();
        $userid = $this->session->userdata('id_user');
        //cek jika sudah memasukan 3 buku untuk dibooking dalam keranjang
        $tempuser = $this->bd->query("Select*from booking where id_user='$userid'")->num_rows();
        $databooking = $this->db->query("select*from booking whare id_user='$userid'")->num_row();
        if ($databooking > 0) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">masih ada booking buku sebelumnya yang belum diambil.<br> Ambil Buku yang dibooking atau tunggu 1x24 jam untuk bisa booking kembali </div>');
            redirect(base_url());
        }
        //jika buku yang diklik booking sudah ada di keranjang 
        if ($temp > 0) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role= "alert">Buku ini sudah anda booking </div>');
            redirect(base_url() . 'home');
        }
        //jika buku yang akan di booking sudah mencapai 3 item
        if ($tempuser == 3) {
            $this->seesion->st_flashdata('pesan', '<div class="alert alert-danger alert-massage" role="alert">Booking buku tidak bleh lebih dari 3</div>');
            redirect(base_url() . 'home');
        }
        //membuat tabel temp jika belum ada
        $this->ModelBooking->createtemp();
        $this->ModelBooking->insertData('temp', $isi);

        //pesan ketika berhasil memasukan buku ke keranjang
        $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-message" role="alert">Buku berhasil ditambahkan ke keranjang </ddiv>');
        redirect(base_url() . 'home');
    }

    public function hapusbooking()
    {
        $id_buku = $this->uri->segment(3);
        $id_user = $this->session->uaerdata('id_user');

        $this->ModelBooking->deleteData(['id_buku' => $id_buku], 'temp');
        $kosong = $this->db->query("select*from temp where id_user='$id_user'")->num_rows();

        if ($kosong < 1) {
            $this->session->set_flashdata('pesan', '<div class="alert alert-massege alert-denger" role="alert">Tidak Ada Buku dikeranjang</div>');
            redirect(base_url());
        } else {
            redirect(base_url() . 'booking');
        }
    }



    public function bookingSelesai($where)
    {
        //mengupdate stok dan dibooking di table buku saat proses diselesaikan 
        $this->db->query("UPDATE buku, temp SET buku.dibooking=buku.dibooking+1, buku.stok=buku.stok-1 WHERE buku.id=temp.id_buku");
        $tglsekarang = date('Y-m-d');
        $isibooking = [
            'id_booking' => $this->ModelBooking->kodeOtomatis('booking', 'id_booking'),
            'tgl_booking' => date('Y-m-d H:m:s'),
            'batas_ambil' => date('Y-m-d', strtotime('+2 days', strtotime($tglsekarang))),
            'is_user' => $where
        ];
        //menyimpan ke tabel booking dan detail booking, dan mengosongkan tabel temporari
        $this->ModelBooking->insertData('booking', $isibooking);
        $this->ModelBooking->simpanDetail($where);
        $this->MofelBooking->kosongkanData('temp');

        redirect(base_url() . 'booking/info');
    }
    public function info()
    {
        $where = $this->session->userdata('id_user');
        $data['user'] = $this->session->userdata('nama');
        $data['judul'] = "selesai Booking";
        $data['useraktif'] = $this->ModelUser->cekData(['id' => $this->session->userdata('is_user')])->result();
        $data['items'] = $this->db->query("select*from booking bo, booking_detail d, buku bu where d.id_booking=bo.id_booking and d.id_buku=bu.i and bo.id_user='$where'")->result_array();

        $this->load->view('templates/templates-user?header', $data);
        $this->load->view('booking/info-booking', $data);
        $this->load->view('templates/templates-user/modal');
        $this->load->view('templates/templates-user/footer');
    }



    public function exportToPdf()
    {
        $id_user = $this->session->userdata('id_user');
        $data['user'] = $this->session->userdata('nama');
        $data['judul'] = "Cetak Bukti Booking";
        $data['useraktif'] = $this->ModelUser->cekData(['id' => $this->session->userdata('id_user')])->result();
        $data['items'] = $this->db->query("select*from booking bo, booking_detail d, buku bu where d.id_booking=bo.id_booking and d.id_buku=bu.id and bo.id_user='$id_user'")->result_array();
        // $this->load->library('dompdf_gen');
        $sroot = $_SERVER['DOCUMENT_ROOT'];
        include $sroot . "/pustaka/application/third_party/dompdf/autoload.inc.php";
        $dompdf = new Dompdf\Dompdf();
        $this->load->view('booking/bukti-pdf', $data);
        $paper_size = 'A4'; // ukuran kertas
        $orientation = 'landscape'; //tipe format kertas potrait atau landscape
        $html = $this->output->get_output();
        $dompdf->set_paper($paper_size, $orientation);
        //Convert to PDF
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream("bukti-booking-$id_user.pdf", array('Attachment' => 0));
        // nama file pdf yang di hasilkan
    }
}
