<?php


namespace App\Models\Request;


class GetAuditEventsRequest
{
    protected $payload;

    public function __construct($startDatetime, $endDatetime, $query, $categories, $pageSize, $token)
    {
    $this->payload = [
        'data' => [],
        'meta' => [
            'pagination' => [
                'pageSize' => $pageSize
            ]
        ]
    ];
            if ($startDatetime) {
                $this->payload['data'][0]['startDateTime'] = $startDatetime;
            } else {
                $this->payload['data'][0]['startDateTime'] = date('c', strtotime(date('c') .' -5 minutes'));
            }
            if ($endDatetime) {
                $this->payload['data'][0]['endDateTime'] = $endDatetime;
            } else {
                $this->payload['data'][0]['endDateTime'] = date('c', strtotime(date('c')));
            }
            if ($token) {
                $this->payload["meta"]["pagination"]["pageToken"] = $token;
            }
            if ($query) {
                $this->payload['data'][0]['query'] = $query;
            }
            if ($categories) {
                $this->payload['data'][0]['categories'] = $categories;
            }
            $this->payload = json_encode($this->payload);
    }
    public function getPayload(): string
    {
        return $this->payload;
    }
}
