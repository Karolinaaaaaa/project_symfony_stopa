<?php

namespace App\ICalendar\Command;

use Psr\Log\LoggerInterface;
use App\ICalendar\Service\IcalendarService;
use App\ICalendar\Service\S3ClientService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IcalendarCommand extends Command
{
    protected static $defaultName = 'app:parse-ical';
    private $icalendarService;
    private $s3ClientService;
    private $logger;

    public function __construct(IcalendarService $icalendarService, LoggerInterface $logger, S3ClientService $s3ClientService)
    {
        parent::__construct();
        $this->icalendarService = $icalendarService;
        $this->s3ClientService = $s3ClientService;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Analiza pliku iCalendar i generowanie zdarzenia.')
            ->addArgument('url', InputArgument::REQUIRED, 'Adres URL pliku iCalendar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        try {
            $events = $this->icalendarService->processIcalendar($url);
            $output->writeln(json_encode($events, JSON_PRETTY_PRINT));
            $this->logger->info('Plik Icalendar został pomyślnie przetworzony.');

            $content = json_encode($events);

            $fileName = 'results-' . time() . '.json';
            $bucketName = $_ENV['S3_BUCKET_NAME'];
            $s3Url = $this->s3ClientService->uploadData($bucketName, $fileName, $content);


            if ($s3Url) {
                $output->writeln("Wyniki zostały zapisane w S3 pod adresem: $s3Url");
                $this->logger->info('Wyniki zostały zapisane w S3.', ['url' => $s3Url]);
            } else {
                $this->logger->error('Nie udało się zapisać wyników do S3.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Nie udało się przetworzyć pliku Icalendar.', ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
