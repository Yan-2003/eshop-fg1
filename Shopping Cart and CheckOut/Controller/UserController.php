<?php

use Middleware\auth;
use Models\User;

class UserController
{
    public function showUser()
    {
        $auth = new auth();
        
        if($auth->check()){
            $allUsers = new User();
    
            // Fetch all users
            $users = $allUsers->getAllUsers();
    
            echo json_encode($users, JSON_PRETTY_PRINT);
        }else{
            http_response_code(401);
        }

        //Instantiate the object User
    }
}
