<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mlayanan extends CI_Model {
    
    // Mengambil semua data dalam bentuk Array (Sangat cocok untuk JSON API)
    function get_all()
    {
        $this->db->order_by("layanan_id", "DESC");
        $query = $this->db->get("layanan");
        return $query->result_array(); 
    }

    function insert($data)
    {
        return $this->db->insert('layanan', $data);
    }

    function get_by_id($kode)
    {
        $this->db->where('layanan_id', $kode);
        // Menggunakan result_array() agar konsisten dengan get_all 
        // atau tetap row() jika hanya butuh satu objek.
        return $this->db->get("layanan")->row(); 
    }

    function update($kode, $data)
    {
        $this->db->where("layanan_id", $kode);
        return $this->db->update("layanan", $data);
    }

    function delete($kode)
    {
        $this->db->where("layanan_id", $kode);
        return $this->db->delete("layanan");
    }
}