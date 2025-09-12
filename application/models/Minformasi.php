<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Minformasi extends CI_Model {

    public function getAll() {
        return $this->db->get('informasi')->result();
    }

    public function getById($id) {
        return $this->db->get_where('informasi', ['id_informasi' => $id])->row();
    }
}
