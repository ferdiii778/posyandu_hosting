<?php
defined('BASEPATH') or exit('No direct script access allowed');

class detailtimsus extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('modelsapi/Mdetailtimsus'); // Load Model dengan nama yang benar
        
        // Set JSON header
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }

    // GET: Ambil detail timsus beserta posyandu yang ditugaskan
    public function index($timsus_id)
    {
        try {
            // Get info timsus menggunakan Model
            $timsus = $this->Mdetailtimsus->get_timsus_info($timsus_id);
            
            if (!$timsus) {
                $response = [
                    'status' => false,
                    'message' => 'Data timsus tidak ditemukan',
                    'data' => null
                ];
                http_response_code(404);
                echo json_encode($response);
                return;
            }

            // Get detail posyandu yang ditugaskan menggunakan Model
            $detail_timsus = $this->Mdetailtimsus->get_detail_by_timsus_id($timsus_id);
            
            $response = [
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => [
                    'timsus' => $timsus,
                    'detail_timsus' => $detail_timsus
                ]
            ];
            
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'data' => null
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }

    // GET: Ambil semua posyandu (untuk checkbox)
    public function get_posyandu()
    {
        try {
            // Get all posyandu menggunakan Model
            $posyandu = $this->Mdetailtimsus->get_all_posyandu();
            
            $response = [
                'status' => true,
                'message' => 'Data posyandu berhasil diambil',
                'data' => $posyandu
            ];
            
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'data' => []
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }

    // POST: Simpan detail timsus (posyandu yang ditugaskan)
    public function save_detail()
    {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true);

            // Log input untuk debugging
            log_message('debug', 'Save Detail Input: ' . $json);

            if (empty($obj['timsus_id'])) {
                $response = [
                    'status' => false,
                    'message' => 'ID Timsus tidak boleh kosong'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            $timsus_id = $obj['timsus_id'];
            $posyandu_ids = isset($obj['posyandu_ids']) ? $obj['posyandu_ids'] : [];

            // Cek apakah timsus ada menggunakan Model
            $timsus_info = $this->Mdetailtimsus->get_timsus_info($timsus_id);
            if (!$timsus_info) {
                $response = [
                    'status' => false,
                    'message' => 'Data timsus tidak ditemukan'
                ];
                http_response_code(404);
                echo json_encode($response);
                return;
            }

            // Hapus detail lama menggunakan Model
            $this->Mdetailtimsus->delete_by_timsus_id($timsus_id);

            // Insert detail baru menggunakan Model
            if (!empty($posyandu_ids)) {
                $data_batch = [];
                foreach ($posyandu_ids as $posyandu_id) {
                    $data_batch[] = [
                        'timsus_id' => $timsus_id,
                        'posyandu_id' => $posyandu_id
                    ];
                }
                
                // Insert batch
                $result = $this->Mdetailtimsus->insert_batch($data_batch);
                
                if (!$result) {
                    $response = [
                        'status' => false,
                        'message' => 'Gagal menyimpan detail timsus'
                    ];
                    http_response_code(500);
                    echo json_encode($response);
                    return;
                }
            }

            $response = [
                'status' => true,
                'message' => 'Detail timsus berhasil disimpan'
            ];
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'Save Detail Error: ' . $e->getMessage());
            
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }

    // DELETE: Hapus satu posyandu dari detail timsus
    public function delete_detail($detail_timsus_id)
    {
        try {
            // Log untuk debugging
            log_message('debug', 'Delete Detail ID: ' . $detail_timsus_id);

            // Cek apakah data ada menggunakan Model
            $data = $this->Mdetailtimsus->get_by_id($detail_timsus_id);
            
            if (!$data) {
                $response = [
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ];
                http_response_code(404);
                echo json_encode($response);
                return;
            }

            // Delete menggunakan Model
            $result = $this->Mdetailtimsus->delete($detail_timsus_id);
            
            if ($result) {
                $response = [
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ];
                http_response_code(200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Gagal menghapus data'
                ];
                http_response_code(500);
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'Delete Detail Error: ' . $e->getMessage());
            
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }
}