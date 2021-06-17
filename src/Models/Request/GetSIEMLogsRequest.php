<?php


namespace App\Models\Request;


class GetSIEMLogsRequest
{
    protected $payload;
    public function __construct($token, $fileFormat)
    {
        $this->payload = [
            'data' => [
                [
                    'type' => "MTA",
                    'compress' => True,
                    'fileFormat' => $fileFormat
                ]
            ]
        ];
        if ($token) {
            $this->payload['data'][0]['token'] = $token;
        }
    }
    public function getPayload(): string
    {
        return json_encode($this->payload);
    }
}
