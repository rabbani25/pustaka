<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class ModelBooking extends CI_Model
{
    public function getData($table)
    {
        return $this->db->get($table)->row();
    }

    public function getDataWhere($table, $where)
    {
        $this->db->where($where);
        return $this->db->get($table);
    }

    public function getOrderByLimit($table, $order, $limit)
    {
        $this->db->order_by($order, 'desc');
        $this->db->limit($limit);
        return $this->db->get($table);
    }

    public function joinOrder($where)
    {
        $this->db->select('*');
        $this->db->from('booking bo');
        $this->db->join('booking_detail d', 'd.id_booking=bo.id_booking');
        $this->db->join('buku bu ', 'bu.id=d.id_buku');
        $this->db->where($where);
        return $this->db->get();
    }

    public function simpanDetail($where = null)
    {
        $sql = "INSERT INTO booking_detail (id_booking,id_buku) SELECT booking.id
   _booking,temp.id_buku FROM booking, temp WHERE temp.id_user=booking.id_user AND b
   ooking.id_user='$where'";
        $this->db->query($sql);
    }
    public function insertData($table, $data)
    {
        $this->db->insert($table, $data);
    }
    public function updateData($table, $data, $where)
    {
        $this->db->update($table, $data, $where);
    }
    public function deleteData($where, $table)
    {
        $this->db->where($where);
        $this->db->delete($table);
    }
    public function find($where)
    {
        //Query mencari record berdasarkan ID-nya
        $this->db->limit(1);
        return $this->db->get('buku', $where);
    }
    public function kosongkanData($table)
    {
        return $this->db->truncate($table);
    }
}
