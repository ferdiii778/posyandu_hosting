<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbalita extends CI_Model {

    // Ambil semua data balita + orang tua
    public function getAll() {
        $this->db->select('b.nib, b.nama_balita, b.tgl_lahir, b.jenis_kelamin, 
                           b.nama_ibu, b.nama_ayah, b.is_meninggal,
                           ot.nama as nama_orangtua, ot.username');
        $this->db->from('ortu_bayi ob');
        $this->db->join('balita b', 'ob.nib = b.nib');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua');
        return $this->db->get()->result();
    }

    // Ambil detail berdasarkan NIB
    public function getByNib($nib) {
        $this->db->select('b.nib, b.nama_balita, b.tgl_lahir, b.jenis_kelamin, 
                           b.nama_ibu, b.nama_ayah, b.is_meninggal,
                           ot.nama as nama_orangtua, ot.username');
        $this->db->from('ortu_bayi ob');
        $this->db->join('balita b', 'ob.nib = b.nib');
        $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua');
        $this->db->where('b.nib', $nib);
        return $this->db->get()->row();
    }

    // Insert ke balita dan ortu_bayi
    public function insert($dataBalita, $dataOrtuBayi) {
        $this->db->insert('balita', $dataBalita);
        $this->db->insert('ortu_bayi', $dataOrtuBayi);
        return $this->db->affected_rows() > 0;
    }

    // Update data balita
    public function update($nib, $dataBalita) {
        $this->db->where('nib', $nib);
        $this->db->update('balita', $dataBalita);
        return $this->db->affected_rows() > 0;
    }

    // Hapus data balita + ortu_bayi
    public function delete($nib) {
        $this->db->where('nib', $nib);
        $this->db->delete('ortu_bayi');
        $this->db->where('nib', $nib);
        $this->db->delete('balita');
        return $this->db->affected_rows() > 0;
    }
}
