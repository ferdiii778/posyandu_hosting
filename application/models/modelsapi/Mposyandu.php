<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mposyandu extends CI_Model {
    
    function get_all()
    {
        $query = $this->db->get("ref_posyandu");
        return $query->result_array();
    }

    function insert($data)
    {
        $this->db->insert('ref_posyandu', $data);
    }

    function get_by_id($kode)
    {
        $this->db->where('posyandu_id', $kode);
        return $this->db->get("ref_posyandu")->row();
    }

    function update($kode, $data)
    {
        $this->db->where("posyandu_id", $kode);
        $this->db->update("ref_posyandu", $data);
    }

    function delete($kode)
    {
        $this->db->where("posyandu_id", $kode);
        $this->db->delete("ref_posyandu");
    }

}

/* End of file Login_model.php */
/* Location: ./application/models/Login_model.php */