<?php

namespace App\ICalendar\Controller;

use Psr\Log\LoggerInterface;
use App\ICalendar\Service\IcalendarService;
use App\ICalendar\Service\S3ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class IcalendarController extends AbstractController
{
    private $icalendarService;
    private $s3ClientService;
    private $logger;

    public function __construct(IcalendarService $icalendarService, LoggerInterface $logger, S3ClientService $s3ClientService)
    {
        $this->icalendarService = $icalendarService;
        $this->s3ClientService = $s3ClientService;
        $this->logger = $logger;
    }

    #[Route('/api/icalendar', name: 'api_fetch_icalendar', methods: ['GET'])]
    public function fetchIcalendar(Request $request): JsonResponse
    {
        $url = $request->query->get('url');
        if (!$url) {
            return $this->json(['error' => 'Brak parametru adresu URL.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $events = $this->icalendarService->processIcalendar($url);
            $this->logger->info('Icalendar file parsed successfully.');

            $content = json_encode($events);

            $fileName = 'results-' . time() . '.json';
            $bucketName = $_ENV['S3_BUCKET_NAME'];
            $s3Url = $this->s3ClientService->uploadData($bucketName, $fileName, $content);

            if ($s3Url) {
                $this->logger->info('Wyniki zostały zapisane w S3.', ['url' => $s3Url]);
                return $this->json(['success' => true, 's3Url' => $s3Url, 'data' => $events]);
            } else {
                return $this->json(['error' => 'Nie udało się zapisać wyników do S3.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            $this->logger->error('Nie udało się przeanalizować pliku Icalendar.', ['exception' => $e]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
