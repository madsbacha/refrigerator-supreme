<?php
namespace Api\Database;

class UserRepository extends Repository
{
    protected function getTable()
    {
        return 'users';
    }

    protected function getSelect()
    {
        return ['id', 'email'];
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function HasByEmail($email = null)
    {
        if (is_null($email)) {
            return false;
        }
        return $this->Has(compact('email'));
    }

    public function GetByEmailWithPassword($email)
    {
        $selectWithPassword = $this->select;
        array_push($selectWithPassword, 'password');
        return $this->db->get($this->table, $selectWithPassword, compact('email'));
    }

    public function UpdateById($id, $data)
    {
        return $this->Update($data, compact('id'));
    }
}
