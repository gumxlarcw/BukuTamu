<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class M_selamat_datang extends CI_Model{
  
	function view() {
		return $this->db->get('tb_buku');
	}
 
	function input($data, $table) {
		$this->db->insert($table, $data);
  	}
	
	public function check() {
		$this->load->view('view_pilih_status');
	}
	
	public function recognize() {
		// Nanti di sini adalah logika scan wajah
		// sementara arahkan ke view kosong dulu
		echo "<h1 style='color:white; text-align:center; margin-top:100px;'>🚧 Halaman scan wajah dalam pengembangan...</h1>";
	}
  
}