<?php
namespace Api\Database;

use Medoo\Medoo;

abstract class Repository {
    protected $db;
    protected $table;
    protected $select;
    abstract protected function getTable();
    abstract protected function getSelect();

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = $this->getTable();
        $this->select = $this->getSelect();
    }

    public function Get($where = [])
    {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function Select($where = [])
    {
        return $this->db->select($this->table, $this->select, $where);
    }

    public function Has($where = [])
    {
        return $this->db->Has($this->table, $where);
    }

    public function Create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }

    public function Update($data, $where = [])
    {
        $data = $this->db->update($this->table, $data, $where);
        return $data->rowCount() > 0;
    }

    public function Delete($where)
    {
        $data = $this->db->delete($this->table, $where);
        return $data->rowCount() > 0;
    }
}