<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mmaster extends CI_Model
{
    public function get($table, $params = [])
    {
        $this->db->reset_query();
        $this->db->from($table);

        // JOIN
        if (!empty($params['join']) && is_array($params['join'])) {
            foreach ($params['join'] as $j) {
                // format: [table, condition, type]
                $t = $j[0] ?? null;
                $c = $j[1] ?? null;
                $ty = $j[2] ?? 'left';
                if ($t && $c) $this->db->join($t, $c, $ty);
            }
        }

        // WHERE
        if (!empty($params['where']) && is_array($params['where'])) {
            foreach ($params['where'] as $k => $v) {
                // dukung where raw seperti "YEAR(tgl_kematian)" => 2025
                if (strpos($k, '(') !== false || strpos($k, ' ') !== false) {
                    $this->db->where("$k", $v, false);
                } else {
                    $this->db->where($k, $v);
                }
            }
        }

        // ORDER BY
        if (!empty($params['order_by']) && is_array($params['order_by'])) {
            foreach ($params['order_by'] as $k => $v) {
                $this->db->order_by($k, $v);
            }
        }

        // LIMIT
        if (!empty($params['limit'])) {
            $limit = (int)$params['limit'];
            $offset = !empty($params['offset']) ? (int)$params['offset'] : 0;
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function get_row($table, $params = [])
    {
        $res = $this->get($table, $params);
        return !empty($res) ? $res[0] : null;
    }
}