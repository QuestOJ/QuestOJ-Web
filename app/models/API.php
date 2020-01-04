<?php

class API{
    public static function checkClient($token, $secret = NULL) {
        if ($secret != NULL) {
            if (DB::num_rows("select id from api where token = '{$token}' and secret = '{$secret}'") != 0) {
                return true;
            }
            return false;
        }

        if (DB::num_rows("select id from api where token = '$token'") != 0) {
            return true;
        }

        return false;        
    }

    public static function registerRequest($token, $action) {
        if (!self::checkClient($token)) {
            return "";
        }

        $request = time().uojRandString(22);
        DB::insert("insert into api_request (server, requestID, action, status) VALUES ('$token', '$request', '$action', 'pending')");

        return $request;
    }

    public static function checkRequest($token, $request, $action, $status = "pending") {
        if (DB::num_rows("select id from api_request where server = '$token' and requestID = '$request' and action = '$action' and status = '$status'") != 0) {
            return true;
        }

        return false;          
    }

    public static function finishRequest($token, $request, $status) {
        DB::update("update api_request set status = '$status' where server = '$token' and requestID = '$request'");
    }
}