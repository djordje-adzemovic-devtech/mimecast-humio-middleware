<?php


namespace App\Mapper;


class ThreatIntelFeedMapper
{
    protected $mapper = [];

    public function mapperData($response, $fileType, $feedType)
    {
        if ($fileType == 'csv') {
            array_splice($response[0], 0, 1);
        }
        if ($feedType == 'malware_grid') {
            $this->mapper["tags"] = [
                "host" => $_ENV['BASE_URL'],
                "source" => "threat-intel-feed-malware-grid"
            ];
        } else {
            $this->mapper["tags"] = [
                "host" => $_ENV['BASE_URL'],
                "source" => "threat-intel-feed-malware-customer"
            ];
        }

        $this->mapper["events"] = [];
        foreach ($response[0] as $i => $val) {
            $this->mapper["events"][] = [
                "timestamp" => date('c', strtotime(date('c'))),
                "repo" => $_ENV['HUMIO_REPO'],
                "attributes" => $val
            ];
        }
        return $this->mapper;
    }
}