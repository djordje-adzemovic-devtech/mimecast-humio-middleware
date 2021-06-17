<?php


namespace App\Models\Response;


use App\Helpers\ResponseHelper;

class GetAuditEventsResponse extends ResponseHelper
{
    public static function mapResponseData($response)
    {
        if (!empty($response->meta->pagination->next)) {
            return ResponseHelper::$response  = array_merge((array) ResponseHelper::$response, (array) $response);
        } else {
            return ResponseHelper::$response = $response;
        }
    }
}

