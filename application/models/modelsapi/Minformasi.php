<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Minformasi extends CI_Model {

    private $table = 'informasi';
    private $primary_key = 'id_informasi';

    // GET semua data
    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    // GET by ID
    public function getById($id) {
        return $this->db->where($this->primary_key, $id)
                        ->get($this->table)
                        ->row();
    }

    // GET by Posyandu ID
    public function getByPosyandu($posyandu_id) {
        return $this->db->where('posyandu_id', $posyandu_id)
                        ->order_by('tgl_post', 'DESC')
                        ->get($this->table)
                        ->result();
    }

    // INSERT data baru
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // UPDATE data
    public function update($id, $data) {
        return $this->db->where($this->primary_key, $id)
                        ->update($this->table, $data);
    }

    // DELETE data
    public function delete($id) {
        return $this->db->where($this->primary_key, $id)
                        ->delete($this->table);
    }

    // GET latest informasi
    public function getLatest($limit = 5) {
        return $this->db->order_by('tgl_post', 'DESC')
                        ->limit($limit)
                        ->get($this->table)
                        ->result();
    }

    // SEARCH informasi
    public function search($keyword) {
        return $this->db->like('judul', $keyword)
                        ->or_like('isi', $keyword)
                        ->order_by('tgl_post', 'DESC')
                        ->get($this->table)
                        ->result();
    }
}