<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mjadwal extends CI_Model {

    public function getAll() {
        return $this->db->get('jadwal_pemeriksaan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('jadwal_pemeriksaan', ['id_jadwal_pemeriksaan' => $id])->row();
    }
}
