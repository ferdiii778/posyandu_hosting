<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mkematian extends CI_Model {

    private $table = 'kematian'; // ← ubah jadi 'kematian' sesuai tabel aslinya

    // Ambil semua data kematian + join tabel balita
    public function getAll() {
        $this->db->select('kematian.*, balita.nama_balita, balita.nama_ibu, balita.tgl_lahir');
        $this->db->from($this->table);
        $this->db->join('balita', 'balita.nib = kematian.nib', 'left');
        return $this->db->get()->result();
    }

    // Ambil data kematian berdasarkan ID + join tabel balita
    public function getById($id) {
        $this->db->select('kematian.*, balita.nama_balita, balita.nama_ibu, balita.tgl_lahir');
        $this->db->from($this->table);
        $this->db->join('balita', 'balita.nib = kematian.nib', 'left');
        $this->db->where('kematian.id_kematian', $id);
        return $this->db->get()->row();
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
