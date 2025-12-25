<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbbulaki extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_bb_u_laki')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_bb_u_laki', ['bb_u_laki_id' => $id])->row();
    }
}
