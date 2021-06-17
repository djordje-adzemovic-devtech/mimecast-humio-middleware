<?php

namespace App\Helpers;

use Exception;

class ResponseHelper
{
    protected static $next_token = '';
    public static $response = [];

    public static function parseSuccessResponse($response)
    {
        $responseText = json_decode($response);
        if (!empty($responseText->fail)) {
            throw new Exception($responseText->fail[0]->errors[0]->message);
        } else {
            return $responseText;
        }
    }

    public static function getNextToken($response)
    {
        //Extracting token from response headers.
        $has_more_data = false;
        $dictionary_response = json_decode($response);
        if (isset($dictionary_response->meta->pagination)) {
            if (isset($dictionary_response->meta->pagination->next)) {
                $has_more_data = true;
                self::$next_token = $dictionary_response->meta->pagination->next;
            } else {
                self::$next_token = '';
            }
        }

        return [$has_more_data, self::$next_token];
    }

}
