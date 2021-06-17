<?php


namespace App\Models\Request;


class GetThreatIntelFeedRequest
{
    private $payload;

    public function __construct($fromDate, $toDate, $compress, $fileType, $feedType, $token)
    {
        $this->payload = [
            'data' => [
                [
                    'compress' => $compress,
                    'fileType' => $fileType
                ]
            ]
        ];
        if ($fromDate) {
            $this->payload['data'][0]['start'] = $fromDate;
        } else {
            $this->payload['data'][0]['start'] = date('c', strtotime(date('c') . ' -5 minutes'));;
        }
        if ($toDate) {
            $this->payload['data'][0]['end'] = $toDate;
        } else {
            $this->payload['data'][0]['end'] = date('c', strtotime(date('c')));
        }
        if ($feedType) {
            $this->payload['data'][0]['feedType'] = $feedType;
        }
        if ($token) {
            $this->payload['data'][0]['token'] = $token;
        }
    }

    public function getPayload(): string
    {
        return json_encode($this->payload);
    }
}
