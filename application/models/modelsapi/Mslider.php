<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mslider extends CI_Model {
    
    function get_all()
    {
        $this->db->order_by("slider_tgl_post", "DESC");
        $query = $this->db->get("slider");
        return $query->result_array();
    }

    function insert($data)
    {
        $this->db->insert('slider', $data);
    }

    function get_by_id($kode)
    {
        $this->db->where('slider_id', $kode);
        return $this->db->get("slider")->row();
    }

    function update($kode, $data)
    {
        $this->db->where("slider_id", $kode);
        $this->db->update("slider", $data);
    }

    function delete($kode)
    {
        $this->db->where("slider_id", $kode);
        $this->db->delete("slider");
    }

}

/* End of file Login_model.php */
/* Location: ./application/models/Login_model.php */