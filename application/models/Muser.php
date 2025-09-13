<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Muser extends CI_Model {

    public function getAll() {
        return $this->db->get('user')->result();
    }

    public function getById($id) {
        return $this->db->get_where('user', ['id_user' => $id])->row();
    }

    public function insert($data) {
        return $this->db->insert('user', $data);
    }

    public function updateData($id, $data) {
        $this->db->where('id_user', $id);
        return $this->db->update('user', $data);
    }

    public function deleteData($id) {
        $this->db->where('id_user', $id);
        return $this->db->delete('user');
    }
}
