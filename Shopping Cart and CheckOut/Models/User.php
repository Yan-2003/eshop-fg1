<?php
namespace Models;
use Database\DB;
use PDO;
class User
{
    private $db;

    public function __construct()
    {
        $this->db = (new DB())->conn();
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
