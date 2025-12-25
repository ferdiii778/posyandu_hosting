<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * REST API Controller untuk Pemantauan Nifas
 * Untuk Mobile Application
 */
class PemantauanNifas extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('BumilModel');
        
        // Set header untuk JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    private function send_response($status = 200, $message = '', $data = null)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        http_response_code($status);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * GET - Ambil semua pemantauan nifas berdasarkan bumil_id
     * Endpoint: /PemantauanNifas/get_all/{bumil_id}
     */
    public function get_all($bumil_id = null)
    {
        if (empty($bumil_id)) {
            $this->send_response(400, 'bumil_id harus diisi', null);
        }

        try {
            // Cek apakah bumil ada
            $bumil = $this->mm->get('bumil', array('where' => array('bumil_id' => $bumil_id)), 'roar');
            
            if (empty($bumil)) {
                $this->send_response(404, 'Data ibu hamil tidak ditemukan', null);
            }

            // Ambil semua pemantauan nifas
            $nifas = $this->mm->get('pemantauan_nifas', array(
                'where' => array('bumil_id' => $bumil_id),
                'order' => 'pemantauan_nifas_id DESC'
            ));

            $this->send_response(200, 'Berhasil mengambil data', [
                'bumil' => $bumil,
                'nifas' => $nifas
            ]);

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * GET - Ambil detail pemantauan nifas berdasarkan ID
     * Endpoint: /PemantauanNifas/get_by_id/{nifas_id}
     */
    public function get_by_id($nifas_id = null)
    {
        if (empty($nifas_id)) {
            $this->send_response(400, 'pemantauan_nifas_id harus diisi', null);
        }

        try {
            $nifas = $this->BumilModel->get_by_id_nifas($nifas_id);
            
            if (empty($nifas)) {
                $this->send_response(404, 'Data pemantauan nifas tidak ditemukan', null);
            }

            $this->send_response(200, 'Berhasil mengambil data', $nifas);

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * POST - Tambah pemantauan nifas baru
     * Endpoint: /PemantauanNifas/create
     */
    public function create()
    {
        try {
            $json_input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($json_input)) {
                $this->send_response(400, 'Data tidak valid', null);
            }

            if (!isset($json_input['bumil_id']) || empty($json_input['bumil_id'])) {
                $this->send_response(400, 'Field bumil_id harus diisi', null);
            }

            $bumil_id = $json_input['bumil_id'];

            // Cek apakah bumil ada
            $bumil = $this->mm->get('bumil', array('where' => array('bumil_id' => $bumil_id)), 'roar');
            
            if (empty($bumil)) {
                $this->send_response(404, 'Data ibu hamil tidak ditemukan', null);
            }

            // Siapkan data untuk insert
            $data = [
                'bumil_id' => $bumil_id,
                'pemantauan_nifas_hari' => isset($json_input['pemantauan_nifas_hari']) ? $json_input['pemantauan_nifas_hari'] : 0,
                'pemantauan_nifas_tgl' => isset($json_input['pemantauan_nifas_tgl']) ? $json_input['pemantauan_nifas_tgl'] : date('Y-m-d'),
                'pemantauan_nifas_kader' => isset($json_input['pemantauan_nifas_kader']) ? $json_input['pemantauan_nifas_kader'] : '',
                'pemantauan_nifas_periksa' => isset($json_input['pemantauan_nifas_periksa']) ? $json_input['pemantauan_nifas_periksa'] : 0,
                'pemantauan_nifas_vitamin' => isset($json_input['pemantauan_nifas_vitamin']) ? $json_input['pemantauan_nifas_vitamin'] : 0,
                'pemantauan_nifas_tablet_tambah_darah' => isset($json_input['pemantauan_nifas_tablet_tambah_darah']) ? $json_input['pemantauan_nifas_tablet_tambah_darah'] : 0,
                'pemantauan_nifas_gizi' => isset($json_input['pemantauan_nifas_gizi']) ? $json_input['pemantauan_nifas_gizi'] : 0,
                'pemantauan_nifas_jiwa' => isset($json_input['pemantauan_nifas_jiwa']) ? $json_input['pemantauan_nifas_jiwa'] : 0,
                'pemantauan_nifas_demam' => isset($json_input['pemantauan_nifas_demam']) ? $json_input['pemantauan_nifas_demam'] : 0,
                'pemantauan_nifas_pusing' => isset($json_input['pemantauan_nifas_pusing']) ? $json_input['pemantauan_nifas_pusing'] : 0,
                'pemantauan_nifas_pandangan_kabur' => isset($json_input['pemantauan_nifas_pandangan_kabur']) ? $json_input['pemantauan_nifas_pandangan_kabur'] : 0,
                'pemantauan_nifas_nyeri_uluhati' => isset($json_input['pemantauan_nifas_nyeri_uluhati']) ? $json_input['pemantauan_nifas_nyeri_uluhati'] : 0,
                'pemantauan_nifas_jantung_berdebar' => isset($json_input['pemantauan_nifas_jantung_berdebar']) ? $json_input['pemantauan_nifas_jantung_berdebar'] : 0,
                'pemantauan_nifas_keluar_cairan' => isset($json_input['pemantauan_nifas_keluar_cairan']) ? $json_input['pemantauan_nifas_keluar_cairan'] : 0,
                'pemantauan_nifas_napas_pendek' => isset($json_input['pemantauan_nifas_napas_pendek']) ? $json_input['pemantauan_nifas_napas_pendek'] : 0,
                'pemantauan_nifas_payudara_bengkak' => isset($json_input['pemantauan_nifas_payudara_bengkak']) ? $json_input['pemantauan_nifas_payudara_bengkak'] : 0,
                'pemantauan_nifas_susah_kencing' => isset($json_input['pemantauan_nifas_susah_kencing']) ? $json_input['pemantauan_nifas_susah_kencing'] : 0,
                'pemantauan_nifas_kelamin_bengkak' => isset($json_input['pemantauan_nifas_kelamin_bengkak']) ? $json_input['pemantauan_nifas_kelamin_bengkak'] : 0,
                'pemantauan_nifas_darah_berbau' => isset($json_input['pemantauan_nifas_darah_berbau']) ? $json_input['pemantauan_nifas_darah_berbau'] : 0,
                'pemantauan_nifas_pendarahan' => isset($json_input['pemantauan_nifas_pendarahan']) ? $json_input['pemantauan_nifas_pendarahan'] : 0,
                'pemantauan_nifas_keputihan' => isset($json_input['pemantauan_nifas_keputihan']) ? $json_input['pemantauan_nifas_keputihan'] : 0,
            ];

            // Insert data
            $insert_result = $this->mm->save('pemantauan_nifas', $data);

            if ($insert_result) {
                $new_nifas = $this->mm->get('pemantauan_nifas', array(
                    'where' => array('bumil_id' => $bumil_id),
                    'order' => 'pemantauan_nifas_id DESC',
                    'limit' => '1'
                ), 'roar');

                $this->send_response(201, 'Berhasil menambah data pemantauan nifas', $new_nifas);
            } else {
                $this->send_response(500, 'Gagal menambah data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * PUT - Update pemantauan nifas
     * Endpoint: /PemantauanNifas/update/{nifas_id}
     */
    public function update($nifas_id = null)
    {
        if (empty($nifas_id)) {
            $this->send_response(400, 'pemantauan_nifas_id harus diisi', null);
        }

        try {
            $existing_data = $this->BumilModel->get_by_id_nifas($nifas_id);
            
            if (empty($existing_data)) {
                $this->send_response(404, 'Data pemantauan nifas tidak ditemukan', null);
            }

            $json_input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($json_input)) {
                $this->send_response(400, 'Data tidak valid', null);
            }

            // Siapkan data untuk update
            $data = [
                'pemantauan_nifas_hari' => isset($json_input['pemantauan_nifas_hari']) ? $json_input['pemantauan_nifas_hari'] : $existing_data->pemantauan_nifas_hari,
                'pemantauan_nifas_tgl' => isset($json_input['pemantauan_nifas_tgl']) ? $json_input['pemantauan_nifas_tgl'] : $existing_data->pemantauan_nifas_tgl,
                'pemantauan_nifas_kader' => isset($json_input['pemantauan_nifas_kader']) ? $json_input['pemantauan_nifas_kader'] : $existing_data->pemantauan_nifas_kader,
                'pemantauan_nifas_periksa' => isset($json_input['pemantauan_nifas_periksa']) ? $json_input['pemantauan_nifas_periksa'] : $existing_data->pemantauan_nifas_periksa,
                'pemantauan_nifas_vitamin' => isset($json_input['pemantauan_nifas_vitamin']) ? $json_input['pemantauan_nifas_vitamin'] : $existing_data->pemantauan_nifas_vitamin,
                'pemantauan_nifas_tablet_tambah_darah' => isset($json_input['pemantauan_nifas_tablet_tambah_darah']) ? $json_input['pemantauan_nifas_tablet_tambah_darah'] : $existing_data->pemantauan_nifas_tablet_tambah_darah,
                'pemantauan_nifas_gizi' => isset($json_input['pemantauan_nifas_gizi']) ? $json_input['pemantauan_nifas_gizi'] : $existing_data->pemantauan_nifas_gizi,
                'pemantauan_nifas_jiwa' => isset($json_input['pemantauan_nifas_jiwa']) ? $json_input['pemantauan_nifas_jiwa'] : $existing_data->pemantauan_nifas_jiwa,
                'pemantauan_nifas_demam' => isset($json_input['pemantauan_nifas_demam']) ? $json_input['pemantauan_nifas_demam'] : $existing_data->pemantauan_nifas_demam,
                'pemantauan_nifas_pusing' => isset($json_input['pemantauan_nifas_pusing']) ? $json_input['pemantauan_nifas_pusing'] : $existing_data->pemantauan_nifas_pusing,
                'pemantauan_nifas_pandangan_kabur' => isset($json_input['pemantauan_nifas_pandangan_kabur']) ? $json_input['pemantauan_nifas_pandangan_kabur'] : $existing_data->pemantauan_nifas_pandangan_kabur,
                'pemantauan_nifas_nyeri_uluhati' => isset($json_input['pemantauan_nifas_nyeri_uluhati']) ? $json_input['pemantauan_nifas_nyeri_uluhati'] : $existing_data->pemantauan_nifas_nyeri_uluhati,
                'pemantauan_nifas_jantung_berdebar' => isset($json_input['pemantauan_nifas_jantung_berdebar']) ? $json_input['pemantauan_nifas_jantung_berdebar'] : $existing_data->pemantauan_nifas_jantung_berdebar,
                'pemantauan_nifas_keluar_cairan' => isset($json_input['pemantauan_nifas_keluar_cairan']) ? $json_input['pemantauan_nifas_keluar_cairan'] : $existing_data->pemantauan_nifas_keluar_cairan,
                'pemantauan_nifas_napas_pendek' => isset($json_input['pemantauan_nifas_napas_pendek']) ? $json_input['pemantauan_nifas_napas_pendek'] : $existing_data->pemantauan_nifas_napas_pendek,
                'pemantauan_nifas_payudara_bengkak' => isset($json_input['pemantauan_nifas_payudara_bengkak']) ? $json_input['pemantauan_nifas_payudara_bengkak'] : $existing_data->pemantauan_nifas_payudara_bengkak,
                'pemantauan_nifas_susah_kencing' => isset($json_input['pemantauan_nifas_susah_kencing']) ? $json_input['pemantauan_nifas_susah_kencing'] : $existing_data->pemantauan_nifas_susah_kencing,
                'pemantauan_nifas_kelamin_bengkak' => isset($json_input['pemantauan_nifas_kelamin_bengkak']) ? $json_input['pemantauan_nifas_kelamin_bengkak'] : $existing_data->pemantauan_nifas_kelamin_bengkak,
                'pemantauan_nifas_darah_berbau' => isset($json_input['pemantauan_nifas_darah_berbau']) ? $json_input['pemantauan_nifas_darah_berbau'] : $existing_data->pemantauan_nifas_darah_berbau,
                'pemantauan_nifas_pendarahan' => isset($json_input['pemantauan_nifas_pendarahan']) ? $json_input['pemantauan_nifas_pendarahan'] : $existing_data->pemantauan_nifas_pendarahan,
                'pemantauan_nifas_keputihan' => isset($json_input['pemantauan_nifas_keputihan']) ? $json_input['pemantauan_nifas_keputihan'] : $existing_data->pemantauan_nifas_keputihan,
            ];

            // Update data
            $update_result = $this->BumilModel->update_nifas($nifas_id, $data);

            if ($update_result !== false) {
                $updated_nifas = $this->BumilModel->get_by_id_nifas($nifas_id);
                $this->send_response(200, 'Berhasil mengupdate data pemantauan nifas', $updated_nifas);
            } else {
                $this->send_response(500, 'Gagal mengupdate data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * DELETE - Hapus pemantauan nifas
     * Endpoint: /PemantauanNifas/delete/{nifas_id}
     */
    public function delete($nifas_id = null)
    {
        if (empty($nifas_id)) {
            $this->send_response(400, 'pemantauan_nifas_id harus diisi', null);
        }

        try {
            $existing_data = $this->mm->get('pemantauan_nifas', array(
                'where' => array('pemantauan_nifas_id' => $nifas_id)
            ), 'roar');
            
            if (empty($existing_data)) {
                $this->send_response(404, 'Data pemantauan nifas tidak ditemukan', null);
            }

            // Hapus data
            $delete_result = $this->BumilModel->delete_nifas($nifas_id);

            if ($delete_result) {
                $this->send_response(200, 'Berhasil menghapus data pemantauan nifas', null);
            } else {
                $this->send_response(500, 'Gagal menghapus data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }
}