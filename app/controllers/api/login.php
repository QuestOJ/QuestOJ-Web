<?php

    if (empty($_GET["token"])) {
        die("Please provide server token");
    }

    if (empty($_GET["RequestID"])) {
        die("Please provide request id");
    }

    $token == DB::escape($_GET["token"]);
    $RequestID == DB::escape($_GET["RequestID"]);

    if (!API::checkClient($token)) {
        die("No such server");
    }

    if (!API::checkRequest($token, $RequestID, "login")) {
        die("No such request");
    }

    if (Auth::check()) {
        API::finishRequest($token, $RequestID, "success");
    }

    header("Location:/login");
?>