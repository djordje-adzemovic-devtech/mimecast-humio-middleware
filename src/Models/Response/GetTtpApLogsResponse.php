<?php


namespace App\Models\Response;


use App\Helpers\ResponseHelper;

class GetTtpApLogsResponse extends ResponseHelper
{
    public static function mapResponseData($response)
    {
        if (ResponseHelper::$next_token) {
            ResponseHelper::$response  = array_merge((array) ResponseHelper::$response, (array) $response->data[0]->attachmentLogs);
        } else {
            ResponseHelper::$response = array_merge([],(array)$response->data[0]->attachmentLogs);
        }

        return ResponseHelper::$response;
    }

}

