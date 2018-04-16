<?php
/**
 * @file
 * Contains \Drupal\video_filter\Plugin\VideoFilter\MailRu.
 */

namespace Drupal\video_filter\Plugin\VideoFilter;

use Drupal\video_filter\VideoFilterBase;

/**
 * Provides MailRu codec for Video Filter
 *
 * @VideoFilter(
 *   id = "mailru",
 *   name = @Translation("MailRu"),
 *   example_url = "https://my.mail.ru/v/semenikhin_denis/video/_groupvideo/[video-id].html",
 *   regexp = {
 *     "/my\.mail\.ru\/v\/(.*)\/([0-9]+)\.html/i",
 *   },
 *   ratio = "16/9",
 * )
 */
class MailRu extends VideoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function iframe($video) {
    $attributes = [
      'autoplay' => !empty($video['autoplay']) ? 'autoplay=' . (int) $video['autoplay'] : '',
    ];
    return [
      'src' => 'https://videoapi.my.mail.ru/videos/embed/v/' . $video['codec']['matches'][1] . '/' . $video['codec']['matches'][2] . '.html?' . implode('&', $attributes),
      'properties' => [
        'allowfullscreen' => 'true',
        'webkitallowfullscreen' => 'true',
        'mozallowfullscreen' => 'true',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function options() {
    $form = parent::options();
    $form['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
    ];
    return $form;
  }

}
