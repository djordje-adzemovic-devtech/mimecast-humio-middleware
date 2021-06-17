<?php

namespace App\Mapper;

class AuditLogMapper
{
    protected $mapper = [];

    public function auditEventsMapperData($response): array
    {
        $this->mapper["tags"] = [
            "host" => $_ENV['BASE_URL'],
            "source" => "audit-events"
        ];

        $this->mapper["events"] = [];
        if (is_array($response[0])) {
            foreach ($response[0]['data'] as $i => $val) {
                $this->mapper["events"][] = [
                    "timestamp" => date('c', strtotime(date('c'))),
                    "repo" => $_ENV['HUMIO_REPO'],
                    "attributes" => $val
                ];
            }
        }
        return $this->mapper;
    }

    public function getMapper(): array
    {
        return $this->mapper;
    }
}