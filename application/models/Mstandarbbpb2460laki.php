<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mstandarbbpb2460laki extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_standar_bb_pb_24_60_laki')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_standar_bb_pb_24_60_laki', ['standar_bb_pb_24_60_laki_id' => $id])->row();
    }
}
