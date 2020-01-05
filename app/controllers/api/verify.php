<?php

    if (empty($_POST["token"])) {
        die("Please provide server token");
    }

    if (empty($_POST["secret"])) {
        die("Please provide server secret");
    }

    if (empty($_POST["request"])) {
        die("Please provide request id");
    }

    if (empty($_POST["action"])) {
        die("Please provide request action");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);
    $request = DB::escape($_POST["request"]);
    $action = DB::escape($_POST["action"]);

    if (!API::checkClient($token, $secret)) {
        die("No such server");
    }

    $status = API::checkRequest($token, $request, $action, "success");
    
    if ($status) {
        die("success");
    } else {
        die("fail");
    }
?>