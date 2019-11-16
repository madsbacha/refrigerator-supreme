<?php

namespace Api\Database;

use Medoo\Medoo;

class ItemRepository
{
    private $db;
    private $table;
    private $select;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = 'items';
        $this->select = ['id', 'name', 'image', 'category_id'];
    }

    public function Where($where = [])
    {
        return $this->db->select($this->table, $this->select, $where);
    }

    public function RatingOf($id)
    {
        return $this->db->avg($this->table, 'rating', compact('id'));
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function Get($where) {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function Create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }

    public function Delete($id)
    {
        $data = $this->db->delete($this->table, compact('id'));
        return $data->rowCount() > 0;
    }

    public function Update($id, $data)
    {
        $data = $this->db->update($this->table, $data, compact('id'));
        return $data->rowCount() > 0;
    }

    public function Has($where)
    {
        return $this->db->has($this->table, $where);
    }

    public function HasById($id)
    {
        return $this->Has(compact('id'));
    }
}