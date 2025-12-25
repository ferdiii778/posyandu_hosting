<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mdetailtimsus extends CI_Model
{
    private $table = 'detail_timsus';
    private $table_timsus = 'timsus';
    private $table_ref_timsus = 'ref_timsus';
    private $table_posyandu = 'ref_posyandu';
    private $table_desa = 'ref_desa';
    private $table_kecamatan = 'ref_kecamatan';

    // Get detail timsus dengan JOIN
    public function get_detail_by_timsus_id($timsus_id)
    {
        $this->db->select('detail_timsus.*, ref_posyandu.posyandu_nama, ref_posyandu.posyandu_alamat');
        $this->db->from($this->table);
        $this->db->join($this->table_posyandu, 'ref_posyandu.posyandu_id = detail_timsus.posyandu_id', 'left');
        $this->db->where('detail_timsus.timsus_id', $timsus_id);
        $this->db->order_by('ref_posyandu.posyandu_nama', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    // Get info timsus
    public function get_timsus_info($timsus_id)
    {
        $this->db->select('timsus.*, ref_timsus.ref_timsus_nama');
        $this->db->from($this->table_timsus);
        $this->db->join($this->table_ref_timsus, 'ref_timsus.ref_timsus_id = timsus.ref_timsus_id', 'left');
        $this->db->where('timsus.timsus_id', $timsus_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    // Get all posyandu dengan kecamatan dan desa
    public function get_all_posyandu()
    {
        $this->db->select('rp.posyandu_id, rp.posyandu_nama, rp.posyandu_alamat, rp.desa_id, rd.desa_nama, rd.kecamatan_id, rk.kecamatan_nama');
        $this->db->from($this->table_posyandu . ' rp');
        $this->db->join($this->table_desa . ' rd', 'rd.desa_id = rp.desa_id', 'left');
        $this->db->join($this->table_kecamatan . ' rk', 'rk.kecamatan_id = rd.kecamatan_id', 'left');
        $this->db->order_by('rk.kecamatan_nama', 'ASC');
        $this->db->order_by('rd.desa_nama', 'ASC');
        $this->db->order_by('rp.posyandu_nama', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    // Get detail by ID
    public function get_by_id($detail_timsus_id)
    {
        $this->db->where('detail_timsus_id', $detail_timsus_id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    // Insert detail timsus
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->affected_rows() > 0;
    }

    // Insert batch (untuk save multiple posyandu sekaligus)
    public function insert_batch($data_array)
    {
        if (empty($data_array)) {
            return true; // Return true jika array kosong
        }
        
        $this->db->insert_batch($this->table, $data_array);
        return $this->db->affected_rows() > 0;
    }

    // Update detail timsus
    public function update($detail_timsus_id, $data)
    {
        $this->db->where('detail_timsus_id', $detail_timsus_id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows() >= 0;
    }

    // Delete detail by ID
    public function delete($detail_timsus_id)
    {
        $this->db->where('detail_timsus_id', $detail_timsus_id);
        $this->db->delete($this->table);
        return $this->db->affected_rows() > 0;
    }

    // Delete all detail by timsus_id
    public function delete_by_timsus_id($timsus_id)
    {
        $this->db->where('timsus_id', $timsus_id);
        $this->db->delete($this->table);
        return $this->db->affected_rows() >= 0;
    }

    // Check if posyandu already assigned to timsus
    public function is_posyandu_assigned($timsus_id, $posyandu_id)
    {
        $this->db->where('timsus_id', $timsus_id);
        $this->db->where('posyandu_id', $posyandu_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    // Get count posyandu per timsus
    public function count_posyandu_by_timsus($timsus_id)
    {
        $this->db->where('timsus_id', $timsus_id);
        return $this->db->count_all_results($this->table);
    }
}