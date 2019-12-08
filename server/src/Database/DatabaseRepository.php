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
        $this->Items = new ItemRepository($this->db);
        $this->Categories = new CategoryRepository($this->db);
        $this->Ratings = new RatingRepository($this->db);
    }
}
