<?php
/**
 * @file
 * Provides Drupal\video_filter\VideoFilterInterface
 */

namespace Drupal\video_filter;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for ice cream flavor plugins.
 */
interface VideoFilterInterface extends PluginInspectionInterface {

  /**
   * Return the name of the Video Filter codec.
   *
   * @return string
   */
  public function getName();

  /**
   * Return codec sample URL.
   *
   * @return url
   */
  public function getExampleURL();

  /**
   * Return an array of regular expressions for the codec.
   *
   * @return array regexp
   */
  public function getRegexp();

  /**
   * Return video player ratio.
   *
   * @return string
   */
  public function getRatio();

  /**
   * Return video player control bar height.
   *
   * @return int
   */
  public function getControlBarHeight();

  /**
   * Return Video Filter coded usage instructions.
   *
   * @return string
   */
  public function instructions();

  /**
   * Return video HTML5 video (iframe).
   *
   * @return url
   */
  public function iframe($video);

  /**
   * Return Flash video (flv).
   *
   * @return url
   */
  public function flash($video);

  /**
   * Return HTML code of the video player.
   *
   * @return url
   */
  public function html($video);

  /**
   * Return embed options (Form API elements).
   *
   * @return array (Drupal Form API)
   */
  public function options();

  /**
   * Returns absolute URL to preview image.
   *
   * @return url
   */
  public function preview($video);

}
