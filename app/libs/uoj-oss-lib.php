<?php

    date_default_timezone_set("GMT");  
    require $_SERVER['DOCUMENT_ROOT'].'/app/vendor/aliyunoss/autoload.php';

    use OSS\OssClient;
    use OSS\Core\OssException;
    use OSS\Http\RequestCore;
    use OSS\Http\ResponseCore;

    function percentEncode($str) {  
        $res = urlencode($str);  
        $res = preg_replace('/\+/', '%20', $res);  
        $res = preg_replace('/\*/', '%2A', $res);  
        $res = preg_replace('/%7E/', '~', $res);  
        return $res;  
    }  

    function calcSignature($parameters, $accessKeySecret) {  
        ksort($parameters);  
        $canonicalizedQueryString = '';  
        foreach($parameters as $key => $value) {  
            $canonicalizedQueryString .= '&' . percentEncode($key)  
                . '=' . percentEncode($value);  
        }  
        $stringToSign = 'GET&%2F&' . percentencode(substr($canonicalizedQueryString, 1));  
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));  
        return $signature;  
    }  

    function getSTSAccount() {
        $accessKeyId = UOJConfig::$data['data']['accessKeyID'];
        $accessKeySecret = UOJConfig::$data['data']['accessKeySecret'];
        $roleArn = UOJConfig::$data['data']['roleArn'];

        $signatureNonce = time().rand(100000000,200000000);
        $url = "https://sts.aliyuncs.com/?";

        $data = array(  
            'Format' => 'json',  
            'Version' => '2015-04-01',  
            'AccessKeyId' => $accessKeyId,  
            'SignatureMethod' => 'HMAC-SHA1',  
            'SignatureNonce'=> $signatureNonce,  
            'Timestamp' => date('Y-m-d\TH:i:s\Z'),  
            'SignatureVersion' => '1.0',  

            'Action' => 'AssumeRole',
            'RoleArn' => $roleArn,
            'RoleSessionName' => 'sts',
            'DurationSeconds' => 3600,
        );  

        $data['Signature'] = calcSignature($data, $accessKeySecret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url.http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($html, true);
    }

    function getSignedURL($object, $timeout) {
        $credentials = getSTSAccount()['Credentials'];

        $accessKeyId = $credentials['AccessKeyId'];
        $accessKeySecret = $credentials['AccessKeySecret'];
        
        $endpoint = UOJConfig::$data['data']['endpoint'];
        $bucket= UOJConfig::$data['data']['bucket'];
        $securityToken = $credentials['SecurityToken'];

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);
            $signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
        } catch (OssException $e) {
            return;
        }

        return $signedUrl;
    }

    function getStatementURL($problem) {
        return getSignedURL("data/".$problem['id']."/statement.pdf", 900);
    }

    function getTestdataURL($problem) {
        return getSignedURL("data/".$problem['id']."/testdata.zip", 1800);
    }

    function getDownloadURL($problem) {
        return getSignedURL("data/".$problem['id']."/download.zip", 1800);
    }
?>