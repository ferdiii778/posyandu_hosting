<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mchat extends CI_Model {

    public function getAll() {
        return $this->db->get('chat')->result();
    }

    public function getById($id) {
        return $this->db->get_where('chat', ['id' => $id])->row();
    }
}
