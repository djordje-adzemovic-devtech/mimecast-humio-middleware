<?php
namespace App\Models\Request;

class GetDLPLogsRequest
{
    private $payload;

    public function __construct($oldestFirst, $fromDate, $toDate, $actions, $pageSize, $token)
    {
        $this->payload = [
        'meta' => [
            'pagination'=> [
                'pageSize'=> $pageSize
            ]
        ],
        'data' => [
                [
                    'oldestFirst'=> $oldestFirst
                ]
            ]
        ];
        if ($fromDate) {
            $this->payload['data'][0]['from'] = $fromDate;
        } else {
            $this->payload['data'][0]['from'] = date('c', strtotime(date('c') .' -5 minutes'));;
        }
        if ($toDate) {
            $this->payload['data'][0]['to'] = $toDate;
        } else {
            $this->payload['data'][0]['to'] = date('c', strtotime(date('c')));
        }
        if ($actions) {
            $this->payload['data'][0]['actions'] = $actions;
        }
        if ($token) {
            $this->payload["meta"]["pagination"]["pageToken"] = $token;
        }
    }

    public function getPayload(): string
    {
        return json_encode($this->payload);
    }
}