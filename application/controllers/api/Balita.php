<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Balita extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mbalita');
        $this->load->database();
        header('Content-Type: application/json');
    }

    // GET semua data
    public function index() {
        $rows = $this->Mbalita->getAll();
        $result = [];

        foreach ($rows as $row) {
            // Hitung umur
            $tgl_lahir = new DateTime($row->tgl_lahir);
            $today     = new DateTime();
            $diff      = $today->diff($tgl_lahir);
            $umur      = $diff->y." Tahun ".$diff->m." Bulan ".$diff->d." Hari";

            $result[] = [
                'nib'           => $row->nib,
                'nama_balita'   => $row->nama_balita,
                'tgl_lahir'     => date('d-m-Y', strtotime($row->tgl_lahir)),
                'umur'          => $umur,
                'jenis_kelamin' => ($row->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan'),
                'nama_ibu'      => $row->nama_ibu,
                'nama_ayah'     => $row->nama_ayah,
                'nama_orangtua' => $row->nama_orangtua,
                'username'      => $row->username,
                'status'        => ($row->is_meninggal == 1 ? 'Sudah Meninggal' : 'Hidup')
            ];
        }

        echo json_encode(['status' => true, 'data' => $result]);
    }

    // GET detail by NIB
    public function detail($nib) {
        $row = $this->Mbalita->getByNib($nib);

        if ($row) {
            $tgl_lahir = new DateTime($row->tgl_lahir);
            $today     = new DateTime();
            $diff      = $today->diff($tgl_lahir);
            $umur      = $diff->y." Tahun ".$diff->m." Bulan ".$diff->d." Hari";

            $data = [
                'nib'           => $row->nib,
                'nama_balita'   => $row->nama_balita,
                'tgl_lahir'     => date('d-m-Y', strtotime($row->tgl_lahir)),
                'umur'          => $umur,
                'jenis_kelamin' => ($row->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan'),
                'nama_ibu'      => $row->nama_ibu,
                'nama_ayah'     => $row->nama_ayah,
                'nama_orangtua' => $row->nama_orangtua,
                'username'      => $row->username,
                'status'        => ($row->is_meninggal == 1 ? 'Sudah Meninggal' : 'Hidup')
            ];

            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
    }

    // POST create
    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        $dataBalita = [
            'nib'           => $input['nib'],
            'nama_balita'   => $input['nama_balita'],
            'tgl_lahir'     => $input['tgl_lahir'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'nama_ibu'      => $input['nama_ibu'],
            'nama_ayah'     => $input['nama_ayah'],
            'is_meninggal'  => 0
        ];

        $dataOrtuBayi = [
            'id_orang_tua' => $input['id_orang_tua'],
            'nib'          => $input['nib']
        ];

        $success = $this->Mbalita->insert($dataBalita, $dataOrtuBayi);

        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data berhasil ditambahkan' : 'Gagal menambahkan data'
        ]);
    }

    // PUT update
    public function update($nib) {
        $input = json_decode(file_get_contents("php://input"), true);

        $dataBalita = [
            'nama_balita'   => $input['nama_balita'],
            'tgl_lahir'     => $input['tgl_lahir'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'nama_ibu'      => $input['nama_ibu'],
            'nama_ayah'     => $input['nama_ayah'],
            'is_meninggal'  => $input['is_meninggal']
        ];

        $success = $this->Mbalita->update($nib, $dataBalita);

        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data berhasil diupdate' : 'Gagal update data'
        ]);
    }

    // DELETE
    public function delete($nib) {
        $success = $this->Mbalita->delete($nib);

        echo json_encode([
            'status' => $success,
            'message' => $success ? 'Data berhasil dihapus' : 'Gagal hapus data'
        ]);
    }
}
