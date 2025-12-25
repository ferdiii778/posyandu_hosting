<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mlaporan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model(['modelsapi/Mvitamin', 'modelsapi/Mimunisasi', 'modelsapi/Mbalita']);
        
        // Set header untuk API
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    /**
     * Get Data Balita
     * Method: GET
     * Params: tahun (optional), posyandu_id (optional for kader)
     */
    public function get_balita()
    {
        try {
            $tahun = $this->input->get('tahun') ?: date('Y');
            $posyandu_id = $this->input->get('posyandu_id');
            $role = $this->input->get('role'); // 'kader' or 'admin'

            $where = array();
            
            if ($role == 'kader' && $posyandu_id) {
                $where['posyandu_id'] = $posyandu_id;
            }

            $preloadbalita = array(
                'where' => $where,
            );

            $balita = $this->mm->get('balita', $preloadbalita);

            // Get kematian data for the year
            $where_kematian = array();
            if ($tahun) {
                $where_kematian['year(tgl_kematian)'] = $tahun;
            }
            if ($role == 'kader' && $posyandu_id) {
                $where_kematian['posyandu_id'] = $posyandu_id;
            }

            $preloadkematian = array(
                'join' => array(
                    array('balita', 'balita.nib = kematian.nib', 'left')
                ),
                'where' => $where_kematian,
            );
            
            $kematian = $this->mm->get('kematian', $preloadkematian);

            // Format response
            $response = array(
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => array(
                    'balita' => $balita,
                    'kematian' => $kematian,
                    'tahun' => $tahun
                )
            );

            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            );
            echo json_encode($response);
        }
    }

    /**
     * Get Data Pemeriksaan by NIB
     * Method: GET
     * Params: nib (required), tahun (optional), bulan (optional)
     */
    public function get_pemeriksaan()
    {
        try {
            $nib = $this->input->get('nib');
            $tahun = $this->input->get('tahun');
            $bulan = $this->input->get('bulan');

            if (!$nib) {
                throw new Exception('NIB harus diisi');
            }

            // Get balita data
            $balita_data = $this->BalitaModel->get_by_id($nib);

            if (!$balita_data) {
                throw new Exception('Data balita tidak ditemukan');
            }

            // Build where clause for pemeriksaan
            $where_pemeriksaan = array('pemeriksaan.nib' => $nib);
            
            if ($bulan && $tahun) {
                $where_pemeriksaan['month(tgl_timbang)'] = $bulan;
                $where_pemeriksaan['year(tgl_timbang)'] = $tahun;
            }

            $preloadpemeriksaan = array(
                'join' => array(
                    array('balita', 'balita.nib = pemeriksaan.nib', 'left'),
                    array('jenis_imunisasi', 'jenis_imunisasi.id_jenis_imunisasi = pemeriksaan.id_jenis_imunisasi', 'left'),
                    array('jenis_vitamin', 'jenis_vitamin.id_jenis_vitamin = pemeriksaan.id_jenis_vitamin', 'left'),
                ),
                'where' => $where_pemeriksaan,
                'order_by' => array('tgl_timbang' => 'DESC')
            );

            $pemeriksaan = $this->mm->get('pemeriksaan', $preloadpemeriksaan);

            $response = array(
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => array(
                    'balita' => $balita_data,
                    'pemeriksaan' => $pemeriksaan,
                    'total' => count($pemeriksaan)
                )
            );

            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            );
            echo json_encode($response);
        }
    }

    /**
     * Get Data Kematian
     * Method: GET
     * Params: tahun (optional), posyandu_id (optional)
     */
    public function get_kematian()
    {
        try {
            $tahun = $this->input->get('tahun');
            $posyandu_id = $this->input->get('posyandu_id');
            $role = $this->input->get('role');

            $where_kematian = array();
            
            if ($tahun) {
                $where_kematian['year(tgl_kematian)'] = $tahun;
            }
            
            if ($role == 'kader' && $posyandu_id) {
                $where_kematian['posyandu_id'] = $posyandu_id;
            }

            $preloadkematian = array(
                'join' => array(
                    array('balita', 'balita.nib = kematian.nib', 'left')
                ),
                'where' => $where_kematian,
                'order_by' => array('tgl_kematian' => 'DESC')
            );

            $kematian = $this->mm->get('kematian', $preloadkematian);

            $response = array(
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => array(
                    'kematian' => $kematian,
                    'total' => count($kematian)
                )
            );

            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            );
            echo json_encode($response);
        }
    }

    /**
     * Get Tahun List
     * Method: GET
     */
    public function get_tahun()
    {
        try {
            $tahun = $this->mm->get('ref_tahun');

            $response = array(
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $tahun
            );

            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            );
            echo json_encode($response);
        }
    }

    /**
     * Get Summary Statistics
     * Method: GET
     * Params: tahun (optional), posyandu_id (optional)
     */
    public function get_summary()
    {
        try {
            $tahun = $this->input->get('tahun') ?: date('Y');
            $posyandu_id = $this->input->get('posyandu_id');
            $role = $this->input->get('role');

            $where = array();
            if ($role == 'kader' && $posyandu_id) {
                $where['posyandu_id'] = $posyandu_id;
            }

            // Total Balita
            $this->db->where($where);
            $total_balita = $this->db->count_all_results('balita');

            // Total Balita Aktif (tidak meninggal)
            $this->db->where($where);
            $this->db->where('is_meninggal', 0);
            $total_balita_aktif = $this->db->count_all_results('balita');

            // Total Kematian tahun ini
            $where_kematian = $where;
            $where_kematian['year(tgl_kematian)'] = $tahun;
            $this->db->where($where_kematian);
            $total_kematian = $this->db->count_all_results('kematian');

            // Total Pemeriksaan tahun ini
            if ($role == 'kader' && $posyandu_id) {
                $this->db->join('balita', 'balita.nib = pemeriksaan.nib');
                $this->db->where('balita.posyandu_id', $posyandu_id);
            }
            $this->db->where('year(tgl_timbang)', $tahun);
            $total_pemeriksaan = $this->db->count_all_results('pemeriksaan');

            $response = array(
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => array(
                    'total_balita' => $total_balita,
                    'total_balita_aktif' => $total_balita_aktif,
                    'total_kematian' => $total_kematian,
                    'total_pemeriksaan' => $total_pemeriksaan,
                    'tahun' => $tahun
                )
            );

            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            );
            echo json_encode($response);
        }
    }
}