<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mkonsultasi extends CI_Model {

    public function getAll() {
        return $this->db->get('konsultasi')->result();
    }

    public function getById($id) {
        return $this->db->get_where('konsultasi', ['id' => $id])->row();
    }
}
