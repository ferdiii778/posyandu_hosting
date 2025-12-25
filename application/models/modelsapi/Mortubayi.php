<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mortubayi extends CI_Model {

    private $table = 'ortu_bayi';

    // Ambil semua relasi + join orang tua & balita
    public function getAll() {
        $this->db->select('ob.*, ot.nama as nama_orangtua, b.nama_balita, b.tgl_lahir');
        $this->db->from($this->table . ' ob');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua', 'left');
        $this->db->join('balita b', 'ob.nib = b.nib', 'left');
        return $this->db->get()->result();
    }

    // Ambil relasi by ID ortu_bayi
    public function getById($id) {
        $this->db->select('ob.*, ot.nama as nama_orangtua, b.nama_balita');
        $this->db->from($this->table . ' ob');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua', 'left');
        $this->db->join('balita b', 'ob.nib = b.nib', 'left');
        $this->db->where('ob.id_ortu_bayi', $id);
        return $this->db->get()->row();
    }

    // Tambah relasi baru (assign balita ke orang tua)
    public function insert($id_orang_tua, $nib) {
        return $this->db->insert($this->table, [
            'id_orang_tua' => $id_orang_tua,
            'nib'          => $nib
        ]);
    }

    // Hapus relasi
    public function delete($id_orang_tua, $nib) {
        return $this->db->delete($this->table, [
            'id_orang_tua' => $id_orang_tua,
            'nib'          => $nib
        ]);
    }

    // Ambil semua balita milik orang tua tertentu
    public function getBalitaByOrangtua($id_orang_tua) {
        $this->db->select('b.nib, b.nama_balita, b.tgl_lahir');
        $this->db->from($this->table . ' ob');
        $this->db->join('balita b', 'ob.nib = b.nib', 'left');
        $this->db->where('ob.id_orang_tua', $id_orang_tua);
        return $this->db->get()->result();
    }
}
