<?php


namespace App\Models\Response;


use App\Helpers\ResponseHelper;

class GetThreatIntelFeedResponse extends ResponseHelper
{
    public static function mapResponseData($response) {
        return $response;
    }
}

