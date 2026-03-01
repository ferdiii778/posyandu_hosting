<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mbalita extends CI_Model {

    private $table = 'balita';

	private function baseQueryBalita()
	{
	    $this->db->select('b.*, ot.nama as nama_orangtua, ot.username, rp.posyandu_nama');
	    $this->db->from('balita b');
	
	    // ✅ Ambil 1 orang tua per NIB (mengganti GROUP BY yg bikin error ONLY_FULL_GROUP_BY)
	    $this->db->join('(
	        SELECT nib, MIN(id_orang_tua) AS id_orang_tua
	        FROM ortu_bayi
	        GROUP BY nib
	    ) ob', 'b.nib = ob.nib', 'left', false);
	
	    $this->db->join('orang_tua ot', 'ob.id_orang_tua = ot.id_orang_tua', 'left');
	    $this->db->join('ref_posyandu rp', 'b.posyandu_id = rp.posyandu_id', 'left');
	}

    // =====================================
    // 👶 AMBIL SEMUA BALITA (ADMIN)
    // =====================================
    public function getAll() {
	    $this->baseQueryBalita();
	    return $this->db->get()->result_array();
	}


    // =============================================
    // 🔍 AMBIL BALITA BERDASARKAN POSYANDU (KADER)
    // =============================================
    public function getByPosyandu($posyandu_id)
	{
	    $this->baseQueryBalita();
	    $this->db->where('b.posyandu_id', $posyandu_id);
	    return $this->db->get()->result_array();
	}


    // ======================================
    // 📌 DETAIL BALITA BERDASARKAN NIB
    // ======================================
    public function getByNib($nib) {
        $this->db->select('
            b.*,
            rp.posyandu_nama
        ');
        $this->db->from('balita b');
        $this->db->join('ref_posyandu rp', 'b.posyandu_id = rp.posyandu_id', 'left');
        $this->db->where('b.nib', $nib);
        return $this->db->get()->row_array();
    }



    // ============================
    // INSERT BALITA BARU
    // ============================
    public function insert($dataBalita)
    {
        return $this->db->insert($this->table, $dataBalita);
    }

    // ============================
    // UPDATE BALITA
    // ============================
    public function update($nib, $dataBalita)
    {
        $this->db->where('nib', $nib);
        return $this->db->update($this->table, $dataBalita);
    }

    // ============================
    // DELETE BALITA + RELASI
    // ============================
    public function delete($nib)
    {
        $this->db->trans_start();
        $this->db->delete('ortu_bayi', ['nib' => $nib]);
        $this->db->delete('balita', ['nib' => $nib]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ============================
    // RELASI ORANG TUA ↔ BALITA
    // ============================

    public function assignToOrangtua($id_orang_tua, $nib)
    {
        return $this->db->insert('ortu_bayi', [
            'id_orang_tua' => $id_orang_tua,
            'nib'          => $nib
        ]);
    }

    public function updateRelasiOrtu($nib, $idOrangTuaBaru)
    {
        $this->db->where('nib', $nib);
        return $this->db->update('ortu_bayi', ['id_orang_tua' => $idOrangTuaBaru]);
    }

    public function removeFromOrangtua($id_orang_tua, $nib)
    {
        return $this->db->delete('ortu_bayi', [
            'id_orang_tua' => $id_orang_tua,
            'nib'          => $nib
        ]);
    }

    // ============================
    // BALITA YANG BELUM PUNYA ORTU
    // ============================
    public function getAvailable()
    {
        $this->db->select('b.nib, b.nama_balita, b.tgl_lahir');
        $this->db->from('balita b');
        $this->db->join('ortu_bayi ob', 'b.nib = ob.nib', 'left');
        $this->db->where('ob.id_orang_tua IS NULL', null, false);
        return $this->db->get()->result_array();
    }

    // ============================
    // GENERATE KODE NIB
    // ============================
    public function nib()
    {
        $this->db->select('RIGHT(balita.nib,3) as kode', FALSE);
        $this->db->order_by('nib', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('balita');

        if ($query->num_rows() <> 0) {
            $data = $query->row();
            $kode = intval($data->kode) + 1;
        } else {
            $kode = 1;
        }

        $kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT);
        return "B" . $kodemax;
    }


    // =========================================
    // 🩺 PEMERIKSAAN BALITA
    // =========================================

    public function getPemeriksaanAll($nib)
    {
        $this->db->select('p.*, v.nama_vitamin, i.nama_imunisasi');
        $this->db->from('pemeriksaan p');
        $this->db->join('jenis_vitamin v', 'v.id_jenis_vitamin = p.id_jenis_vitamin', 'left');
        $this->db->join('jenis_imunisasi i', 'i.id_jenis_imunisasi = p.id_jenis_imunisasi', 'left');
        $this->db->where('p.nib', $nib);
        $this->db->order_by('p.tgl_timbang', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getByIdPemeriksaan($kode_pemeriksaan)
    {
        $this->db->select('p.*, v.nama_vitamin, i.nama_imunisasi');
        $this->db->from('pemeriksaan p');
        $this->db->join('jenis_vitamin v', 'v.id_jenis_vitamin = p.id_jenis_vitamin', 'left');
        $this->db->join('jenis_imunisasi i', 'i.id_jenis_imunisasi = p.id_jenis_imunisasi', 'left');
        $this->db->where('p.kode_pemeriksaan', $kode_pemeriksaan);
        return $this->db->get()->row_array();
    }

    public function insertPemeriksaan($data)
    {
        return $this->db->insert('pemeriksaan', $data);
    }

    public function updatePemeriksaan($kode, $data)
    {
        $this->db->where('kode_pemeriksaan', $kode);
        return $this->db->update('pemeriksaan', $data);
    }

    public function deletePemeriksaan($kode)
    {
        $this->db->where('kode_pemeriksaan', $kode);
        return $this->db->delete('pemeriksaan');
    }

    public function kodePemeriksaan()
    {
        $this->db->select('RIGHT(pemeriksaan.kode_pemeriksaan,3) as kode', FALSE);
        $this->db->order_by('kode_pemeriksaan', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('pemeriksaan');

        if ($query->num_rows() <> 0) {
            $data = $query->row();
            $kode = intval($data->kode) + 1;
        } else {
            $kode = 1;
        }

        $kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT);
        return "P" . $kodemax;
    }
}
