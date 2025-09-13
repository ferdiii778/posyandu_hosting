<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mvitamin extends CI_Model {

    public function getAll() {
        return $this->db->get('jenis_vitamin')->result();
    }

    public function getById($id) {
        return $this->db->get_where('jenis_vitamin', ['id_jenis_vitamin' => $id])->row();
    }
}
