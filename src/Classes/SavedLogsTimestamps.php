<?php


namespace App\Classes;

class SavedLogsTimestamps
{
    public static function createEmptyLogsTimestampObject()
    {
        return $object = [
            "audit-events" => "",
            "dlp-logs" => "",
            "ttp-ip-logs" => "",
            "ttp-ap-logs" => "",
            "ttp-url-logs" => "",
            "threat-intel-logs-malware-grid" => "",
            "threat-intel-logs-malware-customer" => ""
        ];
    }
    public function initializeLastLogTimestampStorage($filePath) {
        $fp = fopen($filePath, "w+");
        $object = self::createEmptyLogsTimestampObject();
        $objData = serialize($object);
        fwrite($fp, $objData);
        fclose($fp);
    }
    public function storeLastLogTimestamp($logType, $timestamp)
    {
        $filePath = dirname(dirname(dirname(__FILE__))) . '/timestamp_checkpoint.txt';
        if (!file_exists($filePath)) {
            $this->initializeLastLogTimestampStorage($filePath);
        }
        $lastLogTimestamps = unserialize(file_get_contents($filePath));
        $lastLogTimestamps[$logType] = $timestamp;
        $objData = serialize($lastLogTimestamps);
        $fp = fopen($filePath, "w");
        fwrite($fp, $objData);
        fwrite($fp, "");
        fclose($fp);
    }
}