<?php
require '../vendor/autoload.php';


class DB
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $port;
    public $conn;


    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Access the Database Credintials to the ENV File
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        $dbPort = $_ENV['DB_PORT'];

        // Set Values on Class
        $this->username = $dbHost;
        $this->username = $dbUser;
        $this->password = $dbPass;
        $this->dbname = $dbName;
        $this->port = $dbPort;
    }

    // Function to connect from the database
    public function conn()
    {
        try {
            // Create a new PDO instance
            $this->conn = new PDO("mysql:host=$this->servername;port=$this->port;dbname=$this->dbname", $this->username, $this->password);

            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    // Function to create a query statement
    public function query($query){

        try {
            $statement = $this->conn->query($query);
    
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            echo "\nQuery failed: " . $e->getMessage();
        }
    }

    // Function to close the connection
    public function close(){
        echo "Closed successfully";
        return $this->conn = null;
    }
}

/* $DB = new DB();
$DB->conn(); */
/*
// testing code 



echo json_encode($DB->query("SELECT * FROM users"), JSON_PRETTY_PRINT); */
