<?php

namespace App\Helpers;

class RequestHelper {

    public static function setInitialValues(): array
    {
        //Generating default values before execution enters the loop.
        return [[], [], '', true];
    }
    public static function humioCertificateVerifyConfiguration()
    {
        $verifyKeyValue = false;

        if (isset($_ENV['SERVER_CERT_LOCATION'])) {
            $certLocation = $_ENV['SERVER_CERT_LOCATION'];
            $verifyKeyValue = $certLocation;
            if (isset($_ENV['SERVER_CERT_PASSWORD'])) {
                $certPass = $_ENV['SERVER_CERT_PASSWORD'];
                $verifyKeyValue =  [$certLocation, $certPass];
            }
        }
        return $verifyKeyValue;
    }
}
