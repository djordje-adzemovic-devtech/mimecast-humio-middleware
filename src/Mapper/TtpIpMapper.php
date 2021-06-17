<?php


namespace App\Mapper;


class TtpIpMapper
{
    protected $mapper = [];

    public function ttpIpMapperData($response)
    {
        $this->mapper["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => "ttp-impersonation-logs"
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