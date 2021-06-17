<?php


namespace App\Mapper;


class TtpApMapper
{
    protected $mapper = [];

    public function ttpApMapperData($response)
    {
        $this->mapper["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => "ttp-attachment-logs"
        ];
        $this->mapper["events"] = [];
        foreach ($response as $i => $val) {
            $this->mapper["events"][] = [
                "timestamp" => date('c', strtotime(date('c'))),
                "repo" => $_ENV['HUMIO_REPO'],
                "attributes" => $val
            ];
        }

        return $this->mapper;
    }
}