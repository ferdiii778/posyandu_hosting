<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class peta extends CI_Controller {
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        header('Content-Type: application/json');

        $tahun_gizi = $this->input->get('tahun_gizi');
        $status_gizi = $this->input->get('status_gizi');

        $getbalita = $this->db->get('balita')->result_array();
        $hasil = array();

        foreach ($getbalita as $r) {
            if ($r['latitude'] != 0 && $r['longitude'] != 0) {
                $where = array();

                if ($tahun_gizi && !$status_gizi) {
                    $where = array('YEAR(tgl_timbang)' => $tahun_gizi, 'pemeriksaan.nib' => $r['nib']);
                } elseif ($status_gizi && !$tahun_gizi) {
                    $where = array('status_pemeriksaan' => $status_gizi, 'pemeriksaan.nib' => $r['nib']);
                } elseif ($tahun_gizi && $status_gizi) {
                    $where = array(
                        'YEAR(tgl_timbang)' => $tahun_gizi,
                        'status_pemeriksaan' => $status_gizi,
                        'pemeriksaan.nib' => $r['nib']
                    );
                } else {
                    $where = array('pemeriksaan.nib' => $r['nib']);
                }

                $this->db->order_by('tgl_timbang', 'desc');
                $this->db->limit(1);
                $pemeriksaan = $this->db->get_where('pemeriksaan', $where)->row_array();

                if ($pemeriksaan) {
                    $hasil[] = array(
                        'nib' => $r['nib'],
                        'nama_balita' => $r['nama_balita'],
                        'tgl_lahir' => $r['tgl_lahir'],
                        'jenis_kelamin' => $r['jenis_kelamin'],
                        'nama_ibu' => $r['nama_ibu'],
                        'nama_ayah' => $r['nama_ayah'],
                        'alamat' => $r['alamat'],
                        'status_pemeriksaan' => $pemeriksaan['status_pemeriksaan'],
                        'latitude' => $r['latitude'],
                        'longitude' => $r['longitude'],
                        'umur_bulan' => $pemeriksaan['umur_bulan'],
                        'berat_badan' => $pemeriksaan['berat_badan'],
                        'panjang_badan' => $pemeriksaan['panjang_badan'],
                        'status_warna' => $pemeriksaan['status_warna'],
                        'icon_marker' => (
                            $pemeriksaan['icon_marker'] == '' ||
                            $pemeriksaan['icon_marker'] == 'assets/icons/'
                        ) ? 'assets/icons/marker-blue.png' : $pemeriksaan['icon_marker']
                    );
                }
            }
        }

        echo json_encode([
            'status' => true,
            'message' => 'Data peta berhasil dimuat',
            'data' => $hasil
        ]);
    }
}
