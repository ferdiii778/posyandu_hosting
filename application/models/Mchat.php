<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mchat extends CI_Model {

    private $table = 'chat';

    public function getAll() {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi')
                        ->from($this->table)
                        ->order_by('id', 'ASC')
                        ->get()
                        ->result();
    }

    public function getById($id) {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi')
                        ->from($this->table)
                        ->where('id', $id)
                        ->get()
                        ->row();
    }

    public function getByKonsultasi($id_konsultasi) {
        return $this->db->select('id, id_konsultasi, dari, untuk, isi')
                        ->from($this->table)
                        ->where('id_konsultasi', $id_konsultasi)
                        ->order_by('id', 'ASC')
                        ->get()
                        ->result();
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->where('id', $id)
                        ->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
