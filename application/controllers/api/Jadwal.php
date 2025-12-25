<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mjadwal');
        $this->load->library('upload');
        $this->load->database();
        header('Content-Type: application/json');
    }

    // 1. GET Daftar Posyandu (Untuk pilihan di dropdown website)
    public function get_posyandu() {
        $data = $this->db->get('ref_posyandu')->result_array();
        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET semua jadwal
    public function index() {
        // Pastikan nama kolom 'nama_posyandu' sesuai dengan yang ada di tabel ref_posyandu
        // Jika di tabel ref_posyandu nama kolomnya adalah 'nama', ubah menjadi 'ref_posyandu.nama'
        $this->db->select('jadwal_pemeriksaan.*, ref_posyandu.posyandu_nama as nama_posyandu'); 
        $this->db->from('jadwal_pemeriksaan');
        $this->db->join('ref_posyandu', 'ref_posyandu.posyandu_id = jadwal_pemeriksaan.posyandu_id', 'left');
        $data = $this->db->get()->result_array();
    
        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jadwal by ID
    public function detail($id) {
        $data = $this->Mjadwal->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jadwal pemeriksaan tidak ditemukan'
            ]);
        }
    }

    // GET jadwal by user_id (MIRIP DENGAN DASHBOARD)
    public function user($user_id = null) {
        // Validasi user_id
        if (!$user_id) {
            echo json_encode([
                'status' => false,
                'message' => 'user_id tidak boleh kosong'
            ]);
            return;
        }

        // Cari posyandu_id dari user
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        
        if (!$posyandu_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Data posyandu tidak ditemukan untuk user ini'
            ]);
            return;
        }

        $bulan_ini = date('m');
        $tahun_ini = date('Y');

        // Ambil jadwal berdasarkan posyandu_id
        $jadwal = $this->db->select('*')
            ->from('jadwal_pemeriksaan')
            ->where('posyandu_id', $posyandu_id)
            ->where('MONTH(tgl_jadwal)', $bulan_ini)
            ->where('YEAR(tgl_jadwal)', $tahun_ini)
            ->where('tgl_jadwal >=', date('Y-m-d')) // Hanya jadwal yang belum lewat
            ->order_by('tgl_jadwal', 'ASC')
            ->get()
            ->result_array();

        // Format response - SESUAIKAN DENGAN STRUKTUR TABEL
        $formatted_jadwal = array();
        foreach ($jadwal as $item) {
            // Gunakan field yang sesuai dengan tabel jadwal_pemeriksaan
            $id_jadwal = isset($item['id_jadwal_pemeriksaan']) ? $item['id_jadwal_pemeriksaan'] : 
                        (isset($item['id_jadwal']) ? $item['id_jadwal'] : 
                        (isset($item['id']) ? $item['id'] : null));
            
            $jenis_pemeriksaan = isset($item['jenis_pemeriksaan']) ? $item['jenis_pemeriksaan'] : 'Pemeriksaan Umum';
            $tanggal = isset($item['tgl_jadwal']) ? $item['tgl_jadwal'] : date('Y-m-d');
            $waktu = isset($item['waktu']) ? $item['waktu'] : '08:00 - 12:00';
            $tempat = isset($item['tempat']) ? $item['tempat'] : 'Posyandu';
            $keterangan = isset($item['keterangan']) ? $item['keterangan'] : '';
            
            $formatted_jadwal[] = array(
                'id' => $id_jadwal,
                'jenis_pemeriksaan' => $jenis_pemeriksaan,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $tempat,
                'keterangan' => $keterangan,
                'posyandu_id' => $item['posyandu_id']
            );
        }

        echo json_encode([
            'status' => true,
            'user_id' => $user_id,
            'posyandu_id' => $posyandu_id,
            'bulan' => $bulan_ini,
            'tahun' => $tahun_ini,
            'data' => $formatted_jadwal
        ]);
    }

    // GET jadwal by user_id dengan filter bulan dan tahun
    public function user_filter($user_id = null) {
        // Validasi user_id
        if (!$user_id) {
            echo json_encode([
                'status' => false,
                'message' => 'user_id tidak boleh kosong'
            ]);
            return;
        }

        // Ambil parameter filter dari query string
        $bulan = $this->input->get('bulan') ? $this->input->get('bulan') : date('m');
        $tahun = $this->input->get('tahun') ? $this->input->get('tahun') : date('Y');

        // Cari posyandu_id dari user
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        
        if (!$posyandu_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Data posyandu tidak ditemukan untuk user ini'
            ]);
            return;
        }

        // Ambil jadwal berdasarkan posyandu_id dengan filter
        $jadwal = $this->db->select('*')
            ->from('jadwal_pemeriksaan')
            ->where('posyandu_id', $posyandu_id)
            ->where('MONTH(tgl_jadwal)', $bulan)
            ->where('YEAR(tgl_jadwal)', $tahun)
            ->where('tgl_jadwal >=', date('Y-m-d')) // Hanya jadwal yang belum lewat
            ->order_by('tgl_jadwal', 'ASC')
            ->get()
            ->result_array();

        // Format response - SESUAIKAN DENGAN STRUKTUR TABEL
        $formatted_jadwal = array();
        foreach ($jadwal as $item) {
            // Gunakan field yang sesuai dengan tabel jadwal_pemeriksaan
            $id_jadwal = isset($item['id_jadwal_pemeriksaan']) ? $item['id_jadwal_pemeriksaan'] : 
                        (isset($item['id_jadwal']) ? $item['id_jadwal'] : 
                        (isset($item['id']) ? $item['id'] : null));
            
            $jenis_pemeriksaan = isset($item['jenis_pemeriksaan']) ? $item['jenis_pemeriksaan'] : 'Pemeriksaan Umum';
            $tanggal = isset($item['tgl_jadwal']) ? $item['tgl_jadwal'] : date('Y-m-d');
            $waktu = isset($item['waktu']) ? $item['waktu'] : '08:00 - 12:00';
            $tempat = isset($item['tempat']) ? $item['tempat'] : 'Posyandu';
            $keterangan = isset($item['keterangan']) ? $item['keterangan'] : '';
            
            $formatted_jadwal[] = array(
                'id' => $id_jadwal,
                'jenis_pemeriksaan' => $jenis_pemeriksaan,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $tempat,
                'keterangan' => $keterangan,
                'posyandu_id' => $item['posyandu_id']
            );
        }

        echo json_encode([
            'status' => true,
            'user_id' => $user_id,
            'posyandu_id' => $posyandu_id,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $formatted_jadwal
        ]);
    }

    // Method untuk debug - lihat struktur tabel jadwal_pemeriksaan
    public function debug_table($user_id = null) {
        if (!$user_id) {
            echo "user_id tidak boleh kosong";
            return;
        }

        echo "<h3>Debug Struktur Tabel jadwal_pemeriksaan untuk user_id: " . $user_id . "</h3>";

        // Cari posyandu_id dari user
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        
        if (!$posyandu_id) {
            echo "Data posyandu tidak ditemukan untuk user ini";
            return;
        }

        echo "<h4>Struktur kolom tabel jadwal_pemeriksaan:</h4>";
        $fields = $this->db->field_data('jadwal_pemeriksaan');
        echo "<pre>";
        print_r($fields);
        echo "</pre>";

        echo "<h4>Contoh data dari tabel jadwal_pemeriksaan untuk posyandu_id " . $posyandu_id . ":</h4>";
        $jadwal = $this->db->select('*')
            ->from('jadwal_pemeriksaan')
            ->where('posyandu_id', $posyandu_id)
            ->limit(5)
            ->get()
            ->result_array();
        echo "<pre>";
        print_r($jadwal);
        echo "</pre>";
    }

    // CREATE jadwal baru
    public function create() {
        // Karena ada upload file/foto, kita gunakan $_POST bukan json_decode php://input
        $jadwal_nama      = $this->input->post('jadwal_nama'); // Judul
        $posyandu_id      = $this->input->post('posyandu_id'); // Pilihan Posyandu
        $tgl_jadwal       = $this->input->post('tgl_jadwal');  // Tanggal
        $jam_mulai        = $this->input->post('jam_mulai');   // Jam Mulai
        $jam_selese       = $this->input->post('jam_selese');  // Jam Selesai
        $jadwal_deskripsi = $this->input->post('jadwal_deskripsi'); // Deskripsi

        // Konfigurasi Upload Foto
        $config['upload_path']   = './images/jadwal';
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['file_name']     = 'jadwal_'.time(); // Nama file unik berdasarkan waktu

        $this->upload->initialize($config);

        $foto_name = null;
        if (!empty($_FILES['jadwal_foto']['name'])) {
            if ($this->upload->do_upload('jadwal_foto')) {
                $uploadData = $this->upload->data();
                $foto_name = $uploadData['file_name'];
            }
        }

        $data = [
            'jadwal_nama'      => $jadwal_nama,
            'posyandu_id'      => $posyandu_id,
            'tgl_jadwal'       => $tgl_jadwal,
            'jam_mulai'        => $jam_mulai,
            'jam_selese'       => $jam_selese,
            'jadwal_deskripsi' => $jadwal_deskripsi,
            'jadwal_foto'      => $foto_name
        ];

        $insert = $this->Mjadwal->insert($data);

        if ($insert) {
            echo json_encode(['status' => true, 'message' => 'Jadwal berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menambah jadwal']);
        }
    }

    // UPDATE jadwal
    // Di dalam file api/Jadwal.php
    
    public function update($id) {
        // 1. WAJIB: Load library upload terlebih dahulu agar tidak null
        $this->load->library('upload'); 
    
        // 2. Ambil data teks dari Multipart Flutter
        $data = [
            'jadwal_nama'      => $this->input->post('jadwal_nama'),
            'posyandu_id'      => $this->input->post('posyandu_id'),
            'tgl_jadwal'       => $this->input->post('tgl_jadwal'),
            'jam_mulai'        => $this->input->post('jam_mulai'),
            'jam_selese'       => $this->input->post('jam_selese'),
            'jadwal_deskripsi' => $this->input->post('jadwal_deskripsi'),
        ];
    
        // 3. Konfigurasi Upload
        $config['upload_path']   = './images/jadwal';
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['file_name']     = 'jadwal_'.time();
    
        $this->upload->initialize($config); // Sekarang ini tidak akan error lagi
    
        if (!empty($_FILES['jadwal_foto']['name'])) {
            if ($this->upload->do_upload('jadwal_foto')) {
                $photo = $this->upload->data();
                $data['jadwal_foto'] = $photo['file_name'];
            }
        }
    
        // 4. Update ke Database
        $update = $this->Mjadwal->update($id, $data);
    
        if ($update) {
            echo json_encode(['status' => true, 'message' => 'Berhasil update']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal update database']);
        }
    }

    // DELETE, DETAIL, dan HELPER tetap sama seperti kode sebelumnya...
    public function delete($id) {
        $delete = $this->Mjadwal->delete($id);
        echo json_encode(['status' => $delete, 'message' => $delete ? 'Berhasil hapus' : 'Gagal hapus']);
    }

    // Helper function untuk mendapatkan posyandu_id dari user_id
    private function _getPosyanduIdByUserId($user_id)
    {
        // 1. Cari di tabel user berdasarkan username
        $this->db->select('posyandu_id, role, username, id_user');
        $this->db->from('user');
        $this->db->where('username', $user_id);
        $user = $this->db->get()->row_array();

        if ($user && !empty($user['posyandu_id'])) {
            return $user['posyandu_id'];
        }

        // 2. Jika tidak ditemukan, coba berdasarkan id_user (jika user_id adalah numeric)
        if (is_numeric($user_id)) {
            $this->db->select('posyandu_id, role, username, id_user');
            $this->db->from('user');
            $this->db->where('id_user', $user_id);
            $user = $this->db->get()->row_array();

            if ($user && !empty($user['posyandu_id'])) {
                return $user['posyandu_id'];
            }
        }

        // 3. Jika role adalah 'member', cari di tabel orang_tua
        if ($user && $user['role'] == 'member') {
            $this->db->select('posyandu_id, username, id_orang_tua');
            $this->db->from('orang_tua');
            $this->db->where('username', $user_id);
            $orang_tua = $this->db->get()->row_array();

            if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                return $orang_tua['posyandu_id'];
            }

            // Coba dengan id_orang_tua jika user_id numeric
            if (is_numeric($user_id)) {
                $this->db->select('posyandu_id, username, id_orang_tua');
                $this->db->from('orang_tua');
                $this->db->where('id_orang_tua', $user_id);
                $orang_tua = $this->db->get()->row_array();

                if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                    return $orang_tua['posyandu_id'];
                }
            }
        }

        // 4. Jika masih tidak ditemukan, cari langsung di orang_tua tanpa memandang role
        $this->db->select('posyandu_id, username, id_orang_tua');
        $this->db->from('orang_tua');
        $this->db->where('username', $user_id);
        $orang_tua = $this->db->get()->row_array();

        if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
            return $orang_tua['posyandu_id'];
        }

        // Coba dengan id_orang_tua jika user_id numeric
        if (is_numeric($user_id)) {
            $this->db->select('posyandu_id, username, id_orang_tua');
            $this->db->from('orang_tua');
            $this->db->where('id_orang_tua', $user_id);
            $orang_tua = $this->db->get()->row_array();

            if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                return $orang_tua['posyandu_id'];
            }
        }

        return null;
    }
}