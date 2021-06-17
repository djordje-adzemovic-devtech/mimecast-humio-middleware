<?php

namespace App\Mapper;

class DlpLogsMapper
{
    protected $mapper = [];

    public function dlpMapper($response): array
    {
        $this->mapper["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => "dlp-logs"
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