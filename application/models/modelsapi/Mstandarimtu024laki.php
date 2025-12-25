<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mstandarimtu024laki extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_standar_imt_u_0_24_laki')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_standar_imt_u_0_24_laki', ['standar_imt_u_0_24_laki_id' => $id])->row();
    }
}
