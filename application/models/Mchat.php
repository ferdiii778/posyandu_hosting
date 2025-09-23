<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mchat extends CI_Model {

    private $table = 'chat';

    // ✅ Ambil semua chat
    public function getAll() {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi, is_deleted, is_edited')
                        ->from($this->table)
                        ->order_by('id', 'ASC')
                        ->get()
                        ->result();
    }

    // ✅ Ambil chat by ID
    public function getById($id) {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi, is_deleted, is_edited')
                        ->from($this->table)
                        ->where('id', $id)
                        ->get()
                        ->row();
    }

    // ✅ Ambil semua chat berdasarkan id_konsultasi
    public function getByKonsultasi($id_konsultasi) {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi, is_deleted, is_edited')
                        ->from($this->table)
                        ->where('id_konsultasi', $id_konsultasi)
                        ->order_by('id', 'ASC')
                        ->get()
                        ->result();
    }

    // ✅ Soft delete chat
    public function softDelete($id) {
        return $this->db->where('id', $id)
                        ->update($this->table, [
                            'is_deleted' => 1,
                            'isi' => 'Pesan ini telah dihapus'
                        ]);
    }

    // ✅ Edit chat
    public function updateMessage($id, $isiBaru) {
        return $this->db->where('id', $id)
                        ->update($this->table, [
                            'isi' => $isiBaru,
                            'is_edited' => 1
                        ]);
    }

    // ✅ Tambah chat baru
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    // ✅ Update chat (umum)
    public function update($id, $data) {
        return $this->db->where('id', $id)
                        ->update($this->table, $data);
    }

    // ❌ Hapus permanen (hati2 kalau dipakai)
    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
