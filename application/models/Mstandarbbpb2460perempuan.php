<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mstandarbbpb2460perempuan extends CI_Model {

    public function getAll() {
        return $this->db->get('ref_standar_bb_pb_24_60_perempuan')->result();
    }

    public function getById($id) {
        return $this->db->get_where('ref_standar_bb_pb_24_60_perempuan', ['standar_bb_pb_24_60_perempuan_id' => $id])->row();
    }
}
