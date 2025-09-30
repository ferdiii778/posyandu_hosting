<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mkematian extends CI_Model {

    private $table = 'kematian'; // tabel di database

    // Ambil semua data kematian
    public function getAll() {
        $this->db->select('data_kematian.*, balita.nama_balita, balita.nama_ibu, balita.tgl_lahir');
        $this->db->from('data_kematian');
        $this->db->join('balita', 'balita.nib = data_kematian.nib', 'left');
        return $this->db->get()->result();
    }
        
    // Ambil semua data menggunakan id
    public function getById($id) {
        $this->db->select('data_kematian.*, balita.nama_balita, balita.nama_ibu, balita.tgl_lahir');
        $this->db->from('data_kematian');
        $this->db->join('balita', 'balita.nib = data_kematian.nib', 'left');
        $this->db->where('data_kematian.id_kematian', $id);
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
