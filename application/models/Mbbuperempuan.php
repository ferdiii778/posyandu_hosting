<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbbuperempuan extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_bb_u_perempuan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_bb_u_perempuan', ['bb_u_perempuan_id' => $id])->row();
    }
}
