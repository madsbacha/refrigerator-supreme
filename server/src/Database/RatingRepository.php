<?php
namespace Api\Database;

class RatingRepository extends Repository
{
    protected function getTable()
    {
        return 'ratings';
    }

    protected function getSelect()
    {
        return ['id', 'item_id', 'user_id', 'rating'];
    }

    /**
     * @param $id Int The id of an Item
     * @return array|bool
     */
    public function On($id) {
        return $this->Select(['item_id'=>$id]);
    }

    public function ByUserId($id) {
        return $this->Select(['user_id'=>$id]);
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }
}