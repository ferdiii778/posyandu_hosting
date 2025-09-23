<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mimunisasi extends CI_Model {

    private $table = 'jenis_imunisasi'; // nama tabel di database

    // Ambil semua data imunisasi
    public function getAll() {
        return $this->db->get($this->table)->result();
    }

    // Ambil imunisasi berdasarkan ID
    public function getById($id) {
        return $this->db->get_where($this->table, ['id_jenis_imunisasi' => $id])->row();
    }

    // Tambah imunisasi baru
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // Update data imunisasi
    public function update($id, $data) {
        $this->db->where('id_jenis_imunisasi', $id);
        return $this->db->update($this->table, $data);
    }

    // Hapus imunisasi
    public function delete($id) {
        $this->db->where('id_jenis_imunisasi', $id);
        return $this->db->delete($this->table);
    }
}
