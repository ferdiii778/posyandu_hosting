<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbalita extends CI_Model {

    public function getAll() {
        return $this->db->get('balita')->result();
    }

    public function getById($nib) {
        return $this->db->get_where('balita', ['nib' => $nib])->row();
    }
}
