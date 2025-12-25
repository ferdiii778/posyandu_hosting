<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        header('Content-Type: application/json');
    }

    // ? GET /api/dashboard/summary
    public function summary()
    {
        // Default mode daily
        $mode = $this->input->get('mode');
        if (!$mode) {
            $mode = 'monthly';
        }

        $today = date('Y-m-d');
        $month = date('m');
        $year  = date('Y');

        // Tentukan kondisi WHERE sesuai mode
        if ($mode === 'monthly') {
            $where = "MONTH(tgl_timbang) = '" . $month . "' AND YEAR(tgl_timbang) = '" . $year . "'";
        } elseif ($mode === 'yearly') {
            $where = "YEAR(tgl_timbang) = '" . $year . "'";
        } else {
            $where = "DATE(tgl_timbang) = '" . $today . "'";
        }

        // 1. Jumlah pasien unik
        $query = $this->db->query("
            SELECT COUNT(DISTINCT nib) AS total
            FROM pemeriksaan
            WHERE $where
        ");
        $row = $query->row();
        $pasien = $row ? (int)$row->total : 0;

        // 2. Jumlah pemeriksaan
        $query = $this->db->query("
            SELECT COUNT(*) AS total
            FROM pemeriksaan
            WHERE $where
        ");
        $row = $query->row();
        $pemeriksaan = $row ? (int)$row->total : 0;

        // 3. Jumlah imunisasi
        $query = $this->db->query("
            SELECT COUNT(*) AS total
            FROM pemeriksaan
            WHERE $where AND id_jenis_imunisasi != 0
        ");
        $row = $query->row();
        $imunisasi = $row ? (int)$row->total : 0;

        // 4. Persentase kesehatan (anak dengan status_pemeriksaan = 'Normal')
        $queryTotal = $this->db->query("
            SELECT COUNT(*) AS total
            FROM pemeriksaan
            WHERE $where
        ");
        $queryNormal = $this->db->query("
            SELECT COUNT(*) AS normal_count
            FROM pemeriksaan
            WHERE $where AND LOWER(status_pemeriksaan) = 'normal'
        ");

        $totalRow  = $queryTotal->row();
        $normalRow = $queryNormal->row();

        $totalAll  = $totalRow ? (int)$totalRow->total : 0;
        $totalNormal = $normalRow ? (int)$normalRow->normal_count : 0;

        if ($totalAll > 0) {
            $avgKesehatan = round(($totalNormal / $totalAll) * 100, 2);
        } else {
            $avgKesehatan = 0;
        }

        // 5. Response JSON
        echo json_encode(array(
            'status' => true,
            'mode' => $mode,
            'data' => array(
                'pasien' => $pasien,
                'pemeriksaan' => $pemeriksaan,
                'imunisasi' => $imunisasi,
                'kesehatan' => $avgKesehatan . '%'
            )
        ));
    }

    // ? GET /api/dashboard/activities
    public function activities() {
        $activities = array();

        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

        // --- Imunisasi terbaru dalam 7 hari terakhir ---
        $imunisasi = $this->db->select('p.id_jenis_imunisasi, j.nama_imunisasi, p.tgl_timbang, b.nama_balita')
            ->from('pemeriksaan p')
            ->join('jenis_imunisasi j', 'p.id_jenis_imunisasi = j.id_jenis_imunisasi', 'left')
            ->join('balita b', 'p.nib = b.nib', 'left')
            ->where('p.id_jenis_imunisasi !=', 0)
            ->where('p.tgl_timbang >=', $sevenDaysAgo)
            ->order_by('p.tgl_timbang', 'DESC')
            ->get()
            ->result_array();

        foreach ($imunisasi as $i) {
            $nama_imunisasi = isset($i['nama_imunisasi']) ? $i['nama_imunisasi'] : '-';
            $nama_balita = isset($i['nama_balita']) ? $i['nama_balita'] : '-';
            
            $activities[] = array(
                'kategori' => 'Imunisasi',
                'judul' => 'Imunisasi ' . $nama_imunisasi,
                'subjudul' => 'Anak: ' . $nama_balita,
                'waktu' => $this->timeAgo($i['tgl_timbang'])
            );
        }

        // --- Pemeriksaan terbaru dalam 7 hari terakhir ---
        $pemeriksaan = $this->db->select('p.kode_pemeriksaan, b.nama_balita, p.tgl_timbang, p.status_gizi')
            ->from('pemeriksaan p')
            ->join('balita b', 'p.nib = b.nib', 'left')
            ->where('p.tgl_timbang >=', $sevenDaysAgo)
            ->order_by('p.tgl_timbang', 'DESC')
            ->get()
            ->result_array();

        foreach ($pemeriksaan as $p) {
            $status_gizi = !empty($p['status_gizi']) ? $p['status_gizi'] : 'Umum';
            $nama_balita = isset($p['nama_balita']) ? $p['nama_balita'] : '-';
            
            $activities[] = array(
                'kategori' => 'Pemeriksaan',
                'judul' => 'Pemeriksaan ' . $status_gizi,
                'subjudul' => 'Anak: ' . $nama_balita,
                'waktu' => $this->timeAgo($p['tgl_timbang'])
            );
        }

        $vitamin = $this->db->select('p.id_jenis_vitamin, v.nama_vitamin, p.tgl_timbang, b.nama_balita')
            ->from('pemeriksaan p')
            ->join('jenis_vitamin v', 'p.id_jenis_vitamin = v.id_jenis_vitamin', 'left')
            ->join('balita b', 'p.nib = b.nib', 'left')
            ->where('p.id_jenis_vitamin !=', 0)
            ->where('p.tgl_timbang >=', $sevenDaysAgo)
            ->order_by('p.tgl_timbang', 'DESC')
            ->get()
            ->result_array();

        foreach ($vitamin as $v) {
            $nama_vitamin = isset($v['nama_vitamin']) ? $v['nama_vitamin'] : '-';
            $nama_balita = isset($v['nama_balita']) ? $v['nama_balita'] : '-';
            
            $activities[] = array(
                'kategori' => 'Vitamin',
                'judul' => 'Pemberian Vitamin ' . $nama_vitamin,
                'subjudul' => 'Anak: ' . $nama_balita,
                'waktu' => $this->timeAgo($v['tgl_timbang'])
            );
        }

        // --- Urutkan hasil gabungan dari terbaru ---
        usort($activities, function($a, $b) {
            return 0; 
        });

        $activities = array_slice($activities, 0, 7);

        echo json_encode(array(
            'status' => true,
            'data' => $activities
        ));
    }

// ? GET /api/berita/user/{user_id}
public function berita_user($user_id = null)
{
    // Validasi user_id
    if (!$user_id) {
        echo json_encode(array(
            'status' => false,
            'message' => 'user_id tidak boleh kosong'
        ));
        return;
    }

    // Cari posyandu_id dari user
    $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
    
    if (!$posyandu_id) {
        echo json_encode(array(
            'status' => false,
            'message' => 'Data posyandu tidak ditemukan untuk user ini'
        ));
        return;
    }

    // Ambil berita berdasarkan posyandu_id - MIRIP DENGAN KODE LAMA
    $berita = $this->db->select('*')
        ->from('informasi')
        ->where('posyandu_id', $posyandu_id)
        ->where('YEAR(tgl_post)', date('Y')) // Sesuai dengan kode lama: year(tgl_post)
        ->order_by('tgl_post', 'DESC')
        ->get()
        ->result_array();

    // Format response
    $formatted_berita = array();
    foreach ($berita as $item) {
        $judul = isset($item['judul']) ? $item['judul'] : 'Judul Tidak Tersedia';
        $isi = isset($item['isi']) ? $item['isi'] : 'Konten tidak tersedia';
        $tanggal = isset($item['tgl_post']) ? $item['tgl_post'] : date('Y-m-d');
        $gambar = isset($item['gambar']) ? $item['gambar'] : null;
        
        // HAPUS TAG HTML DARI JUDUL DAN ISI
        $judul_clean = $this->_removeHtmlTags($judul);
        $isi_clean = $this->_removeHtmlTags($isi);
        
        $formatted_berita[] = array(
            'id' => $item['id_informasi'],
            'judul' => $judul_clean,
            'isi' => $isi_clean,
            'tanggal' => $tanggal,
            'gambar' => $gambar,
            'posyandu_id' => $item['posyandu_id']
        );
    }

    echo json_encode(array(
        'status' => true,
        'user_id' => $user_id,
        'posyandu_id' => $posyandu_id,
        'data' => $formatted_berita
    ));
}

// Helper function untuk menghapus tag HTML
private function _removeHtmlTags($text) {
    if (!$text) {
        return '';
    }
    
    // 1. Hapus semua tag HTML
    $clean = strip_tags($text);
    
    // 2. Hapus karakter khusus HTML entities
    $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
    
    // 3. Hapus multiple spaces dan trim
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);
    
    return $clean;
}

// Atau jika ingin lebih spesifik hanya menghapus tag <p> dan </p> saja:
private function _removePTags($text) {
    if (!$text) {
        return '';
    }
    
    // Hapus tag <p> dan </p> secara spesifik
    $clean = str_replace('<p>', '', $text);
    $clean = str_replace('</p>', '', $clean);
    $clean = str_replace('<P>', '', $clean);
    $clean = str_replace('</P>', '', $clean);
    
    // Hapus multiple spaces dan trim
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);
    
    return $clean;
}
    // ? GET /api/jadwal/user/{user_id}
    public function jadwal_user($user_id = null)
    {
        // Validasi user_id
        if (!$user_id) {
            echo json_encode(array(
                'status' => false,
                'message' => 'user_id tidak boleh kosong'
            ));
            return;
        }

        // Cari posyandu_id dari user
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        
        if (!$posyandu_id) {
            echo json_encode(array(
                'status' => false,
                'message' => 'Data posyandu tidak ditemukan untuk user ini'
            ));
            return;
        }

        $bulan_ini = date('m');
        $tahun_ini = date('Y');

        // Ambil jadwal berdasarkan posyandu_id - MIRIP DENGAN KODE LAMA
        $jadwal = $this->db->select('*')
            ->from('jadwal_pemeriksaan')
            ->where('posyandu_id', $posyandu_id)
            ->where('MONTH(tgl_jadwal)', $bulan_ini) // Sesuai dengan kode lama: month(tgl_jadwal)
            ->where('YEAR(tgl_jadwal)', $tahun_ini)  // Sesuai dengan kode lama: year(tgl_jadwal)
            ->where('tgl_jadwal >=', date('Y-m-d')) // Hanya jadwal yang belum lewat
            ->order_by('tgl_jadwal', 'ASC')
            ->get()
            ->result_array();

        // Format response
        $formatted_jadwal = array();
        foreach ($jadwal as $item) {
            $jenis_pemeriksaan = isset($item['jenis_pemeriksaan']) ? $item['jenis_pemeriksaan'] : 'Pemeriksaan Umum';
            $tanggal = isset($item['tgl_jadwal']) ? $item['tgl_jadwal'] : date('Y-m-d');
            $waktu = isset($item['waktu']) ? $item['waktu'] : '08:00 - 12:00';
            $tempat = isset($item['tempat']) ? $item['tempat'] : 'Posyandu';
            $keterangan = isset($item['keterangan']) ? $item['keterangan'] : '';
            
            $formatted_jadwal[] = array(
                'id' => $item['id_jadwal'],
                'jenis_pemeriksaan' => $jenis_pemeriksaan,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $tempat,
                'keterangan' => $keterangan,
                'posyandu_id' => $item['posyandu_id']
            );
        }

        echo json_encode(array(
            'status' => true,
            'user_id' => $user_id,
            'posyandu_id' => $posyandu_id,
            'bulan' => $bulan_ini,
            'tahun' => $tahun_ini,
            'data' => $formatted_jadwal
        ));
    }

    // ? GET /api/dashboard/member/{user_id}
    public function dashboard_member($user_id = null)
    {
        // Validasi user_id
        if (!$user_id) {
            echo json_encode(array(
                'status' => false,
                'message' => 'user_id tidak boleh kosong'
            ));
            return;
        }

        // Cari posyandu_id dari user
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        
        if (!$posyandu_id) {
            echo json_encode(array(
                'status' => false,
                'message' => 'Data posyandu tidak ditemukan untuk user ini'
            ));
            return;
        }

        // Ambil berita
        $berita = $this->db->select('*')
            ->from('informasi')
            ->where('posyandu_id', $posyandu_id)
            ->where('YEAR(tgl_post)', date('Y'))
            ->order_by('tgl_post', 'DESC')
            ->limit(5)
            ->get()
            ->result_array();

        // Ambil jadwal
        $bulan_ini = date('m');
        $tahun_ini = date('Y');
        
        $jadwal = $this->db->select('*')
            ->from('jadwal_pemeriksaan')
            ->where('posyandu_id', $posyandu_id)
            ->where('MONTH(tgl_jadwal)', $bulan_ini)
            ->where('YEAR(tgl_jadwal)', $tahun_ini)
            ->where('tgl_jadwal >=', date('Y-m-d'))
            ->order_by('tgl_jadwal', 'ASC')
            ->limit(5)
            ->get()
            ->result_array();

        // Format response
        $formatted_berita = array();
        foreach ($berita as $item) {
            $judul = isset($item['judul']) ? $item['judul'] : 'Judul Tidak Tersedia';
            $isi = isset($item['isi']) ? $item['isi'] : 'Konten tidak tersedia';
            $tanggal = isset($item['tgl_post']) ? $item['tgl_post'] : date('Y-m-d');
            $gambar = isset($item['gambar']) ? $item['gambar'] : null;
            
            $formatted_berita[] = array(
                'id' => $item['id_informasi'],
                'judul' => $judul,
                'isi' => $isi,
                'tanggal' => $tanggal,
                'gambar' => $gambar
            );
        }

        $formatted_jadwal = array();
        foreach ($jadwal as $item) {
            $jenis_pemeriksaan = isset($item['jenis_pemeriksaan']) ? $item['jenis_pemeriksaan'] : 'Pemeriksaan Umum';
            $tanggal = isset($item['tgl_jadwal']) ? $item['tgl_jadwal'] : date('Y-m-d');
            $waktu = isset($item['waktu']) ? $item['waktu'] : '08:00 - 12:00';
            $tempat = isset($item['tempat']) ? $item['tempat'] : 'Posyandu';
            $keterangan = isset($item['keterangan']) ? $item['keterangan'] : '';
            
            $formatted_jadwal[] = array(
                'id' => $item['id_jadwal'],
                'jenis_pemeriksaan' => $jenis_pemeriksaan,
                'tanggal' => $tanggal,
                'waktu' => $waktu,
                'tempat' => $tempat,
                'keterangan' => $keterangan
            );
        }

        echo json_encode(array(
            'status' => true,
            'user_id' => $user_id,
            'posyandu_id' => $posyandu_id,
            'data' => array(
                'berita' => $formatted_berita,
                'jadwal' => $formatted_jadwal
            )
        ));
    }

    // Method untuk debug - tampilkan semua data
    public function debug_user($user_id = null)
    {
        if (!$user_id) {
            echo "user_id tidak boleh kosong";
            return;
        }

        echo "<h3>Debug Data untuk user_id: " . $user_id . "</h3>";

        // Cari di user table
        echo "<h4>Data dari tabel user:</h4>";
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('username', $user_id);
        $this->db->or_where('id_user', $user_id);
        $user_data = $this->db->get()->result_array();
        echo "<pre>";
        print_r($user_data);
        echo "</pre>";

        // Cari di orang_tua table
        echo "<h4>Data dari tabel orang_tua:</h4>";
        $this->db->select('*');
        $this->db->from('orang_tua');
        $this->db->where('username', $user_id);
        $this->db->or_where('id_orang_tua', $user_id);
        $orang_tua_data = $this->db->get()->result_array();
        echo "<pre>";
        print_r($orang_tua_data);
        echo "</pre>";

        // Test _getPosyanduIdByUserId
        echo "<h4>Hasil _getPosyanduIdByUserId:</h4>";
        $posyandu_id = $this->_getPosyanduIdByUserId($user_id);
        echo "posyandu_id: " . ($posyandu_id ? $posyandu_id : 'Tidak ditemukan');

        // Test data berita
        if ($posyandu_id) {
            echo "<h4>Data berita untuk posyandu_id " . $posyandu_id . ":</h4>";
            $berita = $this->db->select('*')
                ->from('informasi')
                ->where('posyandu_id', $posyandu_id)
                ->where('YEAR(tgl_post)', date('Y'))
                ->get()
                ->result_array();
            echo "<pre>";
            print_r($berita);
            echo "</pre>";

            echo "<h4>Data jadwal untuk posyandu_id " . $posyandu_id . ":</h4>";
            $jadwal = $this->db->select('*')
                ->from('jadwal_pemeriksaan')
                ->where('posyandu_id', $posyandu_id)
                ->where('MONTH(tgl_jadwal)', date('m'))
                ->where('YEAR(tgl_jadwal)', date('Y'))
                ->get()
                ->result_array();
            echo "<pre>";
            print_r($jadwal);
            echo "</pre>";
        }
    }

    // Helper function untuk mendapatkan posyandu_id dari user_id - DIPERBAIKI
    private function _getPosyanduIdByUserId($user_id)
    {
        // DEBUG: Tampilkan user_id yang diterima
        error_log("Mencari posyandu_id untuk user_id: " . $user_id);

        // 1. Cari di tabel user berdasarkan username (seperti kode lama)
        $this->db->select('posyandu_id, role, username, id_user');
        $this->db->from('user');
        $this->db->where('username', $user_id);
        $user = $this->db->get()->row_array();

        if ($user && !empty($user['posyandu_id'])) {
            error_log("Found in user table - posyandu_id: " . $user['posyandu_id'] . ", role: " . $user['role']);
            return $user['posyandu_id'];
        }

        // 2. Jika tidak ditemukan, coba berdasarkan id_user (jika user_id adalah numeric)
        if (is_numeric($user_id)) {
            $this->db->select('posyandu_id, role, username, id_user');
            $this->db->from('user');
            $this->db->where('id_user', $user_id);
            $user = $this->db->get()->row_array();

            if ($user && !empty($user['posyandu_id'])) {
                error_log("Found in user table by id - posyandu_id: " . $user['posyandu_id'] . ", role: " . $user['role']);
                return $user['posyandu_id'];
            }
        }

        // 3. Jika role adalah 'member', cari di tabel orang_tua (seperti kode lama)
        if ($user && $user['role'] == 'member') {
            error_log("User role is member, searching in orang_tua table...");
            
            $this->db->select('posyandu_id, username, id_orang_tua');
            $this->db->from('orang_tua');
            $this->db->where('username', $user_id);
            $orang_tua = $this->db->get()->row_array();

            if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                error_log("Found in orang_tua table - posyandu_id: " . $orang_tua['posyandu_id']);
                return $orang_tua['posyandu_id'];
            }

            // Coba dengan id_orang_tua jika user_id numeric
            if (is_numeric($user_id)) {
                $this->db->select('posyandu_id, username, id_orang_tua');
                $this->db->from('orang_tua');
                $this->db->where('id_orang_tua', $user_id);
                $orang_tua = $this->db->get()->row_array();

                if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                    error_log("Found in orang_tua table by id - posyandu_id: " . $orang_tua['posyandu_id']);
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
            error_log("Found in orang_tua table (direct search) - posyandu_id: " . $orang_tua['posyandu_id']);
            return $orang_tua['posyandu_id'];
        }

        // Coba dengan id_orang_tua jika user_id numeric
        if (is_numeric($user_id)) {
            $this->db->select('posyandu_id, username, id_orang_tua');
            $this->db->from('orang_tua');
            $this->db->where('id_orang_tua', $user_id);
            $orang_tua = $this->db->get()->row_array();

            if ($orang_tua && !empty($orang_tua['posyandu_id'])) {
                error_log("Found in orang_tua table by id (direct search) - posyandu_id: " . $orang_tua['posyandu_id']);
                return $orang_tua['posyandu_id'];
            }
        }

        error_log("Posyandu_id NOT found for user_id: " . $user_id);
        return null;
    }

    private function timeAgo($datetime) {
        if (!$datetime) return '-';
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', $timestamp);
    }
}