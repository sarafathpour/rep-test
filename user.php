<?php
require("./class.php");
header('Content-Type: application/json');
$user = new MyApi("users", "milogy", ["username"]);
$state = $user->getErrorConnection();
if (isset($state)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo json_encode($state);
}
$user->setFields(["userid", "username", "firstname", "lastname"]);
$request = $_SERVER['REQUEST_METHOD'];
$request = strtolower($_SERVER['REQUEST_METHOD']);
////? Create
if ($request == "post") {
    $data_json = file_get_contents('php://input');
    $data = json_decode($data_json, true);

    if (!isset($data["username"]) || !isset($data["password"]) || !isset($data["firstname"]) || !isset($data["firstname"])) {
        die("Some fields are not entered");
    }
    $username = $data["username"];
    $password = password_hash($data["password"],PASSWORD_DEFAULT);
    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $res = $user->create(["username" => $username, "password" => $password, "firstname" => $firstname, "lastname" => $lastname]);
    if ($res) {
        header("HTTP/1.0 201 Creted");
        echo json_encode($user->readRow(["username" => $username]));
    } else {
        $state = $user->getErrorConnection();
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo json_encode($state);
    }
}
////? Read
elseif ($request == "get") {
    $res = $user->readRow();
    if (sizeof($res) >= 1)
        echo json_encode($res, true);
    else {
        $err = $user->getErrorConnection();
        header("HTTP/1.0 404 Not Found");
        echo json_encode($err);
        die();
    }
}
////? Update
elseif ($request == "put") {
    $data_json = file_get_contents('php://input');
    $data = json_decode($data_json, true);
    if (
        !isset($_GET["userid"], $data["username"]) || !isset($data["password"])
        || !isset($data["firstname"]) || !isset($data["lastname"])
    ) {
        die("Some fields are not entered");
        exit;
    }
    $userid = (int) $_GET["userid"];
    $username = $data["username"];
    $password =  password_hash($data["password"],PASSWORD_DEFAULT);
    $firstname = $data["firstname"];
    $lastname = $data["lastname"];

    $res = $user->update(["userid" => $userid], [
        "username" => $username, "password" => $password,
        "firstname" => $firstname, "lastname" => $lastname
    ]);
    if ($res)
        echo json_encode($user->readRow(["username" => $username]));
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
////? Delete
elseif ($request == 'delete') {
    if (!isset($_GET["userid"])) {
        die("Some fields are not entered");
        exit;
    }
    $userid = (int) $_GET["userid"];
    $res = $user->delete(["userid" => $userid]);
    if ($res) {
        echo json_encode($res);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["Status Code" => 404, "Status Message" => "Not Found"], true);
        die;
    }
}
