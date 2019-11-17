<?php
namespace Api\Database;

class CategoryRepository extends Repository
{
    protected function getTable()
    {
        return 'categories';
    }

    protected function getSelect()
    {
        return ['id', 'name'];
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function DeleteById($id)
    {
        return $this->Delete(compact('id'));
    }

    public function UpdateById($data, $id)
    {
        return $this->Update($data, compact('id'));
    }
}