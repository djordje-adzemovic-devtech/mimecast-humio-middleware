<?php


namespace App\Helpers;

use ErrorException;
use ZipArchive;

class ThreatIntelFeedResponseHelper
{

    public static function parserThreatIntelFeedSuccessResponse($response, $file_type, $compress, $mimecast_endpoint)
    {
        $contentType = $response->getHeader('Content-Type');
        if ($contentType[0] == 'application/octet-stream') {
            $body = $response->getBody()->getContents();
            if ($compress) {
                try {
                    return self::parse_compressed_data($body, $file_type);
                } catch (\ErrorException $e) {
                    echo "Parsing of Threat Intel Feed compressed data failed.";
                }
            } else {
                try {
                    return self::parse_uncompressed_data($body, $file_type);
                } catch (\ErrorException $e) {
                    echo "Parsing of Threat Intel Feed uncompressed data failed.";
                }
            }
        } else {
            $res = json_decode($response->getBody()->getContents());
            if (!empty ($res->fail[0]->errors)) {
                $no_threat_intel_feed = ['No results found for threat intel feed.', 'Unable to compress feeds.'];
                if (in_array($res->fail[0]->errors[0]->message, $no_threat_intel_feed)) {
                    return [];
                } else {
                    throw new ErrorException($res->fail[0]->errors[0]->message);
                }
            }
        }
    }

    public static function parse_compressed_data($response, $file_type)
    {
        $events = [];
        $myfile = fopen(dirname(dirname(dirname(__FILE__)))."/data/threat-intel.zip", "w+") or die("Unable to open file!");
        fwrite($myfile, $response);
        fclose($myfile);

        $zip = new ZipArchive;
        $archive = $zip->open(dirname(dirname(dirname(__FILE__))).'/data/threat-intel.zip');
        if ($archive === true) {
            $zip->extractTo(dirname(dirname(dirname(__FILE__))).'/data/threat-intel');
            $fileList = glob(dirname(dirname(dirname(__FILE__))).'/data/threat-intel/*.csv');
            foreach ($fileList as $key => $files) {
                $content = file_get_contents($files);
                $events[] = self::parse_uncompressed_data($content, $file_type);
            }
            $zip->close();
            $folder = glob(dirname(dirname(dirname(__FILE__)))."/data/threat-intel/*.csv");
            foreach ($folder as $fold) {
                unlink($fold);
            }
            rmdir(dirname(dirname(dirname(__FILE__)))."/data/threat-intel");
            unlink(dirname(dirname(dirname(__FILE__)))."/data/threat-intel.zip");
        } else {
            echo 'failed, code:' . $archive;
        }
        return $events;
    }

    public static function parse_uncompressed_data($response, $file_type)
    {
        $mapped_response = [];
        if ($file_type == 'csv') {
            $rows = str_getcsv($response, "\n");
            foreach ($rows as $row)
            {
                $keys = explode('|', $rows[0]);
                $data = str_getcsv($row, "|");
                $mapped_response[] = array_combine($keys, $data);
            }

        } else if ($file_type === 'stix') {
            $response_array = json_decode($response);
            $mapped_response = $response_array->objects;
        }

        return $mapped_response;
    }
}