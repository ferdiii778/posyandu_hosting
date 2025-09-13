<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mstandarbbpbperempuan extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_standar_bb_pb_0_24_perempuan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_standar_bb_pb_0_24_perempuan', ['standar_bb_pb_0_24_perempuan_id' => $id])->row();
    }
}
