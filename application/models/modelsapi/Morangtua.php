<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Morangtua extends CI_Model {

    private $table = 'orang_tua';

    // Ambil semua orang tua + user + posyandu + daftar balita
    public function getAll() {
        $this->db->select('ot.id_orang_tua, ot.nama, ot.username,
                           u.id_user, u.role, u.aktif,
                           rp.posyandu_id, rp.posyandu_nama');
        $this->db->from($this->table . ' ot');
        $this->db->join('user u', 'u.username = ot.username', 'left');
        $this->db->join('ref_posyandu rp', 'rp.posyandu_id = ot.posyandu_id', 'left');
        $orangtua = $this->db->get()->result_array();

        // Ambil daftar balita untuk tiap orang tua via ortu_bayi
        foreach ($orangtua as &$o) {
            // ✅ pastikan integer
            $o['id_orang_tua'] = (int) $o['id_orang_tua'];
            $o['id_user']      = (int) $o['id_user'];
            $o['aktif']        = (int) $o['aktif'];
            $o['posyandu_id']  = (int) $o['posyandu_id'];

            $this->db->select('b.nib, b.nama_balita, b.tgl_lahir');
            $this->db->from('ortu_bayi ob');
            $this->db->join('balita b', 'ob.nib = b.nib');
            $this->db->where('ob.id_orang_tua', $o['id_orang_tua']);
            $o['balita'] = $this->db->get()->result_array();
        }

        return $orangtua;
    }

    // Ambil orang tua berdasarkan posyandu (untuk kader)
    public function getByPosyandu($posyandu_id) {
        $this->db->select('ot.id_orang_tua, ot.nama, ot.username,
                           u.id_user, u.role, u.aktif,
                           rp.posyandu_id, rp.posyandu_nama');
        $this->db->from($this->table . ' ot');
        $this->db->join('user u', 'u.username = ot.username', 'left');
        $this->db->join('ref_posyandu rp', 'rp.posyandu_id = ot.posyandu_id', 'left');
        $this->db->where('ot.posyandu_id', (int) $posyandu_id);
    
        $orangtua = $this->db->get()->result_array();
    
        // ambil balita per orang tua (copy pola dari getAll)
        foreach ($orangtua as &$o) {
            $o['id_orang_tua'] = (int) $o['id_orang_tua'];
            $o['id_user']      = (int) $o['id_user'];
            $o['aktif']        = (int) $o['aktif'];
            $o['posyandu_id']  = (int) $o['posyandu_id'];
    
            $this->db->select('b.nib, b.nama_balita, b.tgl_lahir');
            $this->db->from('ortu_bayi ob');
            $this->db->join('balita b', 'ob.nib = b.nib');
            $this->db->where('ob.id_orang_tua', $o['id_orang_tua']);
            $o['balita'] = $this->db->get()->result_array();
        }
    
        return $orangtua;
    }

    // Detail orang tua by id
    public function getById($id) {
        $this->db->select('ot.id_orang_tua, ot.nama, ot.username,
                           u.id_user, u.role, u.aktif,
                           rp.posyandu_id, rp.posyandu_nama');
        $this->db->from($this->table . ' ot');
        $this->db->join('user u', 'u.username = ot.username', 'left');
        $this->db->join('ref_posyandu rp', 'rp.posyandu_id = ot.posyandu_id', 'left');
        $this->db->where('ot.id_orang_tua', $id);
        $orangtua = $this->db->get()->row_array();

        if ($orangtua) {
            // ✅ paksa integer
            $orangtua['id_orang_tua'] = (int) $orangtua['id_orang_tua'];
            $orangtua['id_user']      = (int) $orangtua['id_user'];
            $orangtua['aktif']        = (int) $orangtua['aktif'];
            $orangtua['posyandu_id']  = (int) $orangtua['posyandu_id'];

            $this->db->select('b.nib, b.nama_balita, b.tgl_lahir');
            $this->db->from('ortu_bayi ob');
            $this->db->join('balita b', 'ob.nib = b.nib');
            $this->db->where('ob.id_orang_tua', $id);
            $orangtua['balita'] = $this->db->get()->result_array();
        }

        return $orangtua;
    }
}
