<?php


namespace App\Helpers;

use Dotenv\Dotenv;
use ZipArchive;

class SIEMResponseHelper
{
    public static $response = [];
    protected static $next_token = '';

    public static function parseSiemSuccessResponse($response, $file_format, $mimecast_endpoint)
    {
        $responseHeaderContentType = $response->getHeader('Content-Type');
        if ($responseHeaderContentType[0] === 'application/octet-stream') {
                return self::parseCompressedData($response, $file_format);
        }
    }

    private static function parseCompressedData($response, $file_format)
    {
        $files = [];
        $body = $response->getBody()->getContents();
        $myfile = fopen(dirname(dirname(dirname(__FILE__)))."/data/siem.zip", "w+") or die("Unable to open file!");
        fwrite($myfile, $body);
        fclose($myfile);
        $zip = new ZipArchive;
        $archive = $zip->open(dirname(dirname(dirname(__FILE__)))."/data/siem.zip");
        if ($archive === true) {
            $zip->extractTo(dirname(dirname(dirname(__FILE__)))."/data/siem");
            $fileList = glob(dirname(dirname(dirname(__FILE__))).'/data/siem/*.siem');
            $files = self::parse_key_value_response($fileList);
            $zip->close();
            $folder = glob(dirname(dirname(dirname(__FILE__)))."/data/siem/*.siem");
            foreach ($folder as $fold) {
                unlink($fold);
            }
            rmdir(dirname(dirname(dirname(__FILE__)))."/data/siem");
            unlink(dirname(dirname(dirname(__FILE__)))."/data/siem.zip");
        } else {
            echo 'failed, code:' . $archive;
        }

        return $files;
    }

    private static function parse_key_value_response($files)
    {
        $dotenv = new Dotenv(dirname(dirname(dirname(__FILE__))));
        $dotenv->load();

        $parsedAllLogs = [];
        foreach ($files as $file) {

            $content = file_get_contents($file);
            $dataArray = explode("\r", $content);
            $parsedLogs = [];
            foreach ($dataArray as $item) {
                if (!empty($item)) {
                    $itemExploded = explode('|',$item);
                    $parsedKeyValues = [];
                    foreach ($itemExploded as $exploded) {
                        $keyValueExploded = explode('=',$exploded);
                        $parsedKeyValues[$keyValueExploded[0]] = $keyValueExploded[1];
                        if (str_contains($file, $_ENV['SIEM_DELIVERY_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM delivery logs';
                        } elseif (str_contains($file, $_ENV['SIEM_PROCESS_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM process logs';
                        } elseif (str_contains($file, $_ENV['SIEM_RECEIPT_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM receipt logs';
                        } elseif (str_contains($file, $_ENV['SIEM_AV_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM av logs';
                        } elseif (str_contains($file, $_ENV['SIEM_IMPERSONATION_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM impersonation logs';
                        } elseif (str_contains($file, $_ENV['SIEM_SPAM_EVENT_THREAD_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM spam event thread logs';
                        } elseif (str_contains($file, $_ENV['SIEM_TTP_URL_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM TTP URL logs';
                        } elseif (str_contains($file, $_ENV['SIEM_JRNL_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM journal logs';
                        } elseif (str_contains($file, $_ENV['SIEM_TTP_AP_PREFIX'])) {
                            $parsedKeyValues['type'] = 'SIEM Attachment Protect logs';
                        } elseif (str_contains($file, $_ENV['SIEM_EMAIL_PROTECT'])) {
                            $parsedKeyValues['type'] = 'SIEM Internal Email Protect logs';
                        }
                    }
                    $parsedLogs[] = $parsedKeyValues;
                }
            }
            $parsedAllLogs = array_merge($parsedAllLogs, $parsedLogs);
        }
        return $parsedAllLogs;
    }

    public static function get_siem_next_token($response)
    {
        $has_more_logs = false;
        if ($response instanceof \GuzzleHttp\Psr7\Response){
            $header = $response->getHeaders();
            if (isset($header['mc-siem-token'])) {
                $has_more_logs = true;
                self::$next_token = $header['mc-siem-token'][0];
            }
        }
        return [$has_more_logs, self::$next_token];
    }
}