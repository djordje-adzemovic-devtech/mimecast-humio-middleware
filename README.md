# MIMECAST INTEGRATION
The purpose of this solution (middleware) is to fetch logs from Mimecast API periodically (on a given period as configured in CRON and command line) and ingest it into Humio cloud. 

## Package Contents
Dashboards    

## Use Case
- SecOps 
- ITOps 

## Technology Vendor
Mimecast

## Support
info@mimecast.com

## Dependencies
-  PHP version 7.2.24 installed 
-  To have composer installed (version 2.x.x)

## Installation
The purpose of this solution (middleware) is to fetch logs from MimecastAPI periodically (on a given period as configured in CRON and command line) and ingest it into Humio cloud. If needed, the logs can be fetched manually with command line commands available in the solution (steps are described in the Configuration Guide chapter of this document).

CRON Job (or Scheduled Task) executes command configured in middleware to fetch log data from Mimecast, parse and map the data and ingest it to Humio (via HTTP) in specific format. This way, the Mimecast log data are ingested into Humio and can be further queried, analyzed, and displayed in corresponding dashboards.

Deployment and Configuration Guide

Prerequisites

To deploy the solution, some basic PHP related prerequisites should be met. The solution is based on PHP 7.2 and Composer which is used to download and install all dependencies needed for this middleware to work (via composer.json file). The prerequisites needed for deployment are:
 
- To have PHP version 7.2.24 installed (type php -v to check php version) 
- To have composer installed (version 2.x.x). To check if you have composer installed, type composer in your CLI.
- To have access to Humio cloud where data will be ingested

Deployment Guide

The solution with all relevant source code can be found on https://github.com/djordje-adzemovic-devtech/mimecast-humio-middleware. Clone this project with HTTPS.

The next step is to install dependencies for the solution (middleware). To do that, open the terminal, change the directory to the location where the solution is cloned, and type composer install.

Solution's Directory Structure

When you extract the solution to a specific directory you will see several directories:

- bin – a directory with console php file that provides CLI functionality of the solution (commands and parameters)
- data – the directory for temporary storage of SIEM log files
- src – the directory that contains solution's source code

There is a few more files in the same directory:

- .env – the configuration file (will be described in detail in the next chapter)
- checkpoint.txt – file for storing token for next bulk of logs (do not delete this file)
- checkpoint_timestamp.txt – last retrieved log timestamp (do not delete this this file)
- composer.json and composer.lock – files holding definition of solution's dependencies (not to be modified!)
- vendor – directory that is created after 'composer install' and where all solution's dependencies are.

Configuration Guide

As mentioned in previous chapter, configuration options/parameters for this solution are available in the .env file and is expected from the end customer, to edit this file and provide relevant configuration parameters.

Based on the Mimecast account data and relevant information from your environment, set the corresponding values in the .env file.
Structure of the Configuration File (.ENV)

Mimecast API configuration parameters:
- APP_KEY
- APP_ID
- ACCESS_KEY
- SECRET_KEY
- BASE_URL

These parameters are used to access and authorize against Mimecast API. The parameters and options how to create/access them should be provided by the Mimecast representative.

Humio access configuration parameters:
- HUMIO_BASE_URL – URL to Humio Cloud instance (https://cloud.humio.com)
- HUMIO_API_TOKEN – Bearer Token for Humio Authorization (details on generation of the token are provided in the User Guide section).
- HUMIO_REPO – Humio repository name you created on your Humio account (also explained in the User Guide section).

SIEM files name prefixes (should be left as they are):
= SIEM_DELIVERY_PREFIX, SIEM_PROCESS_PREFIX, SIEM_RECEIPT_PREFIX, SIEM_AV_PREFIX, SIEM_JRNL_PREFIX, SIEM_TTP_URL_PREFIX, SIEM_IMPERSONATION_PREFIX, SIEM_SPAM_EVENT_THREAD_PREFIX, SIEM_TTP_AP_PREFIX, SIEM_EMAIL_PROTECT

These configuration parameters should be used only if Mimecast changes the structure/naming of the SIEM file logs retrievable through corresponding API. For additional information feel free to contact Mimecast support.

Server-side certificates (commented out and should be specified only if these should be used and validated by the middleware)
    
- SERVER_CERT_LOCATION
- SERVER_CERT_PASSWORD

Cron Jobs/Scheduled Tasks Setup

To avoid delays in ingestion of specific log data (e.g. important data from logs that should be ingested sooner than later) the solution provides ability to trigger different logs data ingestion at different time period. For example, the most important data (e.g. from specific log type) can be retrieved on a one minute period, while the less important log data can be retrieved/ingested on a 30 minutes period.

As the data retrieval and ingestion are triggered periodically through a CRON job or a scheduled task, this chapter provides basic information about how this can be achieved.

Depending on the operating system of the server with the deployed solution, CRON jobs (for Linux based servers) or Scheduled Tasks (for Windows based servers) should be configured to trigger data retrieval and ingestion.

For Linux:

https://linuxhint.com/setup_CRON_jobs_linux/

For Windows:

https://docs.microsoft.com/en-us/windows/win32/taskschd/schtasks

php bin/console humio-ingest [typeOfLog] [--retrievalPeriod='numberOfMinutes']

In both cases, scheduled task or the CRON job should execute following command in the CLI. The structure of the command line for triggering the solution (command and related parameters) is the same for both OS. It contains the command for retrieving/ingesting specified Mimecast logs and specified period of time (e.g. logs for the past x minutes).

humio-ingest – command for execution of retrieval and ingestion of logs into Humio
typeOfLog - is optional field and it can contain one or more (space separated) values:
- audit-events
- dlp-logs
- ttp-ip-logs
- ttp-ap-logs
- ttp-url-logs
- threat-intel-logs-malware-grid
- threat-intel-logs-malware-customer
- siem-logs

If the typeOfLog is not specified, all available logs will be retrieved. Each value corresponds to a specific log type. More information about log types is available in the User Guide section of this document.

retrievalPeriod – is optional parameter and specifies time period in minutes for which the logs will be retrieved (last x minutes of log). If not specified, the default value for retrievalPeriod is 5 minutes.

Here is the example for Linux. The following line can be entered in the crontab:

*/5 * * * * php bin/console humio-ingest audit-events dlp-logs ttp-ip-logs ttp-ap-logs ttp-url-logs

The above command retrieves specified log types available on Mimecast server for the last 5 minutes from the CRON job execution time (note that CRON is also configured to be executed on a 5-minute interval).

For example, if it is needed to retrieve audit events and dlp logs on every minute, while other logs should be retrieved on 5 minutes interval, following two lines should be entered to crontab:

*/1 * * * * php bin/console humio-ingest audit-events dlp-logs –retrievalPeriod=1

*/5 * * * * php bin/console humio-ingest ttp-ip-logs ttp-ap-logs ttp-url-logs threat-intel-logs-malware-grid threat-intel-logs-malware-customer siem-logs –retrievalPeriod=5

First line means that on every minute CRON job will trigger retrieval of the audit-events and dlp-logs generated by the Mimecast service for the last minute.

Second line means that on every 5 minutes CRON job will trigger retrieval of ttp-ip-logs, ttp-ap-logs, ttp-url-logs, threat-intel-logs-malware-grid, threat-intel-logs-malware-customer, siem-logs generated by the Mimecast service for the last 5 minutes.

To ensure that some log data are not duplicated or skipped please note that period for CRON triggering and the retrievalPeriod should be the same.

In case that, for any reason, the CRON job or scheduled task is not triggered, or the APIs are not available, etc. the middleware will retrieve all logs of specified type, from the point in time where the last log is retrieved. To achieve this, the middleware is storing the time of the last retrieved log type.

User Guide

To be able to ingest Mimecast log data into Humio, corresponding Humio account and access to Humio are required. If the account and access to Humio is not created, visit https://cloud.humio.com and create the account. The instructions are available here. Note that the URL towards the Humio instance used should be entered in the configuration file.

In Humio, all data ingested are organized in repositories and repository that will be used for ingesting Mimecast log data should be specified in the configuration file. Current solution for Mimecast log data ingestion supports only one Humio repository, as it is assumed that all Mimecast log data will go in the same repository.

As the last configuration detail for the Humio, an API token is required for the authorization. Details for the creation of this token is available at the following link. This token should be entered in the configuration file.

Understanding Logs

Here is the explanation of the typical log types we mentioned in the previous chapters, with relevant links toward the Mimecast documentation.

Log Types

- Audit Events – These logs contain Mimecast audit events with the following details: audit type, event category and detailed information about the event. The log type that is used in console command for ingestion of these logs is audit-events. Read more about these logs on the Mimecast API documentation.
- Data Leak Protection (DLP) Logs – These logs contains information about messages that triggered a DLP or Content Examination policy. The log type that is used in console command for ingestion of these logs is dlp-logs. Read more about these logs on the Mimecast API documentation.
- TTP Attachment Protection Logs - These logs contain Mimecast TTP attachment protection logs with the following details: result of attachment analysis (if it is malicious or not etc.), date when file is released, sender and recipient address, filename and type, action triggered for the attachment, The route of the original email containing the attachment and details. The log type that is used in console command for ingestion of these logs is ttp-ap-logs. Read more about these logs on the Mimecast API documentation.
- TTP Impersonation Protect Logs - These logs contains information about messages containing information flagged by an Impersonation Protection configuration. The log type that is used in console command for ingestion of these logs is ttp-ip-logs. Read more about these logs on the Mimecast API documentation.
- TTP URL Log - These logs contain Mimecast TTP attachment protection logs with the following details: the category of the URL clicked, the email address of the user who clicked the link, the url clicked, the action taken by the user if user awareness was applied, the route of the email that contained the link, the action defined by the administrator for the URL, the date that the URL was clicked, url scan result, the action that was taken for the click, the description of the definition that triggered the URL to be rewritten by Mimecast, The action requested by the user, An array of components of the messge where the URL was found. The log type that is used in console command for ingestion of these logs is ttp-url-logs. Read more about these logs on the Mimecast API documentation.
- Threat Intel Feed - These logs contains information about messages that return identified malware threats at a customer or regional grid level. There are two types of these logs - malware_grid and malware_customer and we ingest them separately. The log type that is used in console command for ingestion of these logs is threat-intel-logs-malware-grid for malware_grid and threat-intel-logs-malware-customer for malware_customer. Read more about these logs on the Mimecast API documentation.
- SIEM logs - These logs contains information about messages that contains MTA logs (MTA = message transfer agent) – all Inbound, outbound and internal messages. There are many subtypes of these logs. We also ingest them separately, but separation happens in the process of mapping data before ingestion. The log type that is used in console command for ingestion of these logs is siem-logs. Read more about these logs on the Mimecast API documentation.


