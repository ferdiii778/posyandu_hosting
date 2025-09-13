<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Morangtua extends CI_Model {

    public function getAll() {
        return $this->db->get('orang_tua')->result();
    }

    public function getById($id) {
        return $this->db->get_where('orang_tua', ['id_orang_tua' => $id])->row();
    }
}
