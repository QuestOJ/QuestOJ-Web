<?php

    if (empty($_POST["token"])) {
        die("Please provide server token");
    }

    if (empty($_POST["secret"])) {
        die("Please provide server secret");
    }

    if (empty($_POST["action"])) {
        die("Please provide request action");
    }

    if (empty($_POST["callback"])) {
        die("Please provide callback url");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);
    $action = DB::escape($_POST["action"]);
    $callback = DB::escape($_POST["callback"]);

    if (!API::checkClient($token, $secret)) {
        die("No such server");
    }

    $requestID = API::registerRequest($token, $action, $callback);
    die($requestID);

?>