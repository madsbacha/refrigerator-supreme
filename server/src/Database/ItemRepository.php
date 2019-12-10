<?php
namespace Api\Database;

class ItemRepository extends Repository
{
    protected function getTable()
    {
        return 'items';
    }

    protected function getSelect()
    {
        return ['id', 'name', 'image', 'category_id', 'price', 'energy', 'size', 'slug'];
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function DeleteById($id)
    {
        return $this->Delete(compact('id'));
    }

    public function UpdateById($id, $data)
    {
        return $this->Update($data, compact('id'));
    }

    public function HasById($id)
    {
        return $this->Has(compact('id'));
    }
}