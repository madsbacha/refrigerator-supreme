<?php

namespace Api\Database\Repository;

use Medoo\Medoo;

class RatingRepository
{
    private $db;
    private $table;
    private $select;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = 'ratings';
        $this->select = ['id', 'item_id', 'user_id', 'rating'];
    }

    /**
     * @param $id Int The id of an Item
     * @return array|bool
     */
    public function On($id) {
        return $this->Where(['item_id'=>$id]);
    }

    public function ByUserId($id) {
        return $this->Where(['user_id'=>$id]);
    }

    public function FindById($id)
    {
        return $this->Where(compact('id'));
    }

    public function Where($where)
    {
        return $this->db->select($this->table, $this->select, $where);
    }

    public function Get($where)
    {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function Has($where)
    {
        return $this->db->has($this->table, $where);
    }

    public function Update($data, $where)
    {
        $data = $this->db->update($this->table, $data, $where);
        return $data->rowCount() > 0;
    }

    public function Create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }
}