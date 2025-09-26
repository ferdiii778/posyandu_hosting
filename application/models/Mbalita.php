<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbalita extends CI_Model {

    public function getAll() {
        $this->db->select('b.*, ot.nama as nama_orangtua, ot.username, rp.posyandu_nama');
        $this->db->from('ortu_bayi ob');
        $this->db->join('balita b', 'ob.nib = b.nib');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua');
        $this->db->join('ref_posyandu rp', 'b.posyandu_id = rp.posyandu_id', 'left');
        return $this->db->get()->result();
    }

    public function getByNib($nib) {
        $this->db->select('b.*, ot.nama as nama_orangtua, ot.username, rp.posyandu_nama');
        $this->db->from('ortu_bayi ob');
        $this->db->join('balita b', 'ob.nib = b.nib');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua');
        $this->db->join('ref_posyandu rp', 'b.posyandu_id = rp.posyandu_id', 'left');
        $this->db->where('b.nib', $nib);
        return $this->db->get()->row();
    }

    public function insert($dataBalita, $dataOrtuBayi) {
        $this->db->insert('balita', $dataBalita);
        $this->db->insert('ortu_bayi', $dataOrtuBayi);
        return $this->db->affected_rows() > 0;
    }

    public function update($nib, $dataBalita) {
        $this->db->where('nib', $nib);
        $this->db->update('balita', $dataBalita);
        return $this->db->affected_rows() > 0;
    }

    public function delete($nib) {
        $this->db->where('nib', $nib);
        $this->db->delete('ortu_bayi');
        $this->db->where('nib', $nib);
        $this->db->delete('balita');
        return $this->db->affected_rows() > 0;
    }
}
