<?php


/* the get token class is just a testing code in which the developer can access tokens for testing */

use Database\DB;
use Firebase\JWT\JWT;

class TokenController{

    private $email;
    private $password;
    public $token;


    public function getToken(){

        $data = json_decode(file_get_contents("php://input"), true);

        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;


        $DB = new DB();
        $DB->conn();

        $result = $DB->query("SELECT user_id, email, password FROM users WHERE email='$this->email'");

        if($result){
            if($result[0]['password'] == $this->password ){

                $payload = array(
                    "user_id" => $result[0]['user_id'], 
                    "email" => $this->email
                );

                $jwt = JWT::encode($payload, $_ENV['SESSION_KEY'], 'HS256');

                echo  json_encode(array("token" => $jwt), JSON_PRETTY_PRINT);
                
            }
        }else{
            //http_response_code(401);
            $message = [
                "message" => "No Email found"
            ];
            echo json_encode($message, JSON_PRETTY_PRINT);
        }

    }
}