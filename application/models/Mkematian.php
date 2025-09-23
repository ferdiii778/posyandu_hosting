<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mkematian extends CI_Model {

    private $table = 'kematian'; // tabel di database

    // Ambil semua data kematian
    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    // Ambil data kematian berdasarkan ID
    public function getById($id) {
        return $this->db->get_where($this->table, ['id_kematian' => $id])->row();
    }

    // Tambah data kematian
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Update data kematian
    public function update($id, $data) {
        $this->db->where('id_kematian', $id);
        return $this->db->update($this->table, $data);
    }

    // Hapus data kematian
    public function delete($id) {
        $this->db->where('id_kematian', $id);
        return $this->db->delete($this->table);
    }
}
