<?php


namespace App\Mapper;


class TtpUrlMapper
{
    protected $mapper = [];

    public function ttpUrlMapperData($response):array
    {
        $this->mapper["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => "ttp-url-logs"
        ];
        $this->mapper["events"] = [];
        foreach ($response as $i => $val) {
            ;
            $this->mapper["events"][] = [
                "timestamp" => date('c', strtotime(date('c'))),
                "repo" => $_ENV['HUMIO_REPO'],
                "attributes" => $val
            ];
        }

        return $this->mapper;
    }
}