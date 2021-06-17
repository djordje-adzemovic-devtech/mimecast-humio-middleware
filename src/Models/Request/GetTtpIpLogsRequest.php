<?php


namespace App\Models\Request;


class GetTtpIpLogsRequest
{
    private $payload;

    public function __construct($oldestFirst, $taggedMalicious, $searchField, $identifiers, $query, $fromDate, $toDate, $actions, $pageSize, $token)
    {
        $this->payload = [
            'meta' => [
                'pagination' => [
                    'pageSize' => $pageSize
                ]
            ],
            'data' => [
                [
                    'oldestFirst' => $oldestFirst

                ]
            ]
        ];
        if ($taggedMalicious) {
            $this->payload['data'][0]['taggedMalicious'] = $taggedMalicious;
        }
        if ($searchField) {
            $this->payload['data'][0]['searchField'] = $searchField;
        }
        if ($identifiers) {
            $this->payload['data'][0]['identifiers'] = $identifiers;
        }
        if ($query) {
            $this->payload['data'][0]['query'] = $query;
        }
        if ($fromDate) {
            $this->payload['data'][0]['from'] = $fromDate;
        } else {
            $this->payload['data'][0]['from'] = date('c', strtotime(date('c') .' -5 minutes'));
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
