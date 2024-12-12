<?php

namespace Database;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class DB
{
    private $hostname;
    private $username;
    private $password;
    private $dbname;
    private $port;
    public $conn;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Access the Database Credintials to the ENV File
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        $dbPort = $_ENV['DB_PORT'];

        // Set Values on Class
        $this->hostname = $dbHost;
        $this->username = $dbUser;
        $this->password = $dbPass;
        $this->dbname = $dbName;
        $this->port = $dbPort;
    }

    // Function to connect from the database
    public function conn()
    {
        try {
            // Create a new PDO instance and include dbname in the DSN
            $this->conn = new PDO("mysql:host=$this->hostname;port=$this->port;dbname=$this->dbname", $this->username, $this->password);

            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "unable to connect to the database please try again later.", "reason" => "Connection failed: " . $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    // Function to create a query statement
    public function query($query)
    {

        try {
            $statement = $this->conn->query($query);

            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            echo "\nQuery failed: " . $e->getMessage();
        }
    }

    // Function to close the connection
    public function close()
    {
        echo "Closed successfully";
        return $this->conn = null;
    }
}
