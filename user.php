<?php
    require("./class.php");
    $user=new func("users", "milogy");
    $user->setFields(["username", "password", "firstname", "lastname"]);
    $request = strtolower((string)$_REQUEST['REQUEST_METHOD']);

    ////? Create
    if ($request=="post") {
        if(!isset($_POST["username"]) || !isset($_POST["password"]) || !isset($_POST["firstname"]) || !isset($_POST["firstname"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $username = $_POST["username"];
        $password = $_POST["password"];
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $user->create(["username"=>$username, "password"=>$password, "firstname"=>$firstname, "lastname"=>$lastname]);
    }
     ////? Read
    elseif($request=="get")
    {
        $res=$user->readRow();
        if($res)
        {
            $json_res=json_encode($res);
            echo $json_res;
            // var_dump($res);
        }
        else
            echo "Not Found";
    }
    ////? Update
    elseif ($request=="put") 
    {
        parse_str(file_get_contents('php://input'), $data);
        // $data=json_decode($data_json, true);
        if(!isset($data["userid"], $data["username"]) || !isset($data["password"])
             || !isset($data["firstname"]) || !isset($data["lastname"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $userid = $data["userid"];
        $username = $data["username"];
        $password = $data["password"];
        $firstname = $data["firstname"];
        $lastname = $data["lasttname"];
        $user->update(["userid"=>$userid],["username"=>$username, "password"=>$password, 
            "firstname"=>$firstname, "lastname"=>$lastname]);
    }
    ////? Delete
    elseif ($request=='delete')
     {
        parse_str(file_get_contents('php://input'), $data);
        if(!isset($data["userid"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $userid = $data["userid"];

        $user->delete(["userid"=>$userid]);
    }
?>