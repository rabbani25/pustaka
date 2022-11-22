<?php if (!defined('BASEPATH')) exit('No Direct Script Access Allowed');
class Pinjam extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        cek_login();
        cek_user();
    }

    public function daftarBooking()
    {
        $data['judul'] = "Daftar Booking";
        $data['user'] = $this->ModelUser->cekData(['email' => $this->session->userdata('email')])->row_array();
        $data['pinjam'] = $this->db->query("select*from booking")->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('booking/daftar-booking', $data);
        $this->load->view('templates/footer');
    }


    public function bookingDetail()
    {
        $id_booking = $this->uri->segment(3);
        $data['judul'] = "Booking Detail";
        $data['user'] = $this->ModelUser->cekData(['email' => $this->session->userdata('email')])->row_array();
        $data['agt_booking'] = $this->db->query("select*from booking b, user u where b.id_user=u.id and b.id_booking='$id_booking'")->result_array();
        $data['detail'] = $this->db->query("select id_buku,judul_buku,pengarang,penerbit,tahun_terbit from booking_detail d, buku b where d.id_buku=b.id and d.id_booking='$id_booking'")->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('booking/booking-detail', $data);
        $this->load->view('templates/footer');
    }
    public function index()

    {
    $data['judul'] = "Data Pinjam";
    $data['user'] = $this->ModelUser->cekData(['email' => $this->session->userdata('email')])->row_array();
    $data['pinjam'] = $this->ModelPinjam->joinData();
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('pinjam/data-pinjam', $data);
    $this->load->view('templates/footer');
    }
    public function ubahStatus()
    {
    $id_buku = $this->uri->segment(3);
    $no_pinjam = $this->uri->segment(4);
    $where = ['id_buku' => $this->uri->segment(3),];
    $tgl = date('Y-m-d');
    $status = 'Kembali';
    //update status menjadi kembali pada saat buku dikembalikan
    $this->db->query("UPDATE pinjam, detail_pinjam SET pinjam.status='$status', pinjam.tgl_pengembalian='$tgl' WHERE detail_pinjam.id_buku='$id_buku' AND pinjam.no_pinjam='$no_pinjam'");
    //update stok dan dipinjam pada tabel buku
    $this->db->query("UPDATE buku, detail_pinjam SET buku.dipinjam=buku.dipinjam-1, buku.stok=buku.stok+1 WHERE buku.id=detail_pinjam.id_buku");
    $this->session->set_flashdata('pesan', '<div class="laert alert-message alert-success" role="alert"></div>');
    redirect(base_url('pinjam'));
    }
}   
