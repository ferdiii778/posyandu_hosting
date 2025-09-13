<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mkematian extends CI_Model {

    public function getAll() {
        return $this->db->get('kematian')->result();
    }

    public function getById($id) {
        return $this->db->get_where('kematian', ['id_kematian' => $id])->row();
    }
}
