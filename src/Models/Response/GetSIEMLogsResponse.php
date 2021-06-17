<?php


namespace App\Models\Response;


use App\Helpers\SIEMResponseHelper;

class GetSIEMLogsResponse extends SIEMResponseHelper
{
    public static function mapResponseData($response)
    {
        if ($response) {
            if (SIEMResponseHelper::$next_token) {
                SIEMResponseHelper::$response += $response;
            } else {
                SIEMResponseHelper::$response = $response;
            }
        }

        return SIEMResponseHelper::$response;
    }

}

