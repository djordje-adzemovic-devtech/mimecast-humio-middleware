<?php


namespace App\Models\Response;

use App\Helpers\ResponseHelper;

class GetTtpUrlLogsResponse extends ResponseHelper {

    public static function mapResponseData($response){
        if (ResponseHelper::$next_token) {
            ResponseHelper::$next_token  = array_merge((array) ResponseHelper::$next_token, (array) $response->data[0]->clickLogs);

        } else {
            ResponseHelper::$next_token = array_merge([],(array)$response->data[0]->clickLogs);
        }
        return ResponseHelper::$next_token;
    }
}


