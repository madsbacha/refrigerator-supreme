<?php

namespace Api\Database;

use Medoo\Medoo;

class CategoryRepository
{
    private $db;
    private $table;
    private $select;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = 'categories';
        $this->select = ['id', 'name'];
    }

    public function Get($where)
    {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function Create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->id();
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function Delete($where)
    {
        $data = $this->db->delete($this->table, $where);
        return $data->rowCount() > 0;
    }

    public function DeleteById($id)
    {
        return $this->Delete(compact('id'));
    }

    public function Update($data, $where)
    {
        $data = $this->db->update($this->table, $data, $where);
        return $data->rowCount() > 0;
    }

    public function UpdateById($data, $id)
    {
        return $this->Update($data, compact('id'));
    }
}