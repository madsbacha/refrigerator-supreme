<?php

namespace Api\Database;

use Medoo\Medoo;

class CommentRepository
{
    private $db;
    private $table;
    private $select;

    public function __construct(Medoo $db)
    {
        $this->db = $db;
        $this->table = 'comments';
        $this->select = ['id', 'item_id', 'user_id', 'text'];
    }

    public function Where($where)
    {
        return $this->db->select($this->table, $this->select, $where);
    }

    public function ByUserId($id)
    {
        return $this->Where(['user_id'=>$id]);
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function Get($where)
    {
        return $this->db->get($this->table, $this->select, $where);
    }

    public function Delete($where)
    {
        return $this->db->delete($this->table, $where);
    }
}