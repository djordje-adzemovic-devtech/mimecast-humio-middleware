<?php


namespace App\Mapper;

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

use Dotenv\Dotenv;

class SIEMMapper
{
    protected $mapper = [];

    public function siemLogsMapperData($response)
    {
        $dotenv = new Dotenv(dirname(dirname(dirname(__FILE__))));
        $dotenv->load();

        $payloadData = [];
        foreach ($response as $value) {
            $payloadData[] = $this->makeEventsArray($value);
        }

        return $payloadData;
    }

    private function makeEventsArray($data)
    {
        $source = '';
        if ($data['type'] == 'SIEM delivery logs') {
            $source = 'siem-delivery-logs';
        } elseif ($data['type'] == 'SIEM process logs') {
            $source = 'siem-process-logs';
        } elseif ($data['type'] == 'SIEM receipt logs') {
            $source = 'siem-receipt-logs';
        } elseif ($data['type'] == 'SIEM av logs') {
            $source = 'siem-av-logs';
        } elseif ($data['type'] == 'SIEM impersonation logs') {
            $source = 'siem-impersonation-logs';
        } elseif ($data['type'] == 'SIEM spam event thread logs') {
            $source = 'siem-spam-event-thread-logs';
        } elseif ($data['type'] == 'SIEM TTP URL logs') {
            $source = 'siem-ttp-url-logs';
        } elseif ($data['type'] == 'SIEM journal logs') {
            $source = 'siem-journal-logs';
        } elseif ($data['type'] == 'SIEM Attachment Protect logs') {
            $source = 'siem-ap-logs';
        } elseif ($data['type'] == 'SIEM Internal Email Protect logs') {
            $source = 'siem-email-protect-logs';
        }

        $mappedData["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => $source
        ];
        $mappedData["events"] = [];
        $mappedData["events"][] = [
            "timestamp" => date('c', strtotime(date('c'))),
            "repo" => $_ENV['HUMIO_REPO'],
            "attributes" => $data
        ];

        return $mappedData;
    }
}