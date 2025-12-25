<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orangtua extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Morangtua');
        $this->load->model('modelsapi/Mbalita');
        $this->load->database();
        header('Content-Type: application/json');
    }

    // ======================
    // AUTH (SAMA DENGAN BALITA)
    // ======================
    private function auth() {
        $token   = $this->input->get_request_header('X-Token');
        $expired = $this->input->get_request_header('X-Expired');

        if (!$token || !$expired) {
            return ['status'=>false,'message'=>'Token diperlukan'];
        }

        if ($expired < time()) {
            return ['status'=>false,'message'=>'Token expired'];
        }

        return ['status'=>true];
    }

    private function getUserFromToken() {
        $user_id = $this->input->get_request_header('X-User-Id');
        if (!$user_id) return null;

        return $this->db
            ->get_where('user', ['id_user' => $user_id])
            ->row_array();
    }

    // ======================
    // GET LIST ORANG TUA
    // ======================
    public function index() {

        // 🔐 Auth
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        // 👤 User
        $user = $this->getUserFromToken();
        if (!$user) {
            echo json_encode(['status'=>false,'message'=>'User tidak ditemukan']);
            return;
        }

        // 🧠 Role-based
        if ($user['role'] === 'admin') {
            $data = $this->Morangtua->getAll();
        }
        elseif ($user['role'] === 'kader') {
            if (!$user['posyandu_id']) {
                echo json_encode(['status'=>false,'message'=>'Posyandu ID tidak ditemukan']);
                return;
            }
            $data = $this->Morangtua->getByPosyandu($user['posyandu_id']);
        }
        else {
            $data = [];
        }

        echo json_encode([
            'status' => true,
            'role'   => $user['role'],
            'data'   => $data
        ]);
    }

    // ======================
    // DETAIL ORANG TUA
    // ======================
    public function detail($id) {
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $data = $this->Morangtua->getById($id);
        echo json_encode([
            'status' => (bool)$data,
            'data'   => $data
        ]);
    }

    // ======================
    // CREATE ORANG TUA (ADMIN/KADER)
    // ======================
    public function create() {

        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $user = $this->getUserFromToken();
        if (!$user || !in_array($user['role'], ['admin','kader'])) {
            echo json_encode(['status'=>false,'message'=>'Tidak memiliki izin']);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true);

        $this->db->insert('orang_tua', [
            'nama'        => $input['nama'],
            'username'    => $input['username'],
            'posyandu_id' => (int)$input['posyandu_id']
        ]);

        $this->db->insert('user', [
            'nama'        => $input['nama'],
            'username'    => $input['username'],
            'password'    => md5($input['password']),
            'role'        => 'member',
            'posyandu_id' => (int)$input['posyandu_id'],
            'aktif'       => 1
        ]);

        echo json_encode([
            'status'=>true,
            'message'=>'Data orang tua berhasil ditambahkan'
        ]);
    }

    // ======================
    // UPDATE
    // ======================
    public function update($id) {

        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $input = json_decode($this->input->raw_input_stream, true);

        $this->db
            ->where('id_orang_tua',$id)
            ->update('orang_tua',[
                'nama'=>$input['nama'],
                'username'=>$input['username'],
                'posyandu_id'=>(int)$input['posyandu_id']
            ]);

        $this->db
            ->where('username',$input['username'])
            ->update('user',[
                'nama'=>$input['nama'],
                'posyandu_id'=>(int)$input['posyandu_id']
            ]);

        echo json_encode(['status'=>true,'message'=>'Data berhasil diperbarui']);
    }

    // ======================
    // BALITA RELATION
    // ======================
    public function add_balita($id_orang_tua) {
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $data = json_decode($this->input->raw_input_stream,true);
        $success = $this->Mbalita->assignToOrangtua($id_orang_tua,$data['nib']);

        echo json_encode([
            'status'=>$success,
            'message'=>$success?'Balita ditambahkan':'Gagal menambahkan'
        ]);
    }

    public function delete_balita($id_orang_tua,$nib) {
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $success = $this->Mbalita->removeFromOrangtua($id_orang_tua,$nib);

        echo json_encode([
            'status'=>$success,
            'message'=>$success?'Balita dilepas':'Gagal melepas'
        ]);
    }

    // ======================
    // RESET PASSWORD
    // ======================
    public function reset_password($id_user) {
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $this->db->where('id_user',$id_user)
            ->update('user',['password'=>md5('123456')]);

        echo json_encode(['status'=>true,'message'=>'Password direset']);
    }

    // ======================
    // DELETE ORANG TUA
    // ======================
    public function delete($id) {
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }

        $this->db->where('id_orang_tua',$id)->delete('ortu_bayi');
        $this->db->where('id_orang_tua',$id)->delete('orang_tua');

        echo json_encode(['status'=>true,'message'=>'Data dihapus']);
    }
}
