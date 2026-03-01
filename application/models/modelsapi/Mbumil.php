<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mbumil extends CI_Model
{
    private $table = 'bumil';
    private $primaryKey = 'bumil_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ✅ Ambil semua data ibu hamil
    public function get_all()
    {
        $this->db->select('
            b.bumil_id,
            b.posyandu_id,
            p.nama_posyandu,
            b.bumil_nama,
            b.bumil_ttl,
            b.bumil_nik,
            b.bumil_no_jkn,
            b.bumil_goldar,
            b.bumil_faskes1,
            b.bumil_faskes_rujukan,
            b.bumil_pendidikan,
            b.bumil_pekerjaan,
            b.bumil_telp,
            b.bumil_alamat,
            b.bumil_asuransi_lain,
            b.bumil_asuransi_lain_no,
            b.bumil_asuransi_lain_tgl_aktif,
            b.bumil_puskesmas_domisili,
            b.bumil_no_kohort1_ibu,
            b.bumil_no_kohort1_bayi,
            b.bumil_no_kohort1_balita,
            b.bumil_no_catatan_medik,
            b.bumil_anak_ke,
            b.bumil_tgl_input
        ');
        $this->db->from($this->table . ' b');
        $this->db->join('ref_posyandu p', 'b.posyandu_id = p.posyandu_id', 'left');
        $this->db->order_by('b.bumil_id', 'DESC');
        return $this->db->get()->result();
    }

    // ✅ Ambil data berdasarkan ID
    public function get_by_id($id)
    {
        $this->db->select('
            b.bumil_id,
            b.posyandu_id,
            p.nama_posyandu,
            b.bumil_nama,
            b.bumil_ttl,
            b.bumil_nik,
            b.bumil_no_jkn,
            b.bumil_goldar,
            b.bumil_faskes1,
            b.bumil_faskes_rujukan,
            b.bumil_pendidikan,
            b.bumil_pekerjaan,
            b.bumil_telp,
            b.bumil_alamat,
            b.bumil_asuransi_lain,
            b.bumil_asuransi_lain_no,
            b.bumil_asuransi_lain_tgl_aktif,
            b.bumil_puskesmas_domisili,
            b.bumil_no_kohort1_ibu,
            b.bumil_no_kohort1_bayi,
            b.bumil_no_kohort1_balita,
            b.bumil_no_catatan_medik,
            b.bumil_anak_ke,
            b.bumil_tgl_input
        ');
        $this->db->from($this->table . ' b');
        $this->db->join('ref_posyandu p', 'b.posyandu_id = p.posyandu_id', 'left');
        $this->db->where('b.' . $this->primaryKey, $id);
        return $this->db->get()->row();
    }

    // ✅ Tambah data ibu hamil
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        // Tambahkan log error agar lebih mudah debug jika gagal
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Gagal insert bumil: ' . $this->db->error()['message']);
            return false;
        }
        return true;
    }

    // ✅ Update data ibu hamil
    public function update($id, $data)
    {
        $this->db->where($this->primaryKey, $id);
        $this->db->update($this->table, $data);

        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Gagal update bumil: ' . $this->db->error()['message']);
            return false;
        }
        return true;
    }

    // ✅ Hapus data ibu hamil
    public function delete($id)
    {
        $this->db->where($this->primaryKey, $id);
        $this->db->delete($this->table);

        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Gagal hapus bumil: ' . $this->db->error()['message']);
            return false;
        }
        return true;
    }

    // (Opsional) ✅ Untuk ambil semua data berdasarkan posyandu tertentu
    public function get_by_posyandu($posyandu_id)
    {
        $this->db->where('posyandu_id', $posyandu_id);
        return $this->db->get($this->table)->result();
    }
}
