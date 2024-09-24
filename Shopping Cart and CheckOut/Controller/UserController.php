<?php

require_once 'Models/User.php';

class UserController
{
    public function showUser()
    {

        //Instantiate the object User
        $allUsers = new User();

        // Fetch all users
        $users = $allUsers->getAllUsers();

        echo json_encode($users, JSON_PRETTY_PRINT);
    }
}
