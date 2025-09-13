<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mimunisasi extends CI_Model {

    public function getAll() {
        return $this->db->get('jenis_imunisasi')->result();
    }

    public function getById($id) {
        return $this->db->get_where('jenis_imunisasi', ['id_jenis_imunisasi' => $id])->row();
    }
}
