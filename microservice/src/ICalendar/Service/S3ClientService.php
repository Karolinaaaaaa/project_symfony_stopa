<?php

namespace App\ICalendar\Service;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class S3ClientService
{
    private $s3Client;
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $_ENV['AWS_REGION'],
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
        $this->logger = $logger;
    }

    public function uploadData($bucketName, $keyName, $content)
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $keyName,
                'Body' => $content
            ]);

            return $result->get('ObjectURL');
        } catch (\Exception $e) {
            $this->logger->error("Nie udało się przesłać danych do S3: {$e->getMessage()}");
            return null;
        }
    }
}
