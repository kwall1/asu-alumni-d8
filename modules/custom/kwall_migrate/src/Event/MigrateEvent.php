<?php
/**
 * @file
 * Contains \Drupal\migrate\Event\MigrateMapDeleteEvent.
 */


namespace Drupal\kwall_migrate\Event;

use Drupal\redirect\Entity\Redirect;
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
    //$events[MigrateEvents::POST_ROW_SAVE][] = array('onPostRowSave',0);
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
    // $row->setSourceProperty('first_last', $row->getSourceProperty('first_name') . ' ' . $row->getSourceProperty('last_name'));
  }

  /**
   * Run once done creating node
   * @todo find hook to actually do these redirects
   */
  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    // Migration object being imported.
    $migration = $event->getMigration();
    // Row object containing the specific item just imported.
    $row = $event->getRow();
    // The unique destination ID, as an array (accomodating multi-column keys), of the item just imported.
    $destination_id_values = $event->getDestinationIdValues();
    // add redirect for old page URL TODO: need entity nid or nid.
    if (!empty($row->getSourceProperty('url')) && !empty($entity->nid)) {
      $url = parse_url($row->getSourceProperty('url'));
      $path = trim($url['path'], '/');
      if (!empty($url['query'])) {
        $path .= '?' . $url['query'];
      }
      if (!empty($url['fragment'])) {
        $path .= '#' . $url['fragment'];
      }
      $this->add_redirect($path, 'node/' . $entity->nid);
    }



  }


  public function complete($entity, $row) {
    // add redirect for old page URL
    if (!empty($row->getSourceProperty('url')) && !empty($entity->nid)) {
      $url = parse_url($row->getSourceProperty('url'));
      $path = trim($url['path'], '/');
      if (!empty($url['query'])) {
        $path .= '?' . $url['query'];
      }
      if (!empty($url['fragment'])) {
        $path .= '#' . $url['fragment'];
      }
      $this->add_redirect($path, 'node/' . $entity->nid);
    }

    // add redirect for any aliases
/*
    if (!empty($row->alias) && !empty($entity->nid)) {
      $path = trim($row->alias, '/');
      $this->add_redirect($path, 'node/' . $entity->nid);
    }
*/
  }


  /**
   * Add a redirect
   * Assumes source is relative
   * @todo fix source url to work relative or absolute without hardcoding
   */
  private function add_redirect($source_url, $destination_url) {
    $redirect = array();

    // parse source
    $source = UrlHelper::parse(trim($source_url));
    $redirect['source'] = $source['url'];
    if (!empty($source['query'])) {
      $redirect['source_options']['query'] = $source['query'];
    }

    // check if exists
    if (empty($redirect['source_options']['query'])) {
      $existing = redirect_repository()->findMatchingRedirect($redirect['source']);
      if (!empty($existing)) {
        return FALSE;
      }
    }
    else {
      $existing = redirect_repository()->findMatchingRedirect($redirect['source'], $redirect['source_options']['query']);
      if (!empty($existing)) {
        return FALSE;
      }
    }

    // parse destination
    $destination = UrlHelper::parse(trim($destination_url));
    $redirect['redirect'] = $destination['url'];
    if (isset($destination['query'])) {
      $redirect['redirect_options']['query'] = $destination['query'];
    }
    if (isset($destination['fragment'])) {
      $redirect['redirect_options']['fragment'] = $destination['fragment'];
    }

    // save redirect
    $redirect_object = Redirect::create();
    $redirect_object->setSource($original_path['alias']);
    $redirect_object->setRedirect($path['source']);
    $redirect_object->setLanguage($original_path['langcode']);
    $redirect_object->setStatusCode(\Drupal::config('redirect.settings')->get('default_status_code'));
    $redirect_object->save();

    return $redirect_object;
  }
}
