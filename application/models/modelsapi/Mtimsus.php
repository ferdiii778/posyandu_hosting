<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TimsusModel extends CI_Model
{
    private $table = 'timsus';
    private $table_detail = 'detail_timsus';

    public function get_all()
    {
        $this->db->select('timsus.*, ref_timsus.ref_timsus_nama');
        $this->db->from($this->table);
        $this->db->join('ref_timsus', 'ref_timsus.ref_timsus_id = timsus.ref_timsus_id', 'left');
        $query = $this->db->get();
        return $query->result_array(); // Return as array, bukan object
    }

    public function get_by_id($id)
    {
        $this->db->where('timsus_id', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function update($id, $data)
    {
        $this->db->where('timsus_id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows() >= 0; // >= karena bisa jadi tidak ada perubahan
    }

    public function delete($id)
    {
        $this->db->where('timsus_id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows() > 0;
    }

    // Detail Timsus Methods
    public function get_by_id_detail_timsus($id)
    {
        $this->db->where('detail_timsus_id', $id);
        return $this->db->get($this->table_detail)->row();
    }

    public function update_pemeriksaan($id, $data)
    {
        $this->db->where('detail_timsus_id', $id);
        $this->db->update($this->table_detail, $data);
        return $this->db->affected_rows() >= 0;
    }

    public function delete_detail_timsus($id)
    {
        $this->db->where('detail_timsus_id', $id);
        $this->db->delete($this->table_detail);
        return $this->db->affected_rows() > 0;
    }
}