<?php
    require_once("./class.php");
    $u_s=new func("user-services", "milogy");
    $u_s->setFields(["userid", "serviceid", "status", "start", "finish"]);
    if($_SERVER["REQUEST_METHOD"]=="GET")
    {   
        var_dump($_GET);
        if($_GET["req"]="add-user-service")
        {
            echo "test1";
            var_dump($_GET["userid"]);
            var_dump($_GET[$_GET["serviceid"]]);
            if(!isset($_GET["userid"]) && !isset($_GET["serviceid"]))
                die("Some fields are not entered");
            $userid=$_GET['userid'];
            $serviceid=$_GET["serviceid"];
            $res=$service->readRow(["userid"=>$userid, "serviceid"=>$serviceid]);
            if($res)
            {
                echo "The request has already been sent";
            }
            else
            {
                date_default_timezone_set('Asia/Tehran');
                $start=date("Y-m-d H:i");
                $u_s->create(["userid"=>$userid , "serviceid"=>$serviceid, "status"=>false, "start"=>$start]);
                echo "Create, done";
            }
        }
        if($_GET["req"]=="finish-service")
        {
            $id=$_GET["id"];
            $userid=$_GET['userid'];
            $serviceid=$_GET["serviceid"];
            if(!isset($_GET["userid"]) && !isset($_GET["serviceid"]))
                die("Some fields are not entered");
            $res=$service->readRow(["userid"=>$userid, "serviceid"=>$serviceid]);
            if($res && $res["status"]==false)
            {
                date_default_timezone_set('Asia/Tehran');
                $finish=date("Y-m-d H:i");
                $u_s->update(["id"=>$id], ["status"=>true, "finish"=>$finish]);
                echo "Update, done";
            }
            else
                echo "This service was already finished";
        }
    }
    
?>