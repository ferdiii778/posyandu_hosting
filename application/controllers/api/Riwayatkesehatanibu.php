<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * REST API Controller untuk Riwayat Kesehatan Ibu Hamil
 * Untuk Mobile Application
 */
class riwayatkesehatanibu extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('BumilModel');
        
        // Set header untuk JSON response
        header('Content-Type: application/json');
        
        // Enable CORS untuk mobile app
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    /**
     * Helper function untuk mengirim response JSON
     */
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
     * Validasi token atau session user (optional)
     * Implementasikan sesuai kebutuhan autentikasi Anda
     */
    private function validate_token()
    {
        // Contoh sederhana validasi token dari header
        $token = $this->input->get_request_header('Authorization', TRUE);
        
        if (empty($token)) {
            $this->send_response(401, 'Token tidak ditemukan', null);
        }
        
        // Validasi token di database atau JWT
        // Return user data jika valid
        // Untuk sementara return true
        return true;
    }

    /**
     * GET - Ambil semua riwayat kesehatan berdasarkan bumil_id
     * Endpoint: /api_riwayat_kesehatan_ibu/get_all/{bumil_id}
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

            // Ambil semua riwayat kesehatan
            $riwayat = $this->mm->get('riwayat_kesehatan_ibu', array(
                'where' => array('bumil_id' => $bumil_id),
                'order' => 'riwayat_kesehatan_ibu_id DESC'
            ));

            $this->send_response(200, 'Berhasil mengambil data', [
                'bumil' => $bumil,
                'riwayat' => $riwayat
            ]);

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * GET - Ambil detail riwayat kesehatan berdasarkan ID
     * Endpoint: /api_riwayat_kesehatan_ibu/get_by_id/{riwayat_id}
     */
    public function get_by_id($riwayat_id = null)
    {
        if (empty($riwayat_id)) {
            $this->send_response(400, 'riwayat_kesehatan_ibu_id harus diisi', null);
        }

        try {
            $riwayat = $this->BumilModel->get_by_id_riwayat_pemeriksaan($riwayat_id);
            
            if (empty($riwayat)) {
                $this->send_response(404, 'Data riwayat kesehatan tidak ditemukan', null);
            }

            $this->send_response(200, 'Berhasil mengambil data', $riwayat);

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * POST - Tambah riwayat kesehatan baru
     * Endpoint: /api_riwayat_kesehatan_ibu/create
     * 
     * Body JSON:
     * {
     *   "bumil_id": "1",
     *   "riwayat_kesehatan_ibu_usia": "20",
     *   "riwayat_kesehatan_ibu_kehamilan_ke": "1",
     *   "riwayat_kesehatan_ibu_jml_anak_hidup": "1",
     *   "riwayat_kesehatan_ibu_keguguran_jml": "3",
     *   "riwayat_kesehatan_ibu_penyakit": "tidak ada"
     * }
     */
    public function create()
    {
        try {
            // Ambil JSON input
            $json_input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($json_input)) {
                $this->send_response(400, 'Data tidak valid', null);
            }

            // Validasi field wajib
            $required_fields = ['bumil_id'];
            foreach ($required_fields as $field) {
                if (!isset($json_input[$field]) || empty($json_input[$field])) {
                    $this->send_response(400, "Field {$field} harus diisi", null);
                }
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
                'riwayat_kesehatan_ibu_usia' => isset($json_input['riwayat_kesehatan_ibu_usia']) ? $json_input['riwayat_kesehatan_ibu_usia'] : null,
                'riwayat_kesehatan_ibu_kehamilan_ke' => isset($json_input['riwayat_kesehatan_ibu_kehamilan_ke']) ? $json_input['riwayat_kesehatan_ibu_kehamilan_ke'] : null,
                'riwayat_kesehatan_ibu_jml_anak_hidup' => isset($json_input['riwayat_kesehatan_ibu_jml_anak_hidup']) ? $json_input['riwayat_kesehatan_ibu_jml_anak_hidup'] : null,
                'riwayat_kesehatan_ibu_keguguran_jml' => isset($json_input['riwayat_kesehatan_ibu_keguguran_jml']) ? $json_input['riwayat_kesehatan_ibu_keguguran_jml'] : null,
                'riwayat_kesehatan_ibu_penyakit' => isset($json_input['riwayat_kesehatan_ibu_penyakit']) ? $json_input['riwayat_kesehatan_ibu_penyakit'] : null,
            ];

            // Insert data
            $insert_result = $this->mm->save('riwayat_kesehatan_ibu', $data);

            if ($insert_result) {
                // Ambil data yang baru diinsert
                $new_riwayat = $this->mm->get('riwayat_kesehatan_ibu', array(
                    'where' => array('bumil_id' => $bumil_id),
                    'order' => 'riwayat_kesehatan_ibu_id DESC',
                    'limit' => '1'
                ), 'roar');

                $this->send_response(201, 'Berhasil menambah data riwayat kesehatan', $new_riwayat);
            } else {
                $this->send_response(500, 'Gagal menambah data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * PUT - Update riwayat kesehatan
     * Endpoint: /api_riwayat_kesehatan_ibu/update/{riwayat_id}
     * 
     * Body JSON:
     * {
     *   "riwayat_kesehatan_ibu_usia": "25",
     *   "riwayat_kesehatan_ibu_kehamilan_ke": "2",
     *   "riwayat_kesehatan_ibu_jml_anak_hidup": "1",
     *   "riwayat_kesehatan_ibu_keguguran_jml": "0",
     *   "riwayat_kesehatan_ibu_penyakit": "hipertensi"
     * }
     */
    public function update($riwayat_id = null)
    {
        if (empty($riwayat_id)) {
            $this->send_response(400, 'riwayat_kesehatan_ibu_id harus diisi', null);
        }

        try {
            // Cek apakah data ada
            $existing_data = $this->BumilModel->get_by_id_riwayat_pemeriksaan($riwayat_id);
            
            if (empty($existing_data)) {
                $this->send_response(404, 'Data riwayat kesehatan tidak ditemukan', null);
            }

            // Ambil JSON input
            $json_input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($json_input)) {
                $this->send_response(400, 'Data tidak valid', null);
            }

            // Siapkan data untuk update (hanya field yang dikirim)
            $data = [];
            
            $allowed_fields = [
                'riwayat_kesehatan_ibu_usia',
                'riwayat_kesehatan_ibu_kehamilan_ke',
                'riwayat_kesehatan_ibu_jml_anak_hidup',
                'riwayat_kesehatan_ibu_keguguran_jml',
                'riwayat_kesehatan_ibu_penyakit'
            ];

            foreach ($allowed_fields as $field) {
                if (isset($json_input[$field])) {
                    $data[$field] = $json_input[$field];
                }
            }

            if (empty($data)) {
                $this->send_response(400, 'Tidak ada data yang diupdate', null);
            }

            // Update data
            $update_result = $this->mm->save('riwayat_kesehatan_ibu', $data, array(
                'where' => array('riwayat_kesehatan_ibu_id' => $riwayat_id)
            ));

            if ($update_result !== false) {
                // Ambil data yang sudah diupdate
                $updated_riwayat = $this->BumilModel->get_by_id_riwayat_pemeriksaan($riwayat_id);
                
                $this->send_response(200, 'Berhasil mengupdate data riwayat kesehatan', $updated_riwayat);
            } else {
                $this->send_response(500, 'Gagal mengupdate data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * DELETE - Hapus riwayat kesehatan
     * Endpoint: /api_riwayat_kesehatan_ibu/delete/{riwayat_id}
     */
    public function delete($riwayat_id = null)
    {
        if (empty($riwayat_id)) {
            $this->send_response(400, 'riwayat_kesehatan_ibu_id harus diisi', null);
        }

        try {
            // Cek apakah data ada
            $existing_data = $this->BumilModel->get_by_id_riwayat_pemeriksaan($riwayat_id);
            
            if (empty($existing_data)) {
                $this->send_response(404, 'Data riwayat kesehatan tidak ditemukan', null);
            }

            // Hapus data
            $delete_result = $this->BumilModel->delete_riwayat_pemeriksaan($riwayat_id);

            if ($delete_result) {
                $this->send_response(200, 'Berhasil menghapus data riwayat kesehatan', null);
            } else {
                $this->send_response(500, 'Gagal menghapus data', null);
            }

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }

    /**
     * GET - Ambil statistik riwayat kesehatan per bumil
     * Endpoint: /api_riwayat_kesehatan_ibu/statistik/{bumil_id}
     */
    public function statistik($bumil_id = null)
    {
        if (empty($bumil_id)) {
            $this->send_response(400, 'bumil_id harus diisi', null);
        }

        try {
            // Ambil semua riwayat
            $riwayat = $this->mm->get('riwayat_kesehatan_ibu', array(
                'where' => array('bumil_id' => $bumil_id)
            ));

            if (empty($riwayat)) {
                $this->send_response(404, 'Belum ada data riwayat kesehatan', null);
            }

            // Hitung statistik
            $total_riwayat = count($riwayat);
            $total_anak_hidup = 0;
            $total_keguguran = 0;
            $ada_penyakit = 0;

            foreach ($riwayat as $item) {
                $total_anak_hidup += (int)$item['riwayat_kesehatan_ibu_jml_anak_hidup'];
                $total_keguguran += (int)$item['riwayat_kesehatan_ibu_keguguran_jml'];
                
                if (!empty($item['riwayat_kesehatan_ibu_penyakit']) && 
                    strtolower($item['riwayat_kesehatan_ibu_penyakit']) != 'tidak ada') {
                    $ada_penyakit++;
                }
            }

            $statistik = [
                'total_riwayat' => $total_riwayat,
                'total_anak_hidup' => $total_anak_hidup,
                'total_keguguran' => $total_keguguran,
                'memiliki_penyakit' => $ada_penyakit > 0 ? true : false,
                'jumlah_riwayat_penyakit' => $ada_penyakit
            ];

            $this->send_response(200, 'Berhasil mengambil statistik', $statistik);

        } catch (Exception $e) {
            $this->send_response(500, 'Terjadi kesalahan: ' . $e->getMessage(), null);
        }
    }
}