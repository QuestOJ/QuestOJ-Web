<?php

    if (empty($_POST["token"])) {
        die("Authentication token required (101)");
    }

    if (empty($_POST["secret"])) {
        die("Authentication secret required (102)");
    }

    $token = DB::escape($_POST["token"]);
    $secret = DB::escape($_POST["secret"]);

    if (!API::checkClient($token, $secret)) {
        die("Authentication failed (110)");
    }

    if (!authenticateJudger()) {
        die("Authentication failed (111)");
    }

    die("ok");
?>