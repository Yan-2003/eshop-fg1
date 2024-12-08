<?php

namespace Middleware;

use Database\DB;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class auth{
    private $token;
    private $user_id;

    public function check(){
        $DB = new DB();
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])){
            $authHeader = $headers['Authorization'];
            
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)){
                $bearerToken = $matches[1];
                $this->token = $bearerToken;
                $key = $_ENV['SESSION_KEY'];
                try {
       
                    $decoded = JWT::decode($this->token, new Key($key, 'HS256'));

                    if(!$decoded){
                        http_response_code(403);
                        echo json_encode(["message" => "Token expired."], JSON_PRETTY_PRINT);
                        return;
                    }

                    $this->user_id = $decoded->user_id;
                    $DB->conn();

                    $resutl = $DB->query("SELECT user_id FROM users WHERE user_id='$this->user_id'");

                    if($resutl){
                        $_SESSION['user_id'] = $this->user_id;
                        return true;
    
                    }else{

                        http_response_code(401);
                        echo json_encode(array("message"=>"Unauthorized user."), JSON_PRETTY_PRINT);
                        return false;
                    }
                } catch (SignatureInvalidException $e) {
                    http_response_code(401);
                    echo json_encode(["message" => "Token Expired: " . $e->getMessage()], JSON_PRETTY_PRINT);
                    return false;
                }

            } else {
                http_response_code(401);
                echo json_encode(array("message"=>"Unauthorized user."), JSON_PRETTY_PRINT);
                return false;
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message"=>"Unauthorized user."), JSON_PRETTY_PRINT);
            return false;
        }
    }


}





