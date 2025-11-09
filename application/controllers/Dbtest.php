<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dbtest extends CI_Controller {

    public function index()
    {
        // pastikan database autoload, tapi kita load ulang aja
        $this->load->database();

        echo "HOST: " . $this->db->hostname . "<br>";
        echo "DB: "   . $this->db->database . "<br>";
    }
}

