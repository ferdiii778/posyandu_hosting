<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laporan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('modelsapi/Mmaster', 'mm');
        $this->load->model('modelsapi/Mbalita', 'balita');

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * ==============================
     * GET DATA BALITA
     * ==============================
     * Params:
     * - tahun (optional)
     * - role (admin / kader)
     * - posyandu_id (required if kader)
     */
    public function get_balita()
    {
        $tahun = $this->input->get('tahun');
        if (!$tahun) {
            $tahun = date('Y');
        }

        $role = $this->input->get('role');
        $posyandu_id = $this->input->get('posyandu_id');

        $where = array();
        if ($role === 'kader' && $posyandu_id) {
            $where['posyandu_id'] = $posyandu_id;
        }

        $balita = $this->mm->get('balita', array(
            'where' => $where
        ));

        echo json_encode(array(
            'status' => true,
            'data' => array(
                'tahun' => $tahun,
                'balita' => $balita
            )
        ));
    }

    /**
     * ==============================
     * GET PEMERIKSAAN BY NIB
     * ==============================
     * Params:
     * - nib (required)
     * - tahun (optional)
     * - bulan (optional)
     */
    public function get_pemeriksaan()
    {
        try {
            $nib   = $this->input->get('nib');
            $tahun = $this->input->get('tahun');
            $bulan = $this->input->get('bulan');

            if (!$nib) {
                throw new Exception('NIB harus diisi');
            }

            $balita = $this->balita->get_by_id($nib);
            if (!$balita) {
                throw new Exception('Data balita tidak ditemukan');
            }

            // reset builder
            $this->db->reset_query();

            if ($bulan && $tahun) {
                $this->db->where('MONTH(tgl_timbang)', $bulan);
                $this->db->where('YEAR(tgl_timbang)', $tahun);
            }

            $pemeriksaan = $this->mm->get('pemeriksaan', array(
                'join' => array(
                    array('balita', 'balita.nib = pemeriksaan.nib', 'left'),
                    array('jenis_imunisasi', 'jenis_imunisasi.id_jenis_imunisasi = pemeriksaan.id_jenis_imunisasi', 'left'),
                    array('jenis_vitamin', 'jenis_vitamin.id_jenis_vitamin = pemeriksaan.id_jenis_vitamin', 'left'),
                ),
                'where' => array(
                    'pemeriksaan.nib' => $nib
                ),
                'order_by' => array(
                    'tgl_timbang' => 'DESC'
                )
            ));

            echo json_encode(array(
                'status' => true,
                'data' => array(
                    'balita' => $balita,
                    'pemeriksaan' => $pemeriksaan,
                    'total' => count($pemeriksaan)
                )
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * ==============================
     * GET DATA KEMATIAN
     * ==============================
     * Params:
     * - tahun (optional)
     * - role (admin / kader)
     * - posyandu_id (required if kader)
     */
    public function get_kematian()
    {
        try {
            $tahun = $this->input->get('tahun');
            $role  = $this->input->get('role');
            $posyandu_id = $this->input->get('posyandu_id');

            $this->db->reset_query();
            $this->db->from('kematian');
            $this->db->join('balita', 'balita.nib = kematian.nib', 'left');

            if ($tahun) {
                $this->db->where('YEAR(tgl_kematian)', $tahun);
            }

            if ($role === 'kader' && $posyandu_id) {
                $this->db->where('posyandu_id', $posyandu_id);
            }

            $this->db->order_by('tgl_kematian', 'DESC');
            $data = $this->db->get()->result();

            echo json_encode(array(
                'status' => true,
                'data' => array(
                    'kematian' => $data,
                    'total' => count($data)
                )
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'status' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * ==============================
     * GET LIST TAHUN
     * ==============================
     */
    public function get_tahun()
    {
        $tahun = $this->mm->get('ref_tahun');

        echo json_encode(array(
            'status' => true,
            'data' => $tahun
        ));
    }

    /**
     * ==============================
     * GET SUMMARY LAPORAN
     * ==============================
     * Params:
     * - tahun (optional)
     * - role (admin / kader)
     * - posyandu_id (required if kader)
     */
    public function get_summary()
    {
        $tahun = $this->input->get('tahun');
        if (!$tahun) {
            $tahun = date('Y');
        }

        $role = $this->input->get('role');
        $posyandu_id = $this->input->get('posyandu_id');

        // TOTAL BALITA
        $this->db->reset_query();
        if ($role === 'kader' && $posyandu_id) {
            $this->db->where('posyandu_id', $posyandu_id);
        }
        $total_balita = $this->db->count_all_results('balita');

        // BALITA AKTIF
        $this->db->reset_query();
        if ($role === 'kader' && $posyandu_id) {
            $this->db->where('posyandu_id', $posyandu_id);
        }
        $this->db->where('is_meninggal', 0);
        $total_balita_aktif = $this->db->count_all_results('balita');

        // KEMATIAN
        $this->db->reset_query();
        $this->db->where('YEAR(tgl_kematian)', $tahun);
        if ($role === 'kader' && $posyandu_id) {
            $this->db->where('posyandu_id', $posyandu_id);
        }
        $total_kematian = $this->db->count_all_results('kematian');

        // PEMERIKSAAN
        $this->db->reset_query();
        $this->db->where('YEAR(tgl_timbang)', $tahun);
        if ($role === 'kader' && $posyandu_id) {
            $this->db->join('balita', 'balita.nib = pemeriksaan.nib');
            $this->db->where('balita.posyandu_id', $posyandu_id);
        }
        $total_pemeriksaan = $this->db->count_all_results('pemeriksaan');

        echo json_encode(array(
            'status' => true,
            'data' => array(
                'tahun' => $tahun,
                'total_balita' => $total_balita,
                'total_balita_aktif' => $total_balita_aktif,
                'total_kematian' => $total_kematian,
                'total_pemeriksaan' => $total_pemeriksaan
            )
        ));
    }
}
