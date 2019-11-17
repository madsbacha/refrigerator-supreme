<?php
namespace Api\Database;

class CommentRepository extends Repository
{
    protected function getTable()
    {
        return 'comments';
    }

    protected function getSelect()
    {
        return ['id', 'item_id', 'user_id', 'text'];
    }

    public function ByUserId($id)
    {
        return $this->Select(['user_id'=>$id]);
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }
}