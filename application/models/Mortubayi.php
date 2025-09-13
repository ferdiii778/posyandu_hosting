<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mortubayi extends CI_Model {

    public function getAll() {
        return $this->db->get('ortu_bayi')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ortu_bayi', ['id_ortu_bayi' => $id])->row();
    }
}
