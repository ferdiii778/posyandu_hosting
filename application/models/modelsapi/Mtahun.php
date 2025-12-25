<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mtahun extends CI_Model
{
    private $table = 'ref_tahun';

    public function get_all()
    {
        return $this->db
            ->order_by('tahun_nama', 'ASC')
            ->get($this->table)
            ->result(); // OBJECT
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where('tahun_id', $id)
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('tahun_id', $id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where('tahun_id', $id)
            ->delete($this->table);
    }
}
