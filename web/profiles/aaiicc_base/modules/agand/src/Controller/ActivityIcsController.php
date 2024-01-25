<?php

declare(strict_types=1);

namespace Drupal\agand\Controller;

use Drupal\agand\Entity\Bundle\ActivityInterface;
use Drupal\Core\Controller\ControllerBase;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for activity ics download.
 */
class ActivityIcsController extends ControllerBase {

  /**
   * Download ics file for the event.
   */
  public function icsDownload(ActivityInterface $node) {
    $event = (new Event(new UniqueIdentifier($node->uuid())))
      ->setSummary($node->label());

    $startDate = new DateTime($node->getStartDate(), TRUE);
    $endDate = new DateTime($node->getEndDate(), TRUE);
    $ocurrence = new TimeSpan($startDate, $endDate);

    $event->setOccurrence($ocurrence);

    $calendar = new Calendar([$event]);
    $componentFactory = new CalendarFactory();
    $calendarComponent = $componentFactory->createCalendar($calendar);

    $uri = new Uri($node->toUrl(options: ['absolute' => TRUE])->toString());
    $event->setUrl($uri);

    return new Response($calendarComponent, 200, [
      'Content-Type' => 'text/calendar; charset=utf-8',
      'Content-Disposition' => 'attachment; filename="actividad-agenda.ics',
    ]);

  }

}
