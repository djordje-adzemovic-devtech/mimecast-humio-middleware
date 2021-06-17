<?php

namespace App\Console;

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

use App\Classes\SavedLogsTimestamps;
use App\Helpers\RequestHelper;
use Dotenv\Dotenv;
use App\Classes\MimecastApiWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;


/**
 * Command.
 */
class HumioCommand extends Command
{
    /**
     * Configure.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('humio-ingest');
        $this->setDescription('Command to ingest data retrieved from Mimecast to Humio');
        $this->addArgument('log-type', InputArgument::IS_ARRAY, 'Which log type you want to retrieve from Mimecast', ['audit-events','dlp-logs','ttp-ip-logs','ttp-ap-logs','ttp-url-logs','threat-intel-logs-malware-grid','threat-intel-logs-malware-customer','siem-logs']);
        $this->addOption('retrievalPeriod', null, InputOption::VALUE_OPTIONAL, 'Timeframe for logs retrieval', 5);
    }

    protected function getHumioClient()
    {
        $dotenv = new Dotenv(dirname(dirname(dirname(__FILE__))));
        $dotenv->load();

        $humioApiToken = ["Authorization" => "Bearer " . $_ENV['HUMIO_API_TOKEN']];

        return new Client(
            [
                'verify' => RequestHelper::humioCertificateVerifyConfiguration(),
                "base_uri" => $_ENV['HUMIO_BASE_URL'],
                "headers" => $humioApiToken,
            ]
        );
    }

    private function determineDateFrom ($logType, $timeframe)
    {
        $filePath = dirname(dirname(dirname(__FILE__))) . '/timestamp_checkpoint.txt';
        @$lastRetrievedLogsTimestampsArray = unserialize(file_get_contents($filePath));
        $dateFrom = $lastRetrievedLogsTimestampsArray[$logType[0]];
        $dateTo = date('c', strtotime(date('c')));
        $fromLastRetrievalInterval = strtotime($dateTo) - strtotime($dateFrom);

        if ($fromLastRetrievalInterval > ($timeframe * 60)) {
            $dateFrom = date('c', strtotime(date('c') . '-' . $fromLastRetrievalInterval . ' seconds'));
        } else {
            $dateFrom = date('c', strtotime(date('c') . '-' . $timeframe . ' minutes'));
        }

        return $dateFrom;
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int integer 0 on success, or an error code
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = new Dotenv(dirname(dirname(dirname(__FILE__))));
        $dotenv->load();

        $filePath = dirname(dirname(dirname(__FILE__))) . '/timestamp_checkpoint.txt';
        $logType = $input->getArgument('log-type');
        $timeframe = $input->getOption('retrievalPeriod');

        $dateFrom = $this->determineDateFrom($logType, $timeframe);
        $dateTo = $dateTo = date('c', strtotime(date('c')));

        $obj = new SavedLogsTimestamps();
        $api = new MimecastApiWrapper();
        foreach ($logType as $type) {
            switch ($type) {
                case 'audit-events':
                    $response = $api->getAuditLogs($dateFrom, $dateTo);
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'dlp-logs':
                    $response = $api->getDlpLogs(false,$dateFrom, $dateTo);
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'ttp-ip-logs':
                    $response = $api->getTtpIPLogs(false, '', '', '', '', $dateFrom, $dateTo);
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'ttp-ap-logs':
                    $response = $api->getTtpAPLogs(false, $dateFrom, 'all', $dateTo);
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'ttp-url-logs':
                    $response = $api->getTtpUrlLogs(false, $dateFrom, 'all', $dateTo);
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'siem-logs':
                    $response = $api->getSiemLogs();
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response[0])]);
                    break;
                case 'threat-intel-logs-malware-grid':
                    $response = $api->getThreatIntelFeed($dateFrom, $dateTo, $compress = false, $fileType = 'csv', $feedType = 'malware_grid', $token = '');
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
                    break;
                case 'threat-intel-logs-malware-customer':
                    $response = $api->getThreatIntelFeed($dateFrom, $dateTo, $compress = false, $fileType = 'csv', $feedType = 'malware_customer', $token = '');
                    $client = $this->getHumioClient();
                    $client->request('POST', '/api/v1/ingest/humio-structured', ['body' => json_encode($response)]);
            }
            $obj->storeLastLogTimestamp($type, $dateTo);
        }

        unset($api);
        return 0;
    }
}