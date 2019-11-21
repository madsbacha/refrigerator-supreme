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
        return ['id', 'item_id', 'user_id', 'text', 'parent_id'];
    }

    public function ByUserId($id)
    {
        return $this->Select(['user_id'=>$id]);
    }

    public function FindById($id)
    {
        return $this->Get(compact('id'));
    }

    public function RepliesTo($parent_id)
    {
        return $this->Select(compact('parent_id'));
    }

    public function CountOnItem($item_id)
    {
        return $this->Count(compact('item_id'));
    }
}