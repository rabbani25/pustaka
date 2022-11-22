public function cetak_laporan_buku()
{
    $data['buku']  = $this->ModelBuku->getBuku()->result_array();
    $data['katagori'] = $this->ModelBuku->getKatagori()->result_array();

    $this->load->view('buku/laporan_print_buku', $data);
}