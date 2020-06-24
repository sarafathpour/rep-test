<?php
require("./class.php");
header('Content-Type: application/json');
$service = new MyApi("services", "milogy");
$service->setFields(["serviceid", "title", "description", "orderUser", "price"]);
$request = $_SERVER['REQUEST_METHOD'];
$request = strtolower($_SERVER['REQUEST_METHOD']);

////* Create
if ($request == "post") {
    
    $data_json = file_get_contents('php://input');
    $data = json_decode($data_json, true);
    if (!isset($data["title"]) || !isset($data["description"]) || !isset($data["orderUser"]) || !isset($data["price"])) {
        die("Some fields are not entered");
        exit;
    }
    $title = $data["title"];
    $description = $data["description"];
    $orderUser = $data["orderUser"];
    $price = $data["price"];
    $res = $service->create(["title" => $title, "description" => $description, "orderUser" => $orderUser, "price" => $price]);
    if ($res) {

        header("HTTP/1.0 201 Creted");
        echo json_encode($res);
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo json_encode($state);
        die();
    }
}
////* Read
elseif ($request == "get") {

    $res = $service->readRow();
    if (sizeof($res) >= 1)
        echo json_encode($res, true);
    else {
        $err = $user->getErrorConnection();
        header("HTTP/1.0 404 Not Found");
        echo json_encode($err);
        die();
    }
}
////* Update
elseif ($request == "put") {
    $data_json = file_get_contents('php://input');
    $data = json_decode($data_json, true);
    if (!isset($data["serviceid"], $data["title"]) || !isset($data["description"]) || !isset($data["orderUser"]) || !isset($data["orderUser"])) {
        die("Some fields are not entered");
        exit;
    }
    $serviceid = (int) $_GET["serviceid"];
    $title = $data["title"];
    $description = $data["description"];
    $orderUser = $data["orderUser"];
    $lastname = $data["price"];
    $res = $service->update(["serviceid" => $serviceid], [
        "title" => $title, "description" => $description,
        "orderUser" => $orderUser, "price" => $price
    ]);
    if ($res)
        echo json_encode($res);
    else {
        $err = $user->getErrorConnection();
        if ($err["error-code"] == 404)
            header("HTTP/1.0 404 Not Found");
        else
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo json_encode($err);
        die();
    }
}
////* DELETE
elseif ($request == 'delete') {
    if (!isset($_GET["serciceid"])) {
        die("Some fields are not entered");
        exit;
    }
    $serviceid = (int) $_GET["serciceid"];
    $res = $service->delete(["serciceid" => $serviceid]);
    if ($res) {
        echo json_encode($res);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["Status Code" => 404, "Status Message" => "Not Found"], true);
        die;
    }
}
