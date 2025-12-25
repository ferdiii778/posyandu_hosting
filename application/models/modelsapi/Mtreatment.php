<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mtreatment extends CI_Model
{
    /**
     * LIST DATA TREATMENT
     * Join sama persis seperti controller web mentor
     */
    public function get_all()
    {
        return $this->db
            ->from('treatment')
            ->join(
                'pemeriksaan',
                'pemeriksaan.kode_pemeriksaan = treatment.kode_pemeriksaan',
                'left'
            )
            ->join(
                'balita',
                'balita.nib = pemeriksaan.nib',
                'left'
            )
            ->join(
                'timsus',
                'timsus.timsus_id = treatment.timsus_id',
                'left'
            )
            ->order_by('treatment.treatment_id', 'DESC')
            ->get()
            ->result();
    }

    /**
     * DETAIL
     */
    public function get_by_id($id)
    {
        return $this->db
            ->from('treatment')
            ->join(
                'pemeriksaan',
                'pemeriksaan.kode_pemeriksaan = treatment.kode_pemeriksaan',
                'left'
            )
            ->join(
                'balita',
                'balita.nib = pemeriksaan.nib',
                'left'
            )
            ->join(
                'timsus',
                'timsus.timsus_id = treatment.timsus_id',
                'left'
            )
            ->where('treatment.treatment_id', $id)
            ->get()
            ->row();
    }

    /**
     * DELETE
     */
    public function delete($id)
    {
        return $this->db
            ->where('treatment_id', $id)
            ->delete('treatment');
    }

    /* =========================
    BALITA STUNTING
    ==========================*/
    public function get_balita_stunting()
    {
        $this->db->select('
            pemeriksaan.kode_pemeriksaan,
            balita.nama_balita
        ');
        $this->db->from('pemeriksaan');
        $this->db->join('balita', 'balita.nib = pemeriksaan.nib', 'left');
        $this->db->where('pemeriksaan.status_pemeriksaan', 'Stunting');
        $this->db->where('balita.is_meninggal', '0');
        $this->db->group_by('pemeriksaan.nib');
        $this->db->order_by('pemeriksaan.kode_pemeriksaan', 'DESC');
    
        return $this->db->get()->result_array();
    }
    
    /* =========================
       TIM KHUSUS
    ==========================*/
    public function get_timsus()
    {
        return $this->db
            ->order_by('timsus_id', 'DESC')
            ->get('timsus')
            ->result_array();
    }

}
