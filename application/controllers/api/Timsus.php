<?php
defined('BASEPATH') or exit('No direct script access allowed');

class timsus extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        // JANGAN load model dulu, kita query manual untuk memastikan JOIN berfungsi
        
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

    // GET: Ambil semua data timsus dengan JOIN MANUAL
    public function index()
    {
        try {
            // Query langsung dengan JOIN - PASTI INCLUDE ref_timsus_nama
            $query = "SELECT 
                        timsus.timsus_id,
                        timsus.timsus_nama,
                        timsus.timsus_telp,
                        timsus.ref_timsus_id,
                        ref_timsus.ref_timsus_nama
                      FROM timsus
                      LEFT JOIN ref_timsus ON ref_timsus.ref_timsus_id = timsus.ref_timsus_id
                      ORDER BY timsus.timsus_id ASC";
            
            $result = $this->db->query($query);
            $timsus = $result->result_array();
            
            $response = [
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $timsus
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

    // GET: Ambil data ref_timsus untuk dropdown
    public function get_ref_timsus()
    {
        try {
            $this->db->select('*');
            $this->db->from('ref_timsus');
            $this->db->order_by('ref_timsus_id', 'ASC');
            $query = $this->db->get();
            $ref_timsus = $query->result_array();
            
            $response = [
                'status' => true,
                'message' => 'Data ref_timsus berhasil diambil',
                'data' => $ref_timsus
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

    // GET: Ambil data timsus by ID
    public function get_by_id($id)
    {
        try {
            $this->db->where('timsus_id', $id);
            $query = $this->db->get('timsus');
            $data = $query->row_array();
            
            if ($data) {
                $response = [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => $data
                ];
                http_response_code(200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null
                ];
                http_response_code(404);
            }
            
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

    // POST: Tambah data timsus
    public function create()
    {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true);

            // Validasi input
            if (empty($obj['timsus_nama'])) {
                $response = [
                    'status' => false,
                    'message' => 'Nama timsus tidak boleh kosong'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            if (empty($obj['timsus_telp'])) {
                $response = [
                    'status' => false,
                    'message' => 'Telp tidak boleh kosong'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            if (empty($obj['ref_timsus_id'])) {
                $response = [
                    'status' => false,
                    'message' => 'Tim Khusus harus dipilih'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            $data = [
                'timsus_nama' => trim($obj['timsus_nama']),
                'timsus_telp' => trim($obj['timsus_telp']),
                'ref_timsus_id' => $obj['ref_timsus_id'],
            ];

            $this->db->insert('timsus', $data);
            
            if ($this->db->affected_rows() > 0) {
                $response = [
                    'status' => true,
                    'message' => 'Data berhasil ditambahkan'
                ];
                http_response_code(200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Gagal menambahkan data ke database'
                ];
                http_response_code(500);
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }

    // POST: Update data timsus
    public function update($id = null)
    {
        try {
            // Get JSON input
            $json = file_get_contents('php://input');
            $obj = json_decode($json, true);

            // Get ID from URL or JSON
            if ($id === null && isset($obj['timsus_id'])) {
                $id = $obj['timsus_id'];
            }

            if ($id === null || empty($id)) {
                $response = [
                    'status' => false,
                    'message' => 'ID timsus tidak ditemukan'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            // Validasi input
            if (empty($obj['timsus_nama'])) {
                $response = [
                    'status' => false,
                    'message' => 'Nama timsus tidak boleh kosong'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            if (empty($obj['timsus_telp'])) {
                $response = [
                    'status' => false,
                    'message' => 'Telp tidak boleh kosong'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            if (empty($obj['ref_timsus_id'])) {
                $response = [
                    'status' => false,
                    'message' => 'Tim Khusus harus dipilih'
                ];
                http_response_code(400);
                echo json_encode($response);
                return;
            }

            // Cek apakah data ada
            $this->db->where('timsus_id', $id);
            $check = $this->db->get('timsus');
            
            if ($check->num_rows() == 0) {
                $response = [
                    'status' => false,
                    'message' => 'Data dengan ID ' . $id . ' tidak ditemukan'
                ];
                http_response_code(404);
                echo json_encode($response);
                return;
            }

            $data = [
                'timsus_nama' => trim($obj['timsus_nama']),
                'timsus_telp' => trim($obj['timsus_telp']),
                'ref_timsus_id' => $obj['ref_timsus_id'],
            ];

            $this->db->where('timsus_id', $id);
            $this->db->update('timsus', $data);

            // Selalu return success jika tidak ada error database
            $response = [
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ];
            http_response_code(200);
            
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }

    // POST: Hapus data timsus
    public function delete($id)
    {
        try {
            // Cek apakah data ada
            $this->db->where('timsus_id', $id);
            $check = $this->db->get('timsus');
            
            if ($check->num_rows() == 0) {
                $response = [
                    'status' => false,
                    'message' => 'Data dengan ID ' . $id . ' tidak ditemukan'
                ];
                http_response_code(404);
                echo json_encode($response);
                return;
            }

            $this->db->where('timsus_id', $id);
            $this->db->delete('timsus');
            
            if ($this->db->affected_rows() > 0) {
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
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            
            http_response_code(500);
            echo json_encode($response);
        }
    }
}