<?php

namespace Drupal\kwall_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides a 'Example: uppercase this please' block.
 *
 * @Block(
 *   id = "kwall_custom_addtocalendar",
 *   admin_label = @Translation("Add to calendar")
 * )
 */
class AddEventToCalendarBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $service = \Drupal::service('addtocalendar.apiwidget');

      // Setup variables.
      $config_values = [
        'atcStyle' => '',
        'atcDisplayText' => $this->t('Add to Calendar'),
        'atcTitle' => $node->getTitle(),
        'atcDescription' => trim($node->get('body')->value),
        'atcLocation' => $node->get('field_event_location')->getString(),
        'atcDateStart' => $node->get('field_start_event_date')->getString(),
        'atcDateEnd' => $node->get('field_end_event_date')->getString(),
        'atcPrivacy' => 'public',
        'atcDataSecure' => 'auto',
        'atcDataCalendars' => [
          'iCalendar' => 'iCalendar',
          'Google Calendar' => 'Google Calendar',
          // 'Outlook' => 'Outlook',
          // 'Outlook Online' => 'Outlook Online',
          // 'Yahoo! Calendar' => 'Yahoo! Calendar',
        ],
      ];

      // Load Contact Information.
      $contacts = $node->get('field_information_contact')->getValue();
      $config_values['atcOrganizer'] = '';
      $config_values['atcOrganizerEmail'] = '';
      if (!empty($contacts)) {
        $contacts = Paragraph::load($contacts[0]['target_id']);
        $config_values['atcOrganizer'] = $contacts->get('field_leadership_name')
          ->getString();
        $config_values['atcOrganizerEmail'] = $contacts->get('field_leadership_email')
          ->getString();
      }

      $service->setWidgetValues($config_values);
      $widget = $service->generateWidget();
      $markup = render($widget);

      $build = [
        '#type' => 'markup',
        '#markup' => $markup,
        '#cache' => [
          'contexts' => ['url', 'languages'],
        ],
      ];
    }

    return $build;

  }

}
