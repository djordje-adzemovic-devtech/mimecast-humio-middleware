<?php


namespace App\Classes;

use App\Enums\Endpoints;
use App\Helpers\RequestHelper;
use App\Helpers\RequestUiGenerator;
use App\Helpers\ResponseHelper;
use App\Helpers\SIEMResponseHelper;
use App\Helpers\ThreatIntelFeedResponseHelper;
use App\Mapper\AuditLogMapper;
use App\Mapper\DlpLogsMapper;
use App\Mapper\SIEMMapper;
use App\Mapper\ThreatIntelFeedMapper;
use App\Mapper\TtpApMapper;
use App\Mapper\TtpIpMapper;
use App\Mapper\TtpUrlMapper;
use App\Models\Request\GetAuditEventsRequest;
use App\Models\Request\GetSIEMLogsRequest;
use App\Models\Request\GetDLPLogsRequest;
use App\Models\Request\GetThreatIntelFeedRequest;
use App\Models\Request\GetTtpApLogsRequest;
use App\Models\Request\GetTtpIpLogsRequest;
use App\Models\Request\GetTtpUrlLogsRequest;
use App\Models\Response\GetAuditEventsResponse;
use App\Models\Response\GetDLPLogsResponse;
use App\Models\Response\GetSIEMLogsResponse;
use App\Models\Response\GetThreatIntelFeedResponse;
use App\Models\Response\GetTtpApLogsResponse;
use App\Models\Response\GetTtpIpLogsResponse;
use App\Models\Response\GetTtpUrlLogsResponse;
use GuzzleHttp\Client;

class MimecastApiWrapper implements iApiWrapper
{
    public function setMimecastAuthHeader($requestUri)
    {
        $reqUiObj = new RequestUiGenerator();
        $date = gmdate('D, d M Y H:i:s', strtotime(date('Y-m-d H:i')));
        $requestUuid = $reqUiObj->guidv4();
        $dataToSign = $date . ' UTC' . ':' . $requestUuid . ':' . $requestUri . ':' . $_ENV['APP_KEY'];
        $sk = base64_decode($_ENV['SECRET_KEY']);
        $dts = utf8_encode($dataToSign);
        $secretKeyEnc = hash_hmac('sha1', $dts, $sk, true);
        $bytes = base64_encode($secretKeyEnc);
        $trimmed = rtrim($bytes);

        $client = new Client(
            [
                "verify" => RequestHelper::humioCertificateVerifyConfiguration(),
                "base_uri" => $_ENV['BASE_URL'],
                "headers" => [
                    "Authorization" => "MC " . $_ENV['ACCESS_KEY'] . ":" . utf8_decode($trimmed),
                    "x-mc-app-id" => $_ENV['APP_ID'],
                    "x-mc-date" => $date . ' UTC',
                    "x-mc-req-id" => $requestUuid,
                    "Content-Type" => "application/json"
                ],
            ]
        );
        return $client;
    }
    public function sendPostRequest($requestUri, $model)
    {
        $client = $this->setMimecastAuthHeader($requestUri);
        return $client->request('POST', $requestUri,['body' => $model->getPayload()]);
    }
    public function getSiemLogs()
    {
        $has_more_logs = true;
        $mappedResponseData = [];
        $model = [];
        $checkpoint = fopen(dirname(dirname(dirname(__FILE__)))."/checkpoint.txt","w+");
        $token = file_get_contents(dirname(dirname(dirname(__FILE__)))."/checkpoint.txt");
        $fileFormat = "key_value";
        $lastTokenSent = '';
        $mapperData = [];
        $mapper = new SIEMMapper();

        while ($has_more_logs) {
            $model = new GetSIEMLogsRequest($token, $fileFormat);
            $response = $this->sendPostRequest(Endpoints::GET_SIEM_LOGS, $model);
            $success_response = SIEMResponseHelper::parseSiemSuccessResponse($response, $fileFormat, Endpoints::GET_SIEM_LOGS);
            $mappedResponseData = array_merge(GetSIEMLogsResponse::mapResponseData($success_response), $mappedResponseData);
            [$has_more_logs, $token] = SIEMResponseHelper::get_siem_next_token($response);
            if ($token != '') {
                $lastTokenSent .= $token;
            }
            SIEMResponseHelper::$response = [];
        }

        fwrite($checkpoint, $lastTokenSent);
        fclose($checkpoint);
        $mapperData[] = $mapper->siemLogsMapperData($mappedResponseData);
        return $mapperData;
    }

    public function getAuditLogs($startDatetime='', $endDatetime='', $query='', $categories='', $pageSize=500, $token='')
    {
        $requestHelper = new RequestHelper();
        $mapperData = [];
        [$mappedResponseData, $model, $next_token, $has_more_logs] = $requestHelper->setInitialValues();
        while ($has_more_logs) {
            $model = new GetAuditEventsRequest($startDatetime, $endDatetime, $query, $categories, $pageSize, $next_token);
            $response = $this->sendPostRequest(Endpoints::GET_AUDIT_LOGS, $model);
            $successResponse = ResponseHelper::parseSuccessResponse($response->getBody()->getContents());
            $mappedResponseData[] = GetAuditEventsResponse::mapResponseData($successResponse);
            [$has_more_logs, $next_token] = ResponseHelper::getNextToken($response->getBody()->getContents());

        }

        $mapper = new AuditLogMapper();
        $mapperData[] = $mapper->auditEventsMapperData($mappedResponseData);
        return $mapperData;
   }

    public function getDlpLogs($oldestFirst=False, $fromDate='', $toDate='', $actions='', $pageSize=500, $token='')
    {
        $requestHelper = new RequestHelper();
        [$mapped_response_data, $model, $next_token, $has_more_logs] = $requestHelper->setInitialValues();
        while ($has_more_logs) {
            $model = new GetDLPLogsRequest($oldestFirst, $fromDate, $toDate, $actions, $pageSize, $next_token);
            $response = $this->sendPostRequest(Endpoints::GET_DLP_LOGS, $model);
            $success_response = ResponseHelper::parseSuccessResponse($response->getBody()->getContents());
            $mapped_response_data = GetDLPLogsResponse::mapResponseData($success_response);
            [$has_more_logs, $next_token] = ResponseHelper::getNextToken($response->getBody()->getContents());

        }
        $mapper = new DlpLogsMapper();
        $mapperData[] = $mapper->dlpMapper($mapped_response_data);
        return $mapperData;
    }

    public function getTtpUrlLogs($oldestFirst=false, $fromDate='', $route='all', $toDate='', $scanResult='all', $pageSize=500, $token='')
    {
        $requestHelper = new RequestHelper();
        [$mapped_response_data, $model, $next_token, $has_more_logs] = $requestHelper->setInitialValues();
        while ($has_more_logs) {
            $model = new GetTtpUrlLogsRequest($oldestFirst, $fromDate, $route, $toDate, $scanResult, $pageSize, $next_token);
            $response = $this->sendPostRequest(Endpoints::GET_TTP_URL_LOGS, $model);
            $success_response = ResponseHelper::parseSuccessResponse($response->getBody()->getContents());
            $mapped_response_data = GetTtpUrlLogsResponse::mapResponseData($success_response);
            [$has_more_logs, $next_token] = ResponseHelper::getNextToken($response->getBody()->getContents());
        }
        $mapper = new TtpUrlMapper();
        $mapperData[] = $mapper->ttpUrlMapperData($mapped_response_data);
        return $mapperData;

    }

    public function getTtpIPLogs($oldestFirst=false, $taggedMalicious='', $searchField='', $identifiers='', $query='', $fromDate='', $toDate='', $actions='', $pageSize=500, $token='')
    {
        $requestHelper = new RequestHelper();
        [$mapped_response_data, $model, $next_token, $has_more_logs] = $requestHelper->setInitialValues();
        while ($has_more_logs) {
            $model = new GetTtpIpLogsRequest($oldestFirst, $taggedMalicious, $searchField, $identifiers, $query,
                $fromDate, $toDate, $actions, $pageSize, $next_token);
            $response = $this->sendPostRequest(Endpoints::GET_TTP_IP_LOGS, $model);
            $success_response = ResponseHelper::parseSuccessResponse($response->getBody()->getContents());
            $mapped_response_data = GetTtpIpLogsResponse::mapResponseData($success_response);
            [$has_more_logs, $next_token] = ResponseHelper::getNextToken($response->getBody()->getContents());

        }
        $mapper = new TtpIpMapper();
        $mapperData[] = $mapper->ttpIpMapperData($mapped_response_data);
        return $mapperData;
    }

    public function getTtpAPLogs($oldestFirst=false, $fromDate='', $route='all', $toDate='', $result='all', $pageSize=500, $token='')
    {
        $requestHelper = new RequestHelper();
        [$mapped_response_data, $model, $next_token, $has_more_logs] = $requestHelper->setInitialValues();
        while ($has_more_logs) {
            $model = new GetTtpApLogsRequest($oldestFirst, $fromDate, $route, $toDate, $result, $pageSize, $next_token);
            $response = $this->sendPostRequest(Endpoints::GET_TTP_AP_LOGS, $model);
            $success_response = ResponseHelper::parseSuccessResponse($response->getBody()->getContents());
            $mapped_response_data = GetTtpApLogsResponse::mapResponseData($success_response);
            [$has_more_logs, $next_token] = ResponseHelper::getNextToken($response->getBody()->getContents());
        }
        $mapper = new TtpApMapper();
        $mapperData[] = $mapper->ttpApMapperData($mapped_response_data);
        return $mapperData;
    }

    public function getThreatIntelFeed($fromDate='', $toDate='', $compress = true, $fileType='csv', $feedType='', $token='')
    {
        $mapperData= [];
        $mapped_response_data= [];
        $getThreatIntelFeedLogs = new GetThreatIntelFeedResponse();
        $model =  new GetThreatIntelFeedRequest($fromDate, $toDate, $compress, $fileType, $feedType, $token);
        $response = $this->sendPostRequest(Endpoints::THREAT_INTEL_FEED, $model);
        $success_response = ThreatIntelFeedResponseHelper::parserThreatIntelFeedSuccessResponse($response, $fileType, $compress, Endpoints::THREAT_INTEL_FEED);
        $mapped_response_data[] = $getThreatIntelFeedLogs->mapResponseData($success_response);
        $mapper = new ThreatIntelFeedMapper();
        $mapperData[] = $mapper->mapperData($mapped_response_data, $fileType, $feedType);
        return $mapperData;
    }

}