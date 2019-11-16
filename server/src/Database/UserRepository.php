<?php

namespace Api\Database\Repository;

use Medoo\Medoo;
use function Siler\Swoole\push;

class UserRepository
{
    private $db;
    private $table;
    private $select;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = 'users';
        $this->select = ['id', 'email'];
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function Get($where = [])
    {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function HasByEmail($email = null)
    {
        if (is_null($email)) {
            return false;
        }
        return $this->db->has($this->table, compact('email'));
    }

    public function Create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }

    public function GetByEmailWithPassword($email)
    {
        $selectWithPassword = $this->select;
        array_push($selectWithPassword, 'password');
        return $this->db->get($this->table, $selectWithPassword, compact('email'));
    }

    public function Update($id, $data)
    {
        $data = $this->db->update($this->table, $data, compact('id'));
        return $data->rowCount() > 0;
    }
}
