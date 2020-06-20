<?php
    require("./class.php");
    $service=new func("services", "milogy");
    $service->setFields(["title", "description", "orderUser", "price"]);
        ////* Create
    if ($_SERVER["REQUEST_METHOD"]=="POST") {
        var_dump($_POST);
        if(!isset($_POST["title"]) || !isset($_POST["description"]) || !isset($_POST["orderUser"]) || !isset($_POST["price"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $title = $_POST["title"];
        $description = $_POST["description"];
        $orderUser = $_POST["orderUser"];
        $price = $_POST["price"];
        $service->create(["title"=>$title, "description"=>$description, "orderUser"=>$orderUser, "price"=>$price]);
    }
     ////* Read
    elseif($_SERVER["REQUEST_METHOD"]=="GET")
    {
         
        $res=$service->readRow();
        if($res)
        {
            $json_res=json_encode($res);
            echo $json_res;
        }
        else
            echo "Not Found";
    }
    ////* Update
    elseif ($_SERVER["REQUEST_METHOD"]=="PUT") 
    {
        parse_str(file_get_contents('php://input'), $data);
        // $data=json_decode($data_json, true);
        if(!isset($data["serviceid"], $data["title"]) || !isset($data["description"]) || !isset($data["orderUser"]) || !isset($data["orderUser"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $serviceid = $data["serviceid"];
        $title = $data["title"];
        $description = $data["description"];
        $orderUser = $data["orderUser"];
        $lastname = $data["price"];
        $service->update(["title"=>$this_title],["title"=>$title, "description"=>$description, 
            "orderUser"=>$orderUser, "lastname"=>$lastname]);
    }
    ////* DELETE
    elseif ($_SERVER["REQUEST_METHOD"]=='DELETE')
     {
        parse_str(file_get_contents('php://input'), $data);
        if(!isset($data["serviceid"]))
        {
            die("Some fields are not entered");
            exit;
        }
        $serviceid = $data["serviceid"];
        $service->delete(["serviceid"=>$serviceid]);
    }
   
?>