<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mstandarimtu518perempuan extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_standar_imt_u_5_18_perempuan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_standar_imt_u_5_18_perempuan', ['standar_imt_u_5_18_perempuan_id' => $id])->row();
    }
}
