<?php

namespace App\Tests\ICalendar\Service;

use App\ICalendar\Service\IcalendarService;
use PHPUnit\Framework\TestCase;

class IcalendarServiceTest extends TestCase
{
    public function testProcessIcalendar()
    {
        $icalendarUrl = 'https://slowhop.com/icalendar-export/api-v1/21c0ed902d012461d28605cdb2a8b7a2.ics';

        $icalendarService = new IcalendarService();
        $eventsData = $icalendarService->processIcalendar($icalendarUrl);

        $this->assertIsArray($eventsData);
        $this->assertArrayHasKey('id', $eventsData[0]);
        $this->assertArrayHasKey('start', $eventsData[0]);
        $this->assertArrayHasKey('end', $eventsData[0]);
        $this->assertArrayHasKey('summary', $eventsData[0]);
    }
}
