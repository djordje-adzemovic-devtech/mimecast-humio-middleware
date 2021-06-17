<?php

namespace App\Classes;

interface iApiWrapper
{
    public function getSiemLogs();
    public function getAuditLogs($startDatetime='', $endDatetime='', $query='', $categories='', $pageSize=500, $token='');
    public function getTtpUrlLogs($oldestFirst=false, $fromDate='', $route='all', $toDate='', $scanResult='all', $pageSize=500, $token='');
    public function getTtpIPLogs($oldestFirst=false, $taggedMalicious='', $searchField='', $identifiers='', $query='', $fromDate='', $toDate='', $actions='', $pageSize=500, $token='');
    public function getTtpAPLogs($oldestFirst=false, $fromDate='', $route='all', $toDate='', $result='all', $pageSize=500, $token='');
    public function getDlpLogs($oldest_first=false, $fromDate='', $toDate='', $actions='', $pageSize=500,$token='');
    public function getThreatIntelFeed($fromDate='', $toDate='', $compress= false, $fileType='csv', $feedType='', $token='');
}