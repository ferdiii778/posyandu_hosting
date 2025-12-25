<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mjadwal extends CI_Model {

    private $table = 'jadwal_pemeriksaan';

    public function getAll() {
        return $this->db->select('id_jadwal_pemeriksaan, tgl_jadwal, jam_mulai, jam_selese')
                        ->from($this->table)
                        ->get()
                        ->result();
    }

    public function getById($id) {
        return $this->db->select('id_jadwal_pemeriksaan, tgl_jadwal, jam_mulai, jam_selese')
                        ->from($this->table)
                        ->where('id_jadwal_pemeriksaan', $id)
                        ->get()
                        ->row();
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->where('id_jadwal_pemeriksaan', $id)
                        ->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, ['id_jadwal_pemeriksaan' => $id]);
    }
}
