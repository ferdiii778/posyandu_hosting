<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mvitamin extends CI_Model {

    private $table = 'jenis_vitamin'; // nama tabel di database

    // Ambil semua data vitamin
    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    // Ambil data vitamin berdasarkan ID
    public function getById($id) {
        return $this->db->get_where($this->table, ['id_jenis_vitamin' => $id])->row();
    }

    // Tambah data vitamin
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Update data vitamin
    public function update($id, $data) {
        $this->db->where('id_jenis_vitamin', $id);
        return $this->db->update($this->table, $data);
    }

    // Hapus data vitamin
    public function delete($id) {
        $this->db->where('id_jenis_vitamin', $id);
        return $this->db->delete($this->table);
    }
}
