<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Balita extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mbalita');
        $this->load->database();
        header('Content-Type: application/json');
    }

    private function auth() {
        $token = $this->input->get_request_header('X-Token');
        $expired = $this->input->get_request_header('X-Expired');
    
        // Token & expired wajib
        if (!$token || !$expired) {
            return ['status'=>false,'message'=>'Token diperlukan'];
        }
    
        // Expired check
        if ($expired < time()) {
            return ['status'=>false,'message'=>'Token expired'];
        }
    
        // Signature sudah tidak dipakai
        return ['status'=>true];
    }

    private function getUserFromToken() {
        $user_id = $this->input->get_request_header('X-User-Id');
        if (!$user_id) return null;
        return $this->db->get_where('user', ['id_user' => $user_id])->row_array();
    }

    // ?? Get semua NIB balita dari user (bisa lebih dari satu balita)
    private function get_all_balita_nib_from_user($user_id)
    {
        $nib_list = array();
        
        // Dapatkan username dari tabel user
        $this->db->select('username');
        $this->db->where('id_user', $user_id);
        $user_query = $this->db->get('user');
        
        if ($user_query->num_rows() > 0) {
            $user_data = $user_query->row();
            $username = $user_data->username;
            
            // Cari id_orang_tua berdasarkan username di tabel orang_tua
            $this->db->select('id_orang_tua');
            $this->db->where('username', $username);
            $orang_tua_query = $this->db->get('orang_tua');
            
            if ($orang_tua_query->num_rows() > 0) {
                $orang_tua_data = $orang_tua_query->row();
                $id_orang_tua = $orang_tua_data->id_orang_tua;
                
                // Cari semua NIB dari tabel ortu_bayi berdasarkan id_orang_tua
                $this->db->select('nib');
                $this->db->where('id_orang_tua', $id_orang_tua);
                $ortu_bayi_query = $this->db->get('ortu_bayi');
                
                foreach ($ortu_bayi_query->result() as $row) {
                    $nib_list[] = $row->nib;
                }
            }
        }
        return $nib_list;
    }

    private function check_permission($action, $user)
    {
        // Admin boleh semua
        if ($user['role'] == 'admin') {
            return true;
        }
    
        // Kader tidak boleh hapus balita posyandu lain (cek ada di controller)
        if ($user['role'] == 'kader') {
            return true; // pengecekan posyandu sudah ada di delete()
        }
    
        // Member hanya boleh delete balita miliknya
        if ($user['role'] == 'member') {
            if ($action == 'delete') {
                return true; // pengecekan kepemilikan dilakukan di delete()
            }
        }
    
        return false;
    }

    public function nextNib() {
        $newNib = $this->Mbalita->nib(); // auto generate NIB
        echo json_encode([
            "status" => true,
            "nib" => $newNib
        ]);
    }

    // =======================
    // ?? BALITA SECTION - DIMODIFIKASI UNTUK ROLE-BASED
    // =======================

    public function index()
    {
        // Auth token dulu
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // Ambil user dari DB
        $user = $this->getUserFromToken();
        if (!$user) {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
            return;
        }
    
        $role = $user['role'];
        $posyandu_id = $user['posyandu_id'];
    
        // Hasil data balita
        $rows = [];
    
        if ($role == 'admin') {
    
            // ⭐ Admin melihat semua balita
            $rows = $this->Mbalita->getAll();
    
        } elseif ($role == 'kader') {
    
            // ⭐ Kader harus punya posyandu_id
            if (!$posyandu_id) {
                echo json_encode(['status' => false, 'message' => 'Posyandu ID tidak ditemukan']);
                return;
            }
    
            // ⭐ Kader melihat data balita berdasarkan posyandu_id
            $rows = $this->Mbalita->getByPosyandu($posyandu_id);
    
        } elseif ($role == 'member') {
    
            // ⭐ Member melihat data balita anaknya sendiri
            $nib_list = $this->get_all_balita_nib_from_user($user['id_user']);
    
            if (!empty($nib_list)) {
                $this->db->select('b.*, rp.posyandu_nama');
                $this->db->from('balita b');
                $this->db->join('ref_posyandu rp', 'b.posyandu_id = rp.posyandu_id', 'left');
                $this->db->where_in('b.nib', $nib_list);
                $rows = $this->db->get()->result_array();
            } else {
                $rows = [];
            }
    
        } else {
            echo json_encode(['status' => false, 'message' => 'Role tidak dikenali']);
            return;
        }
    
    
        // ========================
        // FORMAT OUTPUT
        // ========================
    
        $result = [];
    
        foreach ($rows as $row) {
    
            $tgl_lahir = !empty($row['tgl_lahir']) ? new DateTime($row['tgl_lahir']) : new DateTime('1970-01-01');
            $diff = (new DateTime())->diff($tgl_lahir);
            $umur = "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";
    
            $result[] = [
                'nib'            => $row['nib'],
                'nama_balita'    => $row['nama_balita'],
                'tgl_lahir'      => date('d-m-Y', strtotime($row['tgl_lahir'])),
                'umur'           => $umur,
                'jenis_kelamin'  => $row['jenis_kelamin'] == 'L' ? 'Laki-Laki' : 'Perempuan',
                'nama_ibu'       => $row['nama_ibu'],
                'nama_ayah'      => $row['nama_ayah'],
                'alamat'         => $row['alamat'],
                'panjang_badan'  => $row['panjang_badan'],
                'berat_lahir'    => $row['berat_lahir'],
                'lingkar_kepala' => $row['lingkar_kepala'],
                'nik_balita'     => $row['nik_balita'],
                'no_kk'          => $row['no_kk'],
                'anak_ke'        => $row['anak_ke'],
                'latitude'       => $row['latitude'],
                'longitude'      => $row['longitude'],
                'posyandu'       => $row['posyandu_nama'],
                'no_akta'        => $row['no_akta'],
                'goldar'         => $row['goldar'],
                'faskes1'        => $row['faskes1'],
                'status'         => $row['is_meninggal'] == 1 ? 'Sudah Meninggal' : 'Hidup'
            ];
        }
    
        echo json_encode([
            'status' => true,
            'role' => $role,
            'data' => $result
        ]);
    }
    

    public function detail($nib) {
        $row = $this->Mbalita->getByNib($nib);
    
        if (!$row) {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }
    
        $tgl_lahir = new DateTime($row['tgl_lahir']);
        $today     = new DateTime();
        $diff      = $today->diff($tgl_lahir);
        $umur      = $diff->y." Tahun ".$diff->m." Bulan ".$diff->d." Hari";
    
        $data = [
            'nib'            => $row['nib'],
            'nama_balita'    => $row['nama_balita'],
            'tgl_lahir'      => date('d-m-Y', strtotime($row['tgl_lahir'])),
            'umur'           => $umur,
            'jenis_kelamin'  => ($row['jenis_kelamin'] == 'L' ? 'Laki-Laki' : 'Perempuan'),
            'nama_ibu'       => $row['nama_ibu'],
            'nama_ayah'      => $row['nama_ayah'],
            'alamat'         => $row['alamat'],
            'panjang_badan'  => $row['panjang_badan'],
            'berat_lahir'    => $row['berat_lahir'],
            'lingkar_kepala' => $row['lingkar_kepala'],
            'nik_balita'     => $row['nik_balita'],
            'no_kk'          => $row['no_kk'],
            'anak_ke'        => $row['anak_ke'],
            'latitude'       => $row['latitude'],
            'longitude'      => $row['longitude'],
            'posyandu'       => $row['posyandu_nama'],
            'no_akta'        => $row['no_akta'],
            'goldar'         => $row['goldar'],
            'faskes1'        => $row['faskes1'],
            'status'         => ($row['is_meninggal'] == 1 ? 'Sudah Meninggal' : 'Hidup')
        ];
    
        echo json_encode(['status' => true, 'data' => $data]);
    }
    
    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        $newNib = $this->Mbalita->nib();
    
        $data = [
            'nib'            => $newNib,
            'nama_balita'    => $input['nama_balita'],
            'tgl_lahir'      => $input['tgl_lahir'],
            'jenis_kelamin'  => ($input['jenis_kelamin'] == 'Laki-Laki' ? 'L' : 'P'),
            'nama_ibu'       => $input['nama_ibu'],
            'nama_ayah'      => $input['nama_ayah'],
            'alamat'         => $input['alamat'],
            'panjang_badan'  => $input['panjang_badan'],
            'berat_lahir'    => $input['berat_lahir'],
            'lingkar_kepala' => $input['lingkar_kepala'],
            'nik_balita'     => $input['nik_balita'],
            'no_kk'          => $input['no_kk'],
            'anak_ke'        => $input['anak_ke'],
            'no_akta'        => $input['no_akta'],
            'goldar'         => $input['goldar'],
            'faskes1'        => $input['faskes1'],
            'latitude'       => $input['latitude'],
            'longitude'      => $input['longitude'],
            'posyandu_id'    => $input['posyandu_id'],
            'is_meninggal'   => 0,
        ];
    
        $success = $this->Mbalita->insert($data);
    
        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data balita berhasil dibuat' : 'Gagal membuat balita',
            'nib' => $newNib
        ]);
    }

    public function update($nib) {
        $input = json_decode(file_get_contents("php://input"), true);
    
        $data_update = [
            'nama_balita'    => $input['nama_balita'],
            'tgl_lahir'      => $input['tgl_lahir'],
            'jenis_kelamin'  => ($input['jenis_kelamin'] == 'Laki-Laki' ? 'L' : 'P'),
            'nama_ibu'       => $input['nama_ibu'],
            'nama_ayah'      => $input['nama_ayah'],
            'alamat'         => $input['alamat'],
            'panjang_badan'  => $input['panjang_badan'],
            'berat_lahir'    => $input['berat_lahir'],
            'lingkar_kepala' => $input['lingkar_kepala'],
            'nik_balita'     => $input['nik_balita'],
            'no_kk'          => $input['no_kk'],
            'anak_ke'        => $input['anak_ke'],
            'no_akta'        => $input['no_akta'],
            'goldar'         => $input['goldar'],
            'faskes1'        => $input['faskes1'],
            'latitude'       => $input['latitude'],
            'longitude'      => $input['longitude'],
            'posyandu_id'    => $input['posyandu_id'],
        ];
    
        $success = $this->Mbalita->update($nib, $data_update);
        $updated = $this->Mbalita->getByNib($nib);
    
        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data berhasil diupdate' : 'Gagal update data',
            'data' => $updated
        ]);
    }

    public function delete($nib) {
        $user_data = $this->getUserFromToken();
    
        if (!$this->check_permission('delete', $user_data)) {
            echo json_encode(['status' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus data']);
            return;
        }
    
        // Pastikan balita ada
        $row = $this->Mbalita->getByNib($nib);
        if (!$row) {
            echo json_encode(['status' => false, 'message' => 'Data balita tidak ditemukan']);
            return;
        }
    
        // Validasi member
        if ($user_data['role'] == 'member') {
            $user_nib_list = $this->get_all_balita_nib_from_user($user_data['user_id']);
            if (!in_array($nib, $user_nib_list)) {
                echo json_encode(['status' => false, 'message' => 'Anda hanya bisa menghapus data sendiri']);
                return;
            }
        }
    
        // Validasi kader
        if ($user_data['role'] == 'kader') {
            if ($row['posyandu_id'] != $user_data['posyandu_id']) {
                echo json_encode(['status' => false, 'message' => 'Anda tidak memiliki akses ke data ini']);
                return;
            }
        }
    
        // Eksekusi delete
        $success = $this->Mbalita->delete($nib);
    
        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data berhasil dihapus' : 'Gagal hapus data'
        ]);
}


    // =======================
    // ?? PEMERIKSAAN SECTION - DIMODIFIKASI UNTUK ROLE-BASED
    // =======================

    public function pemeriksaan($nib) {
    
        // Ambil balita
        $row = $this->Mbalita->getByNib($nib);
    
        if (!$row) {
            echo json_encode(['status' => false, 'message' => 'Data balita tidak ditemukan']);
            return;
        }
    
        // Ambil pemeriksaan
        $rows = $this->Mbalita->getPemeriksaanAll($nib);
    
        if ($rows) {
            echo json_encode([
                'status' => true,
                'data'   => $rows
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Belum ada data pemeriksaan'
            ]);
        }
    }
    

    // ? TAMBAH PEMERIKSAAN
    public function tambah_pemeriksaan() {
        $input = json_decode(file_get_contents("php://input"), true);
        $kode_pemeriksaan = $this->Mbalita->kodePemeriksaan();

        $balita = $this->db->get_where('balita', ['nib' => $input['nib']])->row_array();
        if (!$balita) {
            echo json_encode(['status' => false, 'message' => 'Balita tidak ditemukan']);
            return;
        }

        // hitung umur
        $tgl_lahir = new DateTime($balita['tgl_lahir']);
        $tgl_timbang = new DateTime($input['tgl_timbang']);
        $umur_diff = $tgl_timbang->diff($tgl_lahir);
        $umur_bulan = ($umur_diff->y * 12) + $umur_diff->m;
        $umur_tahun = $umur_diff->y;
        $umur_text = $umur_diff->y." Tahun ".$umur_diff->m." Bulan ".$umur_diff->d." Hari";

        // referensi WHO
        if ($balita['jenis_kelamin'] == 'L') {
            $ref = $this->db->get_where('ref_bb_u_laki', ['bb_u_laki_nama' => $umur_bulan])->row_array();
            $min2 = $ref['bb_u_laki_min2sd'];
            $min1 = $ref['bb_u_laki_min1sd'];
            $plus2 = $ref['bb_u_laki_plus2sd'];
        } else {
            $ref = $this->db->get_where('ref_bb_u_perempuan', ['bb_u_perempuan_nama' => $umur_bulan])->row_array();
            $min2 = $ref['bb_u_perempuan_min2sd'];
            $min1 = $ref['bb_u_perempuan_min1sd'];
            $plus2 = $ref['bb_u_perempuan_plus2sd'];
        }

        // logika status
        if ($input['berat_badan'] <= $min2) {
            $status = 'Stunting'; $warna = '#ff0000'; $icon = 'assets/icons/marker-red.png';
        } elseif ($input['berat_badan'] < $min1) {
            $status = 'Warning'; $warna = '#fffe00'; $icon = 'assets/icons/marker-yellow.png';
        } elseif ($input['berat_badan'] > $plus2) {
            $status = 'Obesitas'; $warna = '#1767ed'; $icon = 'assets/icons/marker-blue-32.png';
        } else {
            $status = 'Normal'; $warna = '#32a910'; $icon = 'assets/icons/marker-green.png';
        }

        // simpan
        $data = [
            'kode_pemeriksaan' => $kode_pemeriksaan,
            'nib' => $input['nib'],
            'tgl_timbang' => $input['tgl_timbang'],
            'berat_badan' => $input['berat_badan'],
            'panjang_badan' => $input['panjang_badan'],
            'lingkar_perut' => isset($input['lingkar_perut']) ? $input['lingkar_perut'] : '',
            'id_jenis_imunisasi' => isset($input['id_jenis_imunisasi']) ? $input['id_jenis_imunisasi'] : '',
            'id_jenis_vitamin' => isset($input['id_jenis_vitamin']) ? $input['id_jenis_vitamin'] : '',
            'saran' => isset($input['saran']) ? $input['saran'] : '',
            'umur' => $umur_text,
            'umur_tahun' => $umur_tahun,
            'umur_bulan' => $umur_bulan,
            'status_gizi' => 0,
            'status_gizi_normal' => 0,
            'status_pemeriksaan' => $status,
            'status_warna' => $warna,
            'keterangan_pemeriksaan' => 'sehat',
            'icon_marker' => $icon,
            'nama_pemeriksa' => isset($input['nama_pemeriksa']) ? $input['nama_pemeriksa'] : ''
        ];

        $success = $this->Mbalita->insertPemeriksaan($data);

        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Pemeriksaan berhasil ditambahkan' : 'Gagal menambahkan pemeriksaan',
            'data' => $data
        ]);
    }

    // ? UPDATE PEMERIKSAAN
    public function update_pemeriksaan($kode) {
        $input = json_decode(file_get_contents("php://input"), true);
        $pemeriksaan = $this->db->get_where('pemeriksaan', ['kode_pemeriksaan' => $kode])->row_array();
        if (!$pemeriksaan) {
            echo json_encode(['status' => false, 'message' => 'Data pemeriksaan tidak ditemukan']);
            return;
        }

        $balita = $this->db->get_where('balita', ['nib' => $pemeriksaan['nib']])->row_array();
        if (!$balita) {
            echo json_encode(['status' => false, 'message' => 'Balita tidak ditemukan']);
            return;
        }

        $tgl_lahir = new DateTime($balita['tgl_lahir']);
        $tgl_timbang = new DateTime($input['tgl_timbang']);
        $umur_diff = $tgl_timbang->diff($tgl_lahir);
        $umur_bulan = ($umur_diff->y * 12) + $umur_diff->m;
        $umur_tahun = $umur_diff->y;
        $umur_text = $umur_diff->y." Tahun ".$umur_diff->m." Bulan ".$umur_diff->d." Hari";

        if ($balita['jenis_kelamin'] == 'L') {
            $ref = $this->db->get_where('ref_bb_u_laki', ['bb_u_laki_nama' => $umur_bulan])->row_array();
            $min2 = $ref['bb_u_laki_min2sd'];
            $min1 = $ref['bb_u_laki_min1sd'];
            $plus2 = $ref['bb_u_laki_plus2sd'];
        } else {
            $ref = $this->db->get_where('ref_bb_u_perempuan', ['bb_u_perempuan_nama' => $umur_bulan])->row_array();
            $min2 = $ref['bb_u_perempuan_min2sd'];
            $min1 = $ref['bb_u_perempuan_min1sd'];
            $plus2 = $ref['bb_u_perempuan_plus2sd'];
        }

        if ($input['berat_badan'] <= $min2) {
            $status = 'Stunting'; $warna = '#ff0000'; $icon = 'assets/icons/marker-red.png';
        } elseif ($input['berat_badan'] < $min1) {
            $status = 'Warning'; $warna = '#fffe00'; $icon = 'assets/icons/marker-yellow.png';
        } elseif ($input['berat_badan'] > $plus2) {
            $status = 'Obesitas'; $warna = '#1767ed'; $icon = 'assets/icons/marker-blue-32.png';
        } else {
            $status = 'Normal'; $warna = '#32a910'; $icon = 'assets/icons/marker-green.png';
        }

        $data_update = [
            'tgl_timbang' => $input['tgl_timbang'],
            'berat_badan' => $input['berat_badan'],
            'panjang_badan' => $input['panjang_badan'],
            'lingkar_perut' => isset($input['lingkar_perut']) ? $input['lingkar_perut'] : '',
            'id_jenis_imunisasi' => isset($input['id_jenis_imunisasi']) ? $input['id_jenis_imunisasi'] : '',
            'id_jenis_vitamin' => isset($input['id_jenis_vitamin']) ? $input['id_jenis_vitamin'] : '',
            'saran' => isset($input['saran']) ? $input['saran'] : '',
            'umur' => $umur_text,
            'umur_tahun' => $umur_tahun,
            'umur_bulan' => $umur_bulan,
            'status_gizi' => 0,
            'status_gizi_normal' => 0,
            'status_pemeriksaan' => $status,
            'status_warna' => $warna,
            'keterangan_pemeriksaan' => 'sehat',
            'icon_marker' => $icon,
            'nama_pemeriksa' => isset($input['nama_pemeriksa']) ? $input['nama_pemeriksa'] : ''
        ];

        $success = $this->Mbalita->updatePemeriksaan($kode, $data_update);

        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Pemeriksaan berhasil diupdate' : 'Gagal update pemeriksaan',
            'data' => $data_update
        ]);
    }

    // =======================
    // ?? GRAFIK PERKEMBANGAN BALITA
    // =======================
    public function grafik_perkembangan($nib) {
        $balita = $this->db->get_where('balita', ['nib' => $nib])->row_array();
        if (!$balita) {
            echo json_encode(['status' => false, 'message' => 'Data balita tidak ditemukan']);
            return;
        }
    
        // ambil tabel referensi WHO sesuai jenis kelamin
        if ($balita['jenis_kelamin'] == 'L') {
            $ref = $this->db->get('ref_bb_u_laki')->result_array();
        } else {
            $ref = $this->db->get('ref_bb_u_perempuan')->result_array();
        }
    
        // siapkan struktur data Z-Score
        $series = [
            ['name' => 'Z-Score +3', 'data' => [], 'color' => '#0164c0'],
            ['name' => 'Z-Score +2', 'data' => [], 'color' => '#0164c0'],
            ['name' => 'Z-Score +1', 'data' => [], 'color' => '#0164c0'],
            ['name' => 'Z-Score 0 (Median)', 'data' => [], 'color' => '#5cb85c'],
            ['name' => 'Z-Score -1', 'data' => [], 'color' => '#f26159'],
            ['name' => 'Z-Score -2', 'data' => [], 'color' => '#f26159'],
            ['name' => 'Z-Score -3', 'data' => [], 'color' => '#f26159'],
        ];
    
        // isi data z-score
        foreach ($ref as $r) {
            if ($balita['jenis_kelamin'] == 'L') {
                $umur = (int)$r['bb_u_laki_nama'];
                $series[0]['data'][] = [$umur, (float)$r['bb_u_laki_plus3sd']];
                $series[1]['data'][] = [$umur, (float)$r['bb_u_laki_plus2sd']];
                $series[2]['data'][] = [$umur, (float)$r['bb_u_laki_plus1sd']];
                $series[3]['data'][] = [$umur, (float)$r['bb_u_laki_median']];
                $series[4]['data'][] = [$umur, (float)$r['bb_u_laki_min1sd']];
                $series[5]['data'][] = [$umur, (float)$r['bb_u_laki_min2sd']];
                $series[6]['data'][] = [$umur, (float)$r['bb_u_laki_min3sd']];
            } else {
                $umur = (int)$r['bb_u_perempuan_nama'];
                $series[0]['data'][] = [$umur, (float)$r['bb_u_perempuan_plus3sd']];
                $series[1]['data'][] = [$umur, (float)$r['bb_u_perempuan_plus2sd']];
                $series[2]['data'][] = [$umur, (float)$r['bb_u_perempuan_plus1sd']];
                $series[3]['data'][] = [$umur, (float)$r['bb_u_perempuan_median']];
                $series[4]['data'][] = [$umur, (float)$r['bb_u_perempuan_min1sd']];
                $series[5]['data'][] = [$umur, (float)$r['bb_u_perempuan_min2sd']];
                $series[6]['data'][] = [$umur, (float)$r['bb_u_perempuan_min3sd']];
            }
        }
    
        // ambil data pemeriksaan balita
        $pemeriksaan = $this->db->get_where('pemeriksaan', ['nib' => $nib])->result_array();
        $child_data = [];
        foreach ($pemeriksaan as $p) {
            $child_data[] = [(int)$p['umur_bulan'], (float)$p['berat_badan']];
        }
    
        // tambahkan garis data anak ke series
        $child_series = [
            'name' => 'Data Pemeriksaan Anak',
            'type' => 'line',
            'data' => $child_data,
            'color' => '#FFC107',
            'lineWidth' => 3
        ];
        $series[] = $child_series;
    
        echo json_encode([
            'status' => true,
            'nib' => $nib,
            'nama_balita' => $balita['nama_balita'],
            'chart_data' => $series
        ]);
    }


    public function hapus_pemeriksaan($kode) {
        $success = $this->Mbalita->deletePemeriksaan($kode);
        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data pemeriksaan berhasil dihapus' : 'Gagal hapus pemeriksaan'
        ]);
    }

    // Dropdown data
    public function jenis_vitamin() {
        $result = $this->db->get('jenis_vitamin')->result_array();
        echo json_encode(['status' => true, 'data' => $result]);
    }

    public function jenis_imunisasi() {
        $result = $this->db->get('jenis_imunisasi')->result_array();
        echo json_encode(['status' => true, 'data' => $result]);
    }
}