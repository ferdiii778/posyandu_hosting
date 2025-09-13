<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mpemeriksaan extends CI_Model {

    public function getAll() {
        return $this->db->get('pemeriksaan')->result();
    }

    public function getById($kode) {
        return $this->db->get_where('pemeriksaan', ['kode_pemeriksaan' => $kode])->row();
    }
}
