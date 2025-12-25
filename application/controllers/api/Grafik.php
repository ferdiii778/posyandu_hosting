<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grafik extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        header('Content-Type: application/json');
    }

    /**
     * GET /api/grafik
     * Optional query param:
     *  - tahun=YYYY  -> return data per-month for that year (Jan..Dec)
     *  - if no tahun -> return data per-year (using ref_tahun > 2019)
     */
    public function index() {
        $tahunParam = $this->input->get('tahun');

        if ($tahunParam && preg_match('/^\d{4}$/', $tahunParam)) {
            // Monthly mode for the given year (1..12)
            $tahun = (int)$tahunParam;
            $this->output_monthly($tahun);
        } else {
            // Yearly mode (default)
            $this->output_yearly();
        }
    }

    /**
     * Build monthly data for a specific year (months 1..12)
     */
    private function output_monthly($tahun) {
        // Convert to integer untuk memastikan
        $tahun = (int)$tahun;
        
        // Prepare month labels
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        // initialize arrays with zeros
        $kelahiran = array_fill(1, 12, 0);
        $kematian = array_fill(1, 12, 0);
        $normal = array_fill(1, 12, 0);
        $warning = array_fill(1, 12, 0);
        $stunting = array_fill(1, 12, 0);
        $imun1 = array_fill(1, 12, 0);
        $imun2 = array_fill(1, 12, 0);
        $imun3 = array_fill(1, 12, 0);
        $imun4 = array_fill(1, 12, 0);
        $bumil = array_fill(1, 12, 0);
        $pemeriksaan_bumil = array_fill(1, 12, 0);

        // KELAHIRAN per month (based on balita.tgl_lahir)
        $kel = $this->db
            ->select('MONTH(tgl_lahir) as bulan, COUNT(nib) as jml', false)
            ->where('YEAR(tgl_lahir)', $tahun)
            ->group_by('MONTH(tgl_lahir)')
            ->get('balita')
            ->result_array();

        foreach ($kel as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) $kelahiran[$b] = (int)$r['jml'];
        }

        // KEMATIAN per month
        $kem = $this->db
            ->select('MONTH(tgl_kematian) as bulan, COUNT(nib) as jml', false)
            ->where('YEAR(tgl_kematian)', $tahun)
            ->group_by('MONTH(tgl_kematian)')
            ->get('kematian')
            ->result_array();

        foreach ($kem as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) $kematian[$b] = (int)$r['jml'];
        }

        // STATUS GIZI per month (from pemeriksaan.tgl_timbang, grouped by status_pemeriksaan)
        $gizi = $this->db
            ->select('MONTH(tgl_timbang) as bulan,
                      SUM(IF(status_pemeriksaan = "Normal",1,0)) as jml_normal,
                      SUM(IF(status_pemeriksaan = "Warning",1,0)) as jml_warning,
                      SUM(IF(status_pemeriksaan = "Stunting",1,0)) as jml_stunting', false)
            ->where('YEAR(tgl_timbang)', $tahun)
            ->group_by('MONTH(tgl_timbang)')
            ->get('pemeriksaan')
            ->result_array();

        foreach ($gizi as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) {
                $normal[$b] = (int)$r['jml_normal'];
                $warning[$b] = (int)$r['jml_warning'];
                $stunting[$b] = (int)$r['jml_stunting'];
            }
        }

        // IMUNISASI per month - we derive from pemeriksaan.id_jenis_imunisasi
        $im = $this->db
            ->select('MONTH(tgl_timbang) as bulan,
                SUM(IF(p.id_jenis_imunisasi = 1,1,0)) as imun1,
                SUM(IF(p.id_jenis_imunisasi = 3,1,0)) as imun2,
                SUM(IF(p.id_jenis_imunisasi = 4,1,0)) as imun3,
                SUM(IF(p.id_jenis_imunisasi = 5,1,0)) as imun4', false)
            ->from('pemeriksaan p')
            ->where('YEAR(tgl_timbang)', $tahun)
            ->group_by('MONTH(tgl_timbang)')
            ->get()
            ->result_array();

        foreach ($im as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) {
                $imun1[$b] = (int)$r['imun1'];
                $imun2[$b] = (int)$r['imun2'];
                $imun3[$b] = (int)$r['imun3'];
                $imun4[$b] = (int)$r['imun4'];
            }
        }

        // DATA IBU HAMIL per month - gunakan data real berdasarkan bulan
        $bumil_data = $this->db
            ->select('MONTH(bumil_ttl) as bulan, COUNT(bumil_id) as jml', false)
            ->where('YEAR(bumil_ttl)', $tahun)
            ->group_by('MONTH(bumil_ttl)')
            ->get('bumil')
            ->result_array();

        foreach ($bumil_data as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) $bumil[$b] = (int)$r['jml'];
        }

        // PEMERIKSAAN IBU HAMIL per month
        $pemeriksaan_bumil_data = $this->db
            ->select('MONTH(pemeriksaan_kehamilan_tgl) as bulan, COUNT(pemeriksaan_kehamilan_id) as jml', false)
            ->where('YEAR(pemeriksaan_kehamilan_tgl)', $tahun)
            ->group_by('MONTH(pemeriksaan_kehamilan_tgl)')
            ->get('pemeriksaan_kehamilan')
            ->result_array();

        foreach ($pemeriksaan_bumil_data as $r) {
            $b = (int)$r['bulan'];
            if ($b >=1 && $b <=12) $pemeriksaan_bumil[$b] = (int)$r['jml'];
        }

        // prepare output arrays in order Jan..Dec
        $labels = [];
        $kelahiran_series = [];
        $kematian_series = [];
        $normal_series = [];
        $warning_series = [];
        $stunting_series = [];
        $imun1_series = [];
        $imun2_series = [];
        $imun3_series = [];
        $imun4_series = [];
        $bumil_series = [];
        $pemeriksaan_bumil_series = [];

        for ($m=1; $m<=12; $m++) {
            $labels[] = $months[$m];
            $kelahiran_series[] = (int)$kelahiran[$m];
            $kematian_series[] = (int)$kematian[$m];
            $normal_series[] = (int)$normal[$m];
            $warning_series[] = (int)$warning[$m];
            $stunting_series[] = (int)$stunting[$m];
            $imun1_series[] = (int)$imun1[$m];
            $imun2_series[] = (int)$imun2[$m];
            $imun3_series[] = (int)$imun3[$m];
            $imun4_series[] = (int)$imun4[$m];
            $bumil_series[] = (int)$bumil[$m];
            $pemeriksaan_bumil_series[] = (int)$pemeriksaan_bumil[$m];
        }

        echo json_encode([
            'status' => true,
            'mode' => 'monthly',
            'tahun' => $tahun,
            'labels' => $labels, // Jan..Dec
            'kelahiran' => $kelahiran_series,
            'kematian' => $kematian_series,
            'status_gizi' => [
                'normal' => $normal_series,
                'warning' => $warning_series,
                'stunting' => $stunting_series
            ],
            'imunisasi' => [
                'imunisasi1' => $imun1_series,
                'imunisasi2' => $imun2_series,
                'imunisasi3' => $imun3_series,
                'imunisasi4' => $imun4_series
            ],
            'ibu_hamil' => [
                'jumlah_bumil' => $bumil_series,
                'pemeriksaan_bumil' => $pemeriksaan_bumil_series
            ]
        ]);
    }

    /**
     * Build yearly data using ref_tahun (>2019)
     */
    private function output_yearly() {
        $tahun_sekarang = date('Y');
        
        // years master from ref_tahun - hanya sampai tahun ini
        $tahunQuery = $this->db
            ->select('tahun_nama')
            ->where('tahun_nama >', 2019)
            ->where('tahun_nama <=', $tahun_sekarang) // Hanya sampai tahun sekarang
            ->order_by('tahun_nama', 'ASC')
            ->get('ref_tahun')
            ->result_array();

        $tahun_master = [];
        foreach ($tahunQuery as $t) {
            $tahun_master[] = (string)$t['tahun_nama'];
        }

        // init series maps keyed by year
        $jml_kelahiran_series = array_fill_keys($tahun_master, 0);
        $jml_kematian_series = array_fill_keys($tahun_master, 0);
        $data_gizi_by_tahun = [];
        $jml_bumil_series = array_fill_keys($tahun_master, 0);
        $jml_pemeriksaan_bumil_series = array_fill_keys($tahun_master, 0);
        
        foreach ($tahun_master as $y) {
            $data_gizi_by_tahun[$y] = ['normal'=>0,'warning'=>0,'stunting'=>0];
        }

        // KELAHIRAN per year (balita.tgl_lahir)
        $kelahiran = $this->db
            ->select('YEAR(tgl_lahir) as tahun, COUNT(nib) as jml', false)
            ->group_by('YEAR(tgl_lahir)')
            ->get('balita')
            ->result_array();

        foreach ($kelahiran as $r) {
            $y = (string)$r['tahun'];
            if (array_key_exists($y, $jml_kelahiran_series)) {
                $jml_kelahiran_series[$y] = (int)$r['jml'];
            }
        }

        // KEMATIAN per year
        $kematian = $this->db
            ->select('YEAR(tgl_kematian) as tahun, COUNT(nib) as jml', false)
            ->group_by('YEAR(tgl_kematian)')
            ->get('kematian')
            ->result_array();

        foreach ($kematian as $r) {
            $y = (string)$r['tahun'];
            if (array_key_exists($y, $jml_kematian_series)) {
                $jml_kematian_series[$y] = (int)$r['jml'];
            }
        }

        // STATUS GIZI per year
        $status_gizi = $this->db
            ->select('YEAR(tgl_timbang) as tahun,
                SUM(IF(status_pemeriksaan = "Normal",1,0)) as jml_normal,
                SUM(IF(status_pemeriksaan = "Warning",1,0)) as jml_warning,
                SUM(IF(status_pemeriksaan = "Stunting",1,0)) as jml_stunting', false)
            ->group_by('YEAR(tgl_timbang)')
            ->get('pemeriksaan')
            ->result_array();

        foreach ($status_gizi as $r) {
            $y = (string)$r['tahun'];
            if (isset($data_gizi_by_tahun[$y])) {
                $data_gizi_by_tahun[$y]['normal'] = (int)$r['jml_normal'];
                $data_gizi_by_tahun[$y]['warning'] = (int)$r['jml_warning'];
                $data_gizi_by_tahun[$y]['stunting'] = (int)$r['jml_stunting'];
            }
        }

        // IMUNISASI per year (using pemeriksaan.id_jenis_imunisasi)
        $imunisasi = $this->db
            ->select('YEAR(tgl_timbang) as tahun,
                SUM(IF(p.id_jenis_imunisasi = 1,1,0)) as imunisasi1,
                SUM(IF(p.id_jenis_imunisasi = 3,1,0)) as imunisasi2,
                SUM(IF(p.id_jenis_imunisasi = 4,1,0)) as imunisasi3,
                SUM(IF(p.id_jenis_imunisasi = 5,1,0)) as imunisasi4', false)
            ->from('pemeriksaan p')
            ->group_by('YEAR(tgl_timbang)')
            ->get()
            ->result_array();

        $tahun_imunisasi = [];
        $im1 = [];
        $im2 = [];
        $im3 = [];
        $im4 = [];

        // build mapping for imunisasi by year
        $imun_map = [];
        foreach ($imunisasi as $r) {
            $y = (string)$r['tahun'];
            $imun_map[$y] = [
                (int)$r['imunisasi1'],
                (int)$r['imunisasi2'],
                (int)$r['imunisasi3'],
                (int)$r['imunisasi4']
            ];
        }

        // IBU HAMIL per year - gunakan data real berdasarkan tahun
        $bumil_per_year = $this->db
            ->select('YEAR(bumil_ttl) as tahun, COUNT(bumil_id) as jml', false)
            ->where('YEAR(bumil_ttl) >', 2019)
            ->where('YEAR(bumil_ttl) <=', $tahun_sekarang)
            ->group_by('YEAR(bumil_ttl)')
            ->get('bumil')
            ->result_array();

        foreach ($bumil_per_year as $r) {
            $y = (string)$r['tahun'];
            if (array_key_exists($y, $jml_bumil_series)) {
                $jml_bumil_series[$y] = (int)$r['jml'];
            }
        }

        // PEMERIKSAAN IBU HAMIL per year
        $pemeriksaan_bumil = $this->db
            ->select('YEAR(pemeriksaan_kehamilan_tgl) as tahun, COUNT(pemeriksaan_kehamilan_id) as jml', false)
            ->group_by('YEAR(pemeriksaan_kehamilan_tgl)')
            ->get('pemeriksaan_kehamilan')
            ->result_array();

        foreach ($pemeriksaan_bumil as $r) {
            $y = (string)$r['tahun'];
            if (array_key_exists($y, $jml_pemeriksaan_bumil_series)) {
                $jml_pemeriksaan_bumil_series[$y] = (int)$r['jml'];
            }
        }

        // prepare result arrays aligned to tahun_master order
        $kelahiran_series = [];
        $kematian_series = [];
        $series_normal = [];
        $series_warning = [];
        $series_stunting = [];
        $bumil_series = [];
        $pemeriksaan_bumil_series = [];

        foreach ($tahun_master as $y) {
            $kelahiran_series[] = (int)$jml_kelahiran_series[$y];
            $kematian_series[] = (int)$jml_kematian_series[$y];

            $series_normal[] = (int)$data_gizi_by_tahun[$y]['normal'];
            $series_warning[] = (int)$data_gizi_by_tahun[$y]['warning'];
            $series_stunting[] = (int)$data_gizi_by_tahun[$y]['stunting'];

            $bumil_series[] = (int)$jml_bumil_series[$y];
            $pemeriksaan_bumil_series[] = (int)$jml_pemeriksaan_bumil_series[$y];

            // imunisasi for this year (fallback to zeros)
            if (isset($imun_map[$y])) {
                $im = $imun_map[$y];
                $im1[] = $im[0];
                $im2[] = $im[1];
                $im3[] = $im[2];
                $im4[] = $im[3];
            } else {
                $im1[] = 0; $im2[] = 0; $im3[] = 0; $im4[] = 0;
            }
        }

        echo json_encode([
            'status' => true,
            'mode' => 'yearly',
            'labels' => $tahun_master,
            'kelahiran' => $kelahiran_series,
            'kematian' => $kematian_series,
            'status_gizi' => [
                'normal' => $series_normal,
                'warning' => $series_warning,
                'stunting' => $series_stunting
            ],
            'imunisasi' => [
                'tahun' => $tahun_master,
                'imunisasi1' => $im1,
                'imunisasi2' => $im2,
                'imunisasi3' => $im3,
                'imunisasi4' => $im4
            ],
            'ibu_hamil' => [
                'jumlah_bumil' => $bumil_series,
                'pemeriksaan_bumil' => $pemeriksaan_bumil_series
            ]
        ]);
    }
}