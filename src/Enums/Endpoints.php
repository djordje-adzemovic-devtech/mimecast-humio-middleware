<?php

namespace App\Enums;

class Endpoints
{
    public const GET_SIEM_LOGS = '/api/audit/get-siem-logs';
    public const GET_AUDIT_LOGS = '/api/audit/get-audit-events';
    public const GET_TTP_URL_LOGS = '/api/ttp/url/get-logs';
    public const GET_TTP_IP_LOGS = '/api/ttp/impersonation/get-logs';
    public const GET_TTP_AP_LOGS = '/api/ttp/attachment/get-logs';
    public const GET_DLP_LOGS = '/api/dlp/get-logs';
    public const THREAT_INTEL_FEED = '/api/ttp/threat-intel/get-feed';
}
