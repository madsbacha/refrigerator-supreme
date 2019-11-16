<?php
namespace Api\Database;

use Medoo\Medoo;

class DatabaseRepository {
    private $db;
    public $Users;
    public $Items;
    public $Comments;
    public $Categories;
    public $Ratings;

    public function __construct(Medoo $db = null)
    {
        if (is_null($db)) {
            $config = require __DIR__ . '/../../config.php';
            $this->db = new Medoo($config['database']);
        } else {
            $this->db = $db;
        }
        $this->Users = new UserRepository($this->db);
        $this->Items = new \Api\Database\ItemRepository($this->db);
        $this->Comments = new \Api\Database\CommentRepository($this->db);
        $this->Categories = new \Api\Database\CategoryRepository($this->db);
        $this->Ratings = new \Api\Database\RatingRepository($this->db);
    }
}
