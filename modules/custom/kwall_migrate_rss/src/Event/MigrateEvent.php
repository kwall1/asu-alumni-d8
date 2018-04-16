<?php
/**
 * @file
 * Contains \Drupal\migrate\Event\MigrateMapDeleteEvent.
 */


namespace Drupal\kwall_migrate_rss\Event;

use Drupal\Component\Utility\UrlHelper;
use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrateEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[MigrateEvents::PREPARE_ROW][] = array('onPrepareRow', 0);
    return $events;
  }

  /**
   * React to a new row.
   *
   * @param \Drupal\migrate_plus\Event\MigratePrepareRowEvent $event
   *   The prepare-row event.
   */
  public function onPrepareRow(MigratePrepareRowEvent $event) {
    $row = $event->getRow();
    // Add any new rows or alter them
/*
    if(null !==  $row->getSourceProperty('start_date')) {
      $row->setSourceProperty('start_date','2018-03-16T00:30:41.0000000Z');
    }
    if(null !== $row->getSourceProperty('end_date')) {
      $row->setSourceProperty('end_date','2018-03-16T00:30:41.0000000Z');
    }
*/
    // $row->setSourceProperty('first_last', $row->getSourceProperty('first_name') . ' ' . $row->getSourceProperty('last_name'));
    $title_replace_head = '/(.*)M UC Riverside /';
    $title_replace_head_alt = '/(.*)] UC Riverside /';
    $title_replace_tail = '/ vs (.*)/';
    $row->setSourceProperty('title', preg_replace($title_replace_head, '', $row->getSourceProperty('title')));
    $row->setSourceProperty('title', preg_replace($title_replace_head_alt, '', $row->getSourceProperty('title')));
    $row->setSourceProperty('title', preg_replace($title_replace_tail, '', $row->getSourceProperty('title')));
    //drush_print('start date source' . $row->getSourceProperty('start_date'));
    //start_date substr("2018-03-16T00:30:41.0000000Z", 0, -9)
    $row->setSourceProperty('start_date', strtotime(substr($row->getSourceProperty('start_date'), 0, -9)));
    //drush_print('start date translated' . $row->getSourceProperty('start_date'));
  //drush_print('end date source' . $row->getSourceProperty('end_date'));
    //end_date
    $row->setSourceProperty('end_date', strtotime(substr($row->getSourceProperty('end_date'), 0, -9)));
    //drush_print('end date translated' . $row->getSourceProperty('end_date'));
  }

  /**
   * Run once done creating node
   * @todo find hook to actually do these redirects
   */

  public function complete($entity, $row) {
  }


}