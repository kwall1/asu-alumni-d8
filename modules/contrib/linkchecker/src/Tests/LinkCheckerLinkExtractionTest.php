<?php

namespace Drupal\linkchecker\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Test Link checker module link extraction functionality.
 *
 * @group Link checker
 */
class LinkCheckerLinkExtractionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'linkchecker',
    'path',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $full_html_format = filter_format_load('full_html');
    $permissions = [
      'create page content',
      'edit own page content',
      'administer url aliases',
      'create url aliases',
      filter_permission_name($full_html_format),
    ];

    // User to set up google_analytics.
    $this->admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->admin_user);
  }

  public function testLinkCheckerCreateNodeWithLinks() {

    // Enable all node type page for link extraction.
    variable_set('linkchecker_scan_node_page', TRUE);
    variable_set('linkchecker_scan_blocks', 1);

    // Core enables the URL filter for "Full HTML" by default.
    // -> Blacklist / Disable URL filter for testing.
    variable_set('linkchecker_filter_blacklist', array('filter_url' => 'filter_url'));

    // Extract from all link checker supported HTML tags.
    variable_set('linkchecker_extract_from_a', 1);
    variable_set('linkchecker_extract_from_audio', 1);
    variable_set('linkchecker_extract_from_embed', 1);
    variable_set('linkchecker_extract_from_iframe', 1);
    variable_set('linkchecker_extract_from_img', 1);
    variable_set('linkchecker_extract_from_object', 1);
    variable_set('linkchecker_extract_from_video', 1);

    $body = <<<EOT
<!-- UNSUPPORTED for link checking: -->

<a href="mailto:test@example.com">Send email</a>
<a href="javascript:foo()">Execute JavaScript</a>

<!-- SUPPORTED for link checking: -->

<!-- URL in HTML comment: http://example.com/test-if-url-filter-is-disabled -->

<!-- Relative URLs -->
<img src="test.png" alt="Test image 1" />
<img src="../foo1/test.png" alt="Test image 2" />

<a href="../foo1/bar1">../foo1/bar1</a>
<a href="./foo2/bar2">./foo2/bar2</a>
<a href="../foo3/../foo4/foo5">../foo3/../foo4/foo5</a>
<a href="./foo4/../foo5/foo6">./foo4/../foo5/foo6</a>
<a href="./foo4/./foo5/foo6">./foo4/./foo5/foo6</a>
<a href="./test/foo bar/is_valid-hack.test">./test/foo bar/is_valid-hack.test</a>

<!-- URL with uncommon chars that could potentially fail to extract. See http://drupal.org/node/465462. -->
<a href="http://www.lagrandeepicerie.fr/#e-boutique/Les_produits_du_moment,2/coffret_vins_doux_naturels,149">URL with uncommon chars</a>
<a href="http://example.com/foo bar/is_valid-hack.test">URL with space</a>
<a href="http://example.com/ajax.html#key1=value1&key2=value2">URL with ajax query params</a>
<a href="http://example.com/test.html#test">URL with standard anchor</a>

<!-- object tag: Embed SWF files -->
<object width="150" height="116"
  type="application/x-shockwave-flash"
  data="http://wetterservice.msn.de/phclip.swf?zip=60329&ort=Frankfurt">
    <param name="movie" value="http://wetterservice.msn.de/phclip.swf?zip=60329&ort=Frankfurt" />
    <img src="flash.png" width="150" height="116" alt="" /> <br />
      No weather report visible? At <a href="http://www.msn.de/">MSN</a>
      you are able to find the weather report missing here and the
      Flash plugin can be found at <a href="http://www.adobe.com/">Adobe</a>.
</object>

<!-- object tag: Embed Quicktime Movies on HTML pages -->
<object width="420" height="282"
  classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
  codebase="http://www.apple.com/qtactivex/qtplugin.cab">
  <param name="src" value="http://example.net/video/foo1.mov" />
  <param name="href" value="http://example.net/video/foo2.mov" />
  <param name="controller" value="true" />
  <param name="autoplay" value="false" />
  <param name="scale" value="aspect" />
  <!--[if gte IE 7]> <!-->
  <object type="video/quicktime" data="http://example.net/video/foo3.mov" width="420" height="282">
    <param name="controller" value="true" />
    <param name="autoplay" value="false" />
  </object>
  <!--<![endif]-->
</object>

<!-- object tag: Play MP4 videos on HTML pages -->
<object data="http://example.org/video/foo1.mp4" type="video/mp4" width="420" height="288">
  <param name="src" value="http://example.org/video/foo2.mp4" />
  <param name="autoplay" value="false" />
  <param name="autoStart" value="0" />
  <a href="http://example.org/video/foo3.mp4">/video/foo3.mp4</a>
</object>

<!-- object tag: Play MP4 videos with Quicktime -->
<object width="420" height="282" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
  <param name="src" value="http://example.org/video/foo4.mp4" />
  <param name="href" value="http://example.org/video/foo5.mp4" />
  <param name="controller" value="true" />
  <param name="autoplay" value="false" />
  <param name="scale" value="aspect" />
  <!--[if gte IE 7]> <!-->
  <object type="video/quicktime" data="http://example.org/video/foo6.mp4" width="420" height="282">
    <param name="controller" value="true" />
    <param name="autoplay" value="false" />
  </object>
  <!--<![endif]-->
</object>

<!-- object tag: Play flash videos on HTML pages -->
<object type="application/x-shockwave-flash" data="http://example.org/video/player1.swf" width="420" height="270">
    <param name="movie" value="http://example.org/video/player2.swf" />
    <param src="movie" value="http://example.org/video/player3.swf" />
    <param name="flashvars" value="file=http://example.org/video/foo1.flv&width=420&height=270" />
</object>

<!-- Embed ActiveX control as objekt -->
<object width="267" height="175" classid="CLSID:05589FA1-C356-11CE-BF01-00AA0055595A">
  <param name="filename" value="ritmo.mid">
</object>

<!-- Add inline frames -->
<iframe src="http://example.com/iframe/" name="ExampleIFrame" width="300" height="200">
  <p>Your browser does not support inline frames.</p>
</iframe>

<!-- https://developer.mozilla.org/en/Using_audio_and_video_in_Firefox -->

<!-- http://www.theora.org/cortado/ -->
<video src="my_ogg_video.ogg" controls width="320" height="240">
  <object type="application/x-java-applet" width="320" height="240">
    <param name="archive" value="http://www.theora.org/cortado.jar">
    <param name="code" value="com.fluendo.player.Cortado.class">
    <param name="url" value="my_ogg_video.ogg">
    <p>You need to install Java to play this file.</p>
  </object>
</video>

<video src="video.ogv" controls>
  <object data="flvplayer1.swf" type="application/x-shockwave-flash">
    <param name="movie" value="flvplayer2.swf" />
  </object>
</video>

<video controls>
  <source src="http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.ogg" type="video/ogg">
  <source src="http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.mov">
  Your browser does not support the <code>video</code> element.
</video>

<video controls>
  <source src="foo.ogg" type="video/ogg; codecs=&quot;dirac, speex&quot;">
  Your browser does not support the <code>video</code> element.
</video>

<video src="http://v2v.cc/~j/theora_testsuite/320x240.ogg" controls>
  Your browser does not support the <code>video</code> element.
</video>
EOT;

    // Save folder names in variables for reuse.
    $folder1 = $this->randomName(10);
    $folder2 = $this->randomName(5);

    // Fill node array.
    $langcode = LANGUAGE_NONE;
    $edit = array();
    $edit['title'] = $this->randomName(32);
    $edit["body[$langcode][0][value]"] = $body;
    $edit['path[alias]'] = $folder1 . '/' . $folder2;
    $edit["body[$langcode][0][format]"] = 'full_html';

    // Extract only full qualified URLs.
    variable_set('linkchecker_check_links_types', 1);

    // Verify path input field appears on add "Basic page" form.
    $this->drupalGet('node/add/page');
    // Verify path input is present.
    $this->assertFieldByName('path[alias]', '', 'Path input field present on add Basic page form.');

    // Save node.
    $this->drupalPost('node/add/page', $edit, t('Save'));
    $this->assertText(t('@type @title has been created.', array('@type' => 'Basic page', '@title' => $edit['title'])), 'Node was created.');

    // Verify if the content links are extracted properly.
    $urls_fqdn = array(
      'http://www.lagrandeepicerie.fr/#e-boutique/Les_produits_du_moment,2/coffret_vins_doux_naturels,149',
      'http://wetterservice.msn.de/phclip.swf?zip=60329&ort=Frankfurt',
      'http://www.msn.de/',
      'http://www.adobe.com/',
      'http://www.apple.com/qtactivex/qtplugin.cab',
      'http://example.net/video/foo1.mov',
      'http://example.net/video/foo2.mov',
      'http://example.net/video/foo3.mov',
      'http://example.org/video/foo1.mp4',
      'http://example.org/video/foo2.mp4',
      'http://example.org/video/foo3.mp4',
      'http://example.org/video/foo4.mp4',
      'http://example.org/video/foo5.mp4',
      'http://example.org/video/foo6.mp4',
      'http://example.org/video/player1.swf',
      'http://example.org/video/player2.swf',
      'http://example.org/video/player3.swf',
      'http://example.com/iframe/',
      'http://www.theora.org/cortado.jar',
      'http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.ogg',
      'http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.mov',
      'http://v2v.cc/~j/theora_testsuite/320x240.ogg',
      'http://example.com/foo bar/is_valid-hack.test',
      'http://example.com/ajax.html#key1=value1&key2=value2',
      'http://example.com/test.html#test',
    );

    foreach ($urls_fqdn as $org_url => $check_url) {
      $link = $this->getLinkCheckerLink($check_url);
      if ($link) {
        $this->assertIdentical($link->url, $check_url, format_string('Absolute URL %org_url matches expected result %check_url.', array('%org_url' => $org_url, '%check_url' => $check_url)));
      }
      else {
        $this->fail(format_string('URL %check_url not found.', array('%check_url' => $check_url)));
      }
    }

    // Check if the number of links is correct.
    // - Verifies if all HTML tag regexes matched.
    // - Verifies that the linkchecker filter blacklist works well.
    $urls_in_database = $this->getLinkCheckerLinksCount();
    $urls_expected_count = count($urls_fqdn);
    $this->assertEqual($urls_in_database, $urls_expected_count, format_string('Found @urls_in_database URLs in database matches expected result of @urls_expected_count.', array('@urls_in_database' => $urls_in_database, '@urls_expected_count' => $urls_expected_count)));

    // Extract all URLs including relative path.
    variable_set('clean_url', 1);
    variable_set('linkchecker_check_links_types', 0);

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertTrue($node, 'Node found in database.');
    $this->drupalPost('node/' . $node->nid . '/edit', $edit, t('Save'));
    $this->assertRaw(t('@type %title has been updated.', array('@type' => 'Basic page', '%title' => $edit['title'])));

    // @todo Path alias seems not saved!???
    // $this->assertIdentical($node->path, $edit['path'], format_string('URL alias "@node-path" matches path "@edit-path".', array('@node-path' => $node->path, '@edit-path' => $edit['path'])));

    // Verify if the content links are extracted properly.
    global $base_root, $base_path;
    $urls_relative = array(
      '../foo1/test.png' => $base_root . $base_path . 'foo1/test.png',
      'test.png' => $base_root . $base_path . $folder1 . '/test.png',
      '../foo1/bar1' => $base_root . $base_path . 'foo1/bar1',
      './foo2/bar2' => $base_root . $base_path . $folder1 . '/foo2/bar2',
      '../foo3/../foo4/foo5' => $base_root . $base_path . 'foo4/foo5',
      './foo4/../foo5/foo6' => $base_root . $base_path . $folder1 . '/foo5/foo6',
      './foo4/./foo5/foo6' => $base_root . $base_path . $folder1 . '/foo4/foo5/foo6',
      './test/foo bar/is_valid-hack.test' => $base_root . $base_path . $folder1 . '/test/foo bar/is_valid-hack.test',
      'flash.png' => $base_root . $base_path . $folder1 . '/flash.png',
      'ritmo.mid' => $base_root . $base_path . $folder1 . '/ritmo.mid',
      'my_ogg_video.ogg' => $base_root . $base_path . $folder1 . '/my_ogg_video.ogg',
      'video.ogv' => $base_root . $base_path . $folder1 . '/video.ogv',
      'flvplayer1.swf' => $base_root . $base_path . $folder1 . '/flvplayer1.swf',
      'flvplayer2.swf' => $base_root . $base_path . $folder1 . '/flvplayer2.swf',
      'foo.ogg' => $base_root . $base_path . $folder1 . '/foo.ogg',
    );
    $this->verbose(theme('item_list', array('items' => $urls_relative, 'title' => 'Verify if following relative URLs exists:')));

    $links_debug = array();
    $result = db_query('SELECT url FROM {linkchecker_link}');
    foreach ($result as $row) {
      $links_debug[] = $row->url;
    }
    $this->verbose(theme('item_list', array('items' => $links_debug, 'title' => 'Following URLs exists:')));

    foreach ($urls_relative as $org_url => $check_url) {
      $link = $this->getLinkCheckerLink($check_url);
      if ($link) {
        $this->assertIdentical($link->url, $check_url, format_string('Relative URL %org_url matches expected result %check_url.', array('%org_url' => $org_url, '%check_url' => $check_url)));
      }
      else {
        $this->fail(format_string('URL %check_url not found.', array('%check_url' => $check_url)));
      }
    }

    // Check if the number of links is correct.
    $urls_in_database = $this->getLinkCheckerLinksCount();
    $urls_expected_count = count($urls_fqdn + $urls_relative);
    $this->assertEqual($urls_in_database, $urls_expected_count, format_string('Found @urls_in_database URLs in database matches expected result of @urls_expected_count.', array('@urls_in_database' => $urls_in_database, '@urls_expected_count' => $urls_expected_count)));

    // Verify if link check has been enabled for normal URLs.
    $urls = array(
      'http://www.lagrandeepicerie.fr/#e-boutique/Les_produits_du_moment,2/coffret_vins_doux_naturels,149',
      'http://wetterservice.msn.de/phclip.swf?zip=60329&ort=Frankfurt',
      'http://www.msn.de/',
      'http://www.adobe.com/',
      'http://www.apple.com/qtactivex/qtplugin.cab',
      'http://www.theora.org/cortado.jar',
      'http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.ogg',
      'http://v2v.cc/~j/theora_testsuite/pixel_aspect_ratio.mov',
      'http://v2v.cc/~j/theora_testsuite/320x240.ogg',
      $base_root . $base_path . 'foo1/test.png',
      $base_root . $base_path . $folder1 . '/test.png',
      $base_root . $base_path . 'foo1/bar1',
      $base_root . $base_path . $folder1 . '/foo2/bar2',
      $base_root . $base_path . 'foo4/foo5',
      $base_root . $base_path . $folder1 . '/foo5/foo6',
      $base_root . $base_path . $folder1 . '/foo4/foo5/foo6',
      $base_root . $base_path . $folder1 . '/test/foo bar/is_valid-hack.test',
      $base_root . $base_path . $folder1 . '/flash.png',
      $base_root . $base_path . $folder1 . '/ritmo.mid',
      $base_root . $base_path . $folder1 . '/my_ogg_video.ogg',
      $base_root . $base_path . $folder1 . '/video.ogv',
      $base_root . $base_path . $folder1 . '/flvplayer1.swf',
      $base_root . $base_path . $folder1 . '/flvplayer2.swf',
      $base_root . $base_path . $folder1 . '/foo.ogg',
    );

    foreach ($urls as $url) {
      $this->assertTrue($this->getLinkcheckerLink($url)->status, format_string('Link check for %url is enabled.', array('%url' => $url)));
    }

    // Verify if link check has been disabled for example.com/net/org URLs.
    $documentation_urls = array(
      'http://example.net/video/foo1.mov',
      'http://example.net/video/foo2.mov',
      'http://example.net/video/foo3.mov',
      'http://example.org/video/foo1.mp4',
      'http://example.org/video/foo2.mp4',
      'http://example.org/video/foo3.mp4',
      'http://example.org/video/foo4.mp4',
      'http://example.org/video/foo5.mp4',
      'http://example.org/video/foo6.mp4',
      'http://example.org/video/player1.swf',
      'http://example.org/video/player2.swf',
      'http://example.org/video/player3.swf',
      'http://example.com/iframe/',
      'http://example.com/foo bar/is_valid-hack.test',
      'http://example.com/ajax.html#key1=value1&key2=value2',
      'http://example.com/test.html#test',
    );

    foreach ($documentation_urls as $documentation_url) {
      $this->assertFalse($this->getLinkcheckerLink($documentation_url)->status, format_string('Link check for %url is disabled.', array('%url' => $documentation_url)));
    }

  }

  /**
   * Get linkchecker link by url.
   *
   * @param string $url
   *   URL of the link to find.
   *
   * @return object
   *   The link object.
   */
  function getLinkCheckerLink($url) {
    return db_query('SELECT * FROM {linkchecker_link} WHERE urlhash = :urlhash', array(':urlhash' => drupal_hash_base64($url)))->fetchObject();
  }

  /**
   * Get the current number of links in linkchecker_links table.
   */
  function getLinkCheckerLinksCount() {
    return db_query('SELECT COUNT(1) FROM {linkchecker_link}')->fetchField();
  }
}
