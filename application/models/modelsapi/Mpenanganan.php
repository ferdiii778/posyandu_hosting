<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mpenanganan extends CI_Model {
    
    function get_all()
    {
        $this->db->order_by('treatment_id', 'ASC');
        $query = $this->db->get("treatment");
        return $query->result_array();
    }

    function insert($data)
    {
        $this->db->insert('treatment', $data);
        // Tambahkan return ID agar Controller bisa memberi respon sukses
        return $this->db->insert_id(); 
    }

    function get_by_id($kode)
    {
        $this->db->where('treatment_id', $kode);
        return $this->db->get("treatment")->row();
    }

    function update($kode, $data)
    {
        $this->db->where("treatment_id", $kode);
        // Return true/false untuk status update
        return $this->db->update("treatment", $data);
    }

    function delete($kode)
    {
        $this->db->where("treatment_id", $kode);
        return $this->db->delete("treatment");
    }
}