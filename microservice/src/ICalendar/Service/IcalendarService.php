<?php

namespace App\ICalendar\Service;

use ICal\ICal;

class IcalendarService
{
    public function processIcalendar(string $icalendarUrl): array
    {
        $icalContents = file_get_contents($icalendarUrl);
        if ($icalContents === false) {
            throw new \Exception('Nie udało się pobrać pliku.');
        }

        $icalendar = new ICal();
        $icalendar->initString($icalContents);

        $eventsData = [];
        foreach ($icalendar->events() as $event) {
            $eventsData[] = [
                'id' => $event->uid,
                'start' => $icalendar->iCalDateToDateTime($event->dtstart)->format('Y-m-d'),
                'end' => $icalendar->iCalDateToDateTime($event->dtend)->format('Y-m-d'),
                'summary' => $event->summary,
            ];
        }

        return $eventsData;
    }
}
