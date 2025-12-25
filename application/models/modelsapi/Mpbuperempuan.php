<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mpbuperempuan extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_pb_u_perempuan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_pb_u_perempuan', ['pb_u_perempuan_id' => $id])->row();
    }
}
