<?php
/**
 * @file
 * Contains \Drupal\kwall_migrate\Plugin\migrate\source\ExternalCSV.
 */

namespace Drupal\kwall_migrate\Plugin\migrate\source;

use Drupal\Component\Utility\UrlHelper;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\kwall_migrate\CSVFileObject;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Row;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\Html;
use Drupal\redirect\Entity\Redirect;


/**
 * Source for External CSV.
 *
 * If the CSV file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "externalcsv"
 * )
 */
class ExternalCSV extends SourcePluginBase {

  /**
   * List of available source fields.
   *
   * Keys are the field machine names as used in field mappings, values are
   * descriptions.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * List of key fields, as indexes.
   *
   * @var array
   */
  protected $keys = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    // redefine the path for Site Factory
    $this->configuration['path'] = DRUPAL_ROOT . '/' . drupal_get_path('module', 'kwall_migrate') . '/cdu_migrate.csv';
    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new MigrateException('You must declare the "path" to the source CSV file in your source settings.');
    }

    // Key field(s) are required.
    if (empty($this->configuration['keys'])) {
      throw new MigrateException('You must declare "keys" as a unique array of fields in your source settings.');
    }

  }

  /**
   * Return a string representing the source query.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    // File handler using header-rows-respecting extension of SPLFileObject.
    $file = new CSVFileObject($this->configuration['path']);

    // Set basics of CSV behavior based on configuration.
    $delimiter = !empty($this->configuration['delimiter']) ? $this->configuration['delimiter'] : ',';
    $enclosure = !empty($this->configuration['enclosure']) ? $this->configuration['enclosure'] : '"';
    $escape = !empty($this->configuration['escape']) ? $this->configuration['escape'] : '\\';
    $file->setCsvControl($delimiter, $enclosure, $escape);

    // Figure out what CSV column(s) to use. Use either the header row(s) or
    // explicitly provided column name(s).
    if (!empty($this->configuration['header_row_count'])) {
      $file->setHeaderRowCount($this->configuration['header_row_count']);

      // Find the last header line.
      $file->rewind();
      $file->seek($file->getHeaderRowCount() - 1);

      $row = $file->current();
      foreach ($row as $header) {
        $header = trim($header);
        $column_names[] = [$header => $header];
      }
      $file->setColumnNames($column_names);
    }
    // An explicit list of column name(s) will override any header row(s).
    if (!empty($this->configuration['column_names'])) {
      $file->setColumnNames($this->configuration['column_names']);
    }

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    $ids = [];
    foreach ($this->configuration['keys'] as $key) {
      $ids[$key]['type'] = 'string';
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    foreach ($this->getIterator()->getColumnNames() as $column) {
      $fields[key($column)] = reset($column);
    }

    // Any caller-specified fields with the same names as extracted fields will
    // override them; any others will be added.
    if (!empty($this->configuration['fields'])) {
      $fields = $this->configuration['fields'] + $fields;
    }

    return $fields;
  }

  /**
   * Adds additional data to the row.
   *
   * @param \Drupal\Migrate\Row $row
   *   The row object.
   *
   * @return bool
   *   FALSE if this row needs to be skipped.
   */
  public function prepareRow(Row $row) {
    if (parent::prepareRow($row) === FALSE) {
      drush_print('parent stopped');
      return FALSE;
    }

    // load existing node if present
    // @todo fix this to pull in node somehow? could be in the map I see it as such
    //  $destid = $this->getMap()->lookupDestinationID(array('id' => $row->id));
/*
     $node = new stdClass();
     if (!empty($destid['destid1'])) {
       $node = node_load($destid['destid1']);
     }
     if(!empty($node)) {
      drush_print(print_r($node,TRUE));
     }
*/
    // prevent saving if checked that it is cleaned
    // if (!empty($node->field_cleaned['und'][0]['value'])) {
    //   return FALSE;
    // }

/*
    // keep flagged if flagged
    $row->flag = 0;
    if (!empty($node->field_flag['und'][0]['value'])) {
      $row->flag = 1;
    }

    // keep existing issues
    $row->issues = array();
    if (!empty($node->field_issues['und'])) {
      foreach ($node->field_issues['und'] as $issue) {
        $row->issues[] = $issue['value'];
      }
    }
*/

      $page_title = '';

    // fetch content
    if ( ( empty( $row->getSourceProperty('content') ) ) && !empty( $row->getSourceProperty('url') ) ) {
      $notes = array();
      // Check the  configuration for detail or settings
      // if URL came from database, we need to adjust it I have the URL's in a file in KWALL Custom
      $row_url = $row->getSourceProperty('url');
      $client = \Drupal::httpClient();
      try {
        $result = $client->get($row_url, array('http_errors' => FALSE));
        $bodycopy = $result->getBody()->getContents();
        drush_print('status: ' . $result->getStatusCode() . ' url: ' . $row_url);
      }
      catch (Exception $e) {
        drush_print('request exception' . $e);
        $skip_scrape = 1;
        //return FALSE;
      }

      if (!empty($bodycopy) && $result->getStatusCode() == 200) {

        // pull in only body content
        $doc = new \DOMDocument('1.0', 'utf-8');
        libxml_use_internal_errors(true);
        $doc->preserveWhiteSpace = FALSE;
        $doc->strictErrorChecking = FALSE;
        $doc->substituteEntities = FALSE;
        $doc->loadHTML($bodycopy);
        $xpath = new \DOMXpath($doc);


        // pull out content KWALL Set this to the right thing.
        $content = $xpath->query("//div[@class='right-column']");
        $entry = $content->item(0);
        if ( !empty( $entry ) ) {
          $row->setSourceProperty('content',(string) $entry->ownerDocument->saveHTML($entry));
          $row->setSourceProperty('content', preg_replace('/(<h1 id="ctl00_ctl00_ContentArea_PageHeading".*<\/h1>)/s', '', $row->getSourceProperty('content'),1));
        }

        $page_title = $xpath->query("//h1[@id='ctl00_ctl00_ContentArea_PageHeading']");
        $page_title = $page_title->item(0);
        if ( !empty( $page_title ) ) {
          $row->setSourceProperty('title',(string) $page_title->ownerDocument->saveHTML($page_title));
          $row->setSourceProperty('title', str_replace('<h1 id="ctl00_ctl00_ContentArea_PageHeading">', '', $row->getSourceProperty('title') ) );
          $row->setSourceProperty('title', str_replace('</h1>', '', $row->getSourceProperty('title') ) );
        }


// TODO - figure out slide saving or put in images, file is killing process though.
/*



        $top_image = $xpath->query("//div[@id='supergraphic']");
        $header_image = $top_image->item(0);
        if(empty($header_image)) {
          //look for container
          $top_image = $xpath->query("//div[@id='supergraphic_container']");
          $header_image = $top_image->item(0);

        }
        if (!empty($header_image)) {
          $header_img = (string) $header_image->ownerDocument->saveHTML($header_image);
          $images = array();
          if(strstr($header_img, 'img')) {
            //$images = $xpath->query("//img");
            preg_match('/(src=["\'](.*?)["\'])/', $header_img, $match);  //find src="X" or src='X'
            $split = preg_split('/["\']/', $match[0]); // split by quotes
            $images[] = $split[1]; // X between quotes
            $slides = array();
            foreach ($images as $image) {
              //$src = $image->getAttribute('src');
              $file = $this->save_file($image, 'public://documents/imported', $row_url);

              if ($file !== FALSE) {
                $uri = $file->getFileUri();
                // replace link to file
                $url = file_create_url($uri);
                $href = parse_url($url);
                $slides[] = [
                  'target_id' => $file->id(),
                  'alt' => $href['path'],
                  'title' => $href['path'],
                  'width' => 795,
                  'height' => 245,
                ];
                //per mike ryan try fid
                $row->setSourceProperty('legacy_image', $file->id());
              }
            }
          }
          $row->setSourceProperty('legacy_image', $slides);
          $row->setSourceProperty('slides', $slides);
          //drush_print(print_r($row->getSourceProperty('legacy_image'),TRUE));
        }
*/

//END file / slide
/*
        if (empty($entry)) {
          //get new design
        $content = $xpath->query("//div[@class='content-view-full']");
        $entry = $content->item(0);
          drush_print('empty entry attempting new theme');

        }
*/
      }
      if (empty($row->getSourceProperty('content'))) {
        drush_print('empty content');
        $skip_scrape = 1;
        //return FALSE;
      }
    }

    if ( empty( $row->getSourceProperty('title') ) ) {
      if ( !empty( $page_title ) ) {
        drush_print('there is a title');
        $row->setSourceProperty('title', $page_title);
      } else {
        drush_print('no title');
        return FALSE;
      }
    }

    // look for empty content
    $row->setSourceProperty('content', trim($row->getSourceProperty('content')));
    if (empty($row->getSourceProperty('content'))) {
      // the rest is content cleanup so stop
      drush_print('trimmed content empty');
      $notes['note'][] = 'Empty Content';
      $notes['flag'] = 1;
    }

/*
    //remove attribute headers, title is this
    $row->setSourceProperty('content', preg_replace('/(<div class="attribute-header".*<\/div>)/s', '', $row->getSourceProperty('content'), -1, $count));
    if ($count > 0) {
      $notes['note'][] = 'title';
      $notes['flag'] = 1;
    }
*/
    // clean up includes
    $count = 0;
    $row->setSourceProperty('content', preg_replace('/(<%.+?%>)/s', '', $row->getSourceProperty('content'), -1, $count));
    if ($count > 0) {
      $notes['note'][] = 'ASP Includes';
      $notes['flag'] = 1;
    }

    $count = 0;
    $row->setSourceProperty('content', preg_replace('/(<!--#include .+? -->)/s', '', $row->getSourceProperty('content'), -1, $count));
    if ($count > 0) {
      $notes['note'][] = 'Server Includes';
      $notes['flag'] = 1;
    }

    // clean up scripts
    $count = 0;
    $row->setSourceProperty('content', preg_replace('/(<script.*<\/script>)/s', '', $row->getSourceProperty('content'), -1, $count));
    if ($count > 0) {
    // $row->issues[] = t('Scripts');
    }

    // look for forms
    $count = preg_match_all('/<form(.*)<\/form>/s', $row->getSourceProperty('content'));
    if ($count > 0) {
     $notes['note'][] = 'Forms';
      $notes['flag'] = 1;
    }

    // look for iframes
    $count = preg_match_all('/<iframe(.*)>/s', $row->getSourceProperty('content'));
    if ($count > 0) {
      $notes['note'][] = 'iFrames';
      $notes['flag'] = 1;
    }

    // look for flash
    $count = preg_match_all('/<object(.*)>/s', $row->getSourceProperty('content'));
    if ($count > 0) {
      $notes['note'][] = 'Flash';
      $notes['flag'] = 1;
    }

    // clean up microsoft word formatting and validate / tidy HTML
    $config = array(
      'indent' => FALSE,
      'output-xhtml' => TRUE,
      'wrap' => 2000,
      'clean' => TRUE,
      'bare' => TRUE,
      'hide-comments' => TRUE,
      'word-2000' => TRUE,
      'show-warnings' => FALSE,
      'char-encoding' => 'utf8',
      'input-encoding' => 'utf8',
      'output-encoding' => 'utf8',
      'ascii-chars' => FALSE,
    );
    $tidy = tidy_parse_string($row->getSourceProperty('content'), $config, 'UTF8');
    $tidy->cleanRepair();
    $tidy->diagnose();
    if (tidy_error_count($tidy) > 0) {
      $errors = explode("\n", $tidy->errorBuffer);

      foreach ($errors as $error) {
        $notes['note'][] = 'Random Error';
        $notes['flag'] = 1;
      }
    }

    $html = $tidy->html();
    $html = mb_convert_encoding($html->value, 'HTML-ENTITIES', 'UTF-8');

    // load into xpath
    $doc = new \DOMDocument('1.0', 'utf-8');
    libxml_use_internal_errors(true);
    $doc->preserveWhiteSpace = FALSE;
    $doc->strictErrorChecking = FALSE;
    $doc->substituteEntities = FALSE;
    $doc->loadHTML($html);
    $xpath = new \DOMXpath($doc);
    // KW - Skipping links and file processing since I got the downloads from KWALL

    // parse all links
    $links = $xpath->query('//a');
    foreach ($links as $link) {
      $href = $link->getAttribute('href');
      $file = $this->save_file($href, 'public://documents/imported', $row_url);
      if ($file !== FALSE) {
        $notes['files'][] = $file->id();
        $uri = $file->getFileUri();
        // replace link to file
        $url = file_create_url($uri);
        $href = parse_url($url);
        $path = trim($href['path'], '/');
        $link->setAttribute('href', '/' . $path);
      }
      else {
        // save file failed, probable bad link
        $notes['note'][] = 'Possible Bad Link: ' . $path;
        $notes['flag'] = 1;
      }
    }

    // clean up images, add redirects
    $images = $xpath->query("//img");
    foreach ($images as $image) {
      $src = $image->getAttribute('src');
      $file = $this->save_file($src, 'public://images/imported', $row_url);
      if ($file !== FALSE) {
        $notes['images'][] = $file->id();
        $uri = $file->getFileUri();
        // replace link to image
        $url = file_create_url($uri);
        $href = parse_url($url);
        $path = trim($href['path'], '/');
        $image->setAttribute('src', '/' . $path);
      }
      else {
        // save file failed, probable bad link
        $notes['note'][] = 'Possible Bad Link: ' . $path;
        $notes['flag'] = 1;
      }
    }


    // get only the body output
    $html = '';
    $children = $doc->getElementsByTagName('body')->item(0)->childNodes;
    foreach ($children as $child) {
      $html .= $doc->saveHTML($child);
    }
     //override with XML data TMP
    if(empty($html) && !empty($xml_body)) {
      $html = $xml_body;
    }
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

    $row->setSourceProperty('legacy_content', $html);
    $row->setSourceProperty('legacy_sidebar', $html);
    $row->setSourceProperty('content', $html);
    $row->setSourceProperty('body_value', $html);

    $row->setSourceProperty('body_format', 'rich_text' );
    $row->setSourceProperty('status', 1 );
    $row->setSourceProperty('uid', 1 );

    // remove duplicate and not good issues
    $skip = array(
      'Info: Document content looks like HTML Proprietary',
      'Info: Document content looks like XHTML 1.0 Transitional',
    );

    if(empty($row->getSourceProperty('content'))) {
      drush_print('no content to add, blank body');
      return FALSE;
    }
    // set flag if we ran into issues

    if ($notes['flag'] == 1) {
      $row->setSourceProperty('flag', 1);
    }

    if (!empty($notes['note'])) {
         $row->setSourceProperty('issues', $notes['note']);

      foreach ($notes['note'] as $delta => $issue) {
        if (strpos($issue, 'st1:') !== FALSE) {
          unset($notes['note'][$delta]);
        }
        if (strpos($issue, 'Not all warnings/errors were shown.') !== FALSE) {
          unset($notes['note'][$delta]);
        }
        if (in_array($issue, $skip)) {
          unset($notes['note'][$delta]);
        }
      }
    }

  }

  /**
   * Fetch a file and save to files directory in given directory
   * Stores in the file table as permanent so we can see later
   * Also adds a redirect
   */
  private function save_file($source, $directory, $site_url) {
    $url = parse_url($source);

    // skip non http links (mailto, javascript, etc)
    if (empty($url['scheme'])) {
      $url['scheme'] = 'http';
    }
    if (!in_array($url['scheme'], array('http', 'https'))) {
      return FALSE;
    }

    // skip empty paths
    if (empty($url['path'])) {
      return FALSE;
    }

    // make relative paths absolute
    if (!isset($url['host'])) {
      $host = parse_url($site_url);
      $url['host'] = $host['host'];
    }
    elseif ($url['host'] != $site_url && $url['host'] != 'www.' . $site_url) {
      // skip external links
      return FALSE;
    }

    // replace link if it goes to an existing redirect
    $existing_redirect = redirect_repository()->findMatchingRedirect($url['path']);
    if (!empty($existing_redirect)) {
      $url['path'] = trim($existing_redirect->getSource(), '/');
      drush_print('existing redirect: ' . $url['path']);
    }

    // if already local, load the file
    $path = trim($url['path'], '/');
    $path = str_replace('sites/default/files/', '', $path);
    $uri = file_build_uri($path);
    $existing_files = entity_load_multiple_by_properties('file', array('uri' => $uri));
    if (count($existing_files)) {
      $existing = reset($existing_files);
      $fid = $existing->id();
      $file_storage = \Drupal::entityManager()->getStorage('file');
      $file = $file_storage->load($fid);
      return $file;
    }

    // check path extension
    $path = trim($url['path'], '/');
    $path = pathinfo($path);

    // skip no extension
    if (empty($path['extension'])) {
      return FALSE;
    }

    // check that it is allowed
    $allowed_extensions = array(
      'pdf',
      'docx',
      'PDF',
      'xlsx',
      'csv',
      'doc',
      'rtf',
      'txt',
      'ppt',
      'mp3',
      'pps',
      'jpg',
      'png',
      'gif',
      'xls',
      'jpeg',
      'pptx',
      'tar',
      'JPG',
      'xml',
      'ppsx',
      'psd',
      'xlsm',
      'ram',
      'asx',
    );
    if (!in_array($path['extension'], $allowed_extensions)) {
      $known_extensions = array(
        'asp',
        'aspx',
        'as',
        'htm',
        'html',
        'StudentRecruitment',
      );
      if (!in_array($path['extension'], $known_extensions)) {
        drush_print('new extension: ' . $path['extension'] . ' for ' . $href);
      }
      else {
        drush_print('bad extension: ' . $path['extension'] . ' for ' . $href);
      }
      return FALSE;
    }

    // add back query and fragment
    $path = trim($url['path'], '/');
    if (!empty($url['query'])) {
      $path .= '?' . $url['query'];
    }
    if (!empty($url['fragment'])) {
      $path .= '#' . $url['fragment'];
    }

    // build full source
    $source = $url['scheme'] . '://' . $url['host'] . '/' . $path;

    // download file from remote source
    $client = \Drupal::httpClient();
    try {
      $result = $client->get($source, array('http_errors' => FALSE));
      $data = $result->getBody()->getContents();
    }
    catch (Exception $e) {
      drush_print('request exception: ' . $e);
      watchdog_exception('external_csv', $e);
      return FALSE;
    }
    if($result->getStatusCode() != 200) {
      drush_print('bad file status');
      return FALSE;
    }
    if (empty($data)) {
      drush_print('no file data');
      return FALSE;
    }

    // get the filename to save to
    $path = pathinfo($source);
    if (empty($path['basename'])) {
      drush_print('no basename');
      return FALSE;
    }
    $file_name = urldecode($path['basename']);

    // make sure destination is setup
    $destination = $directory . '/' . $file_name;
    $document_dir = 'public://documents';
    file_prepare_directory($document_dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    // save the file
    $file = file_save_data($data, $destination, FILE_EXISTS_REPLACE);
    if(!empty($file)) {
      $uri = $file->getFileUri();
    }

    if (empty($uri)) {
      drush_print('no file uri');
      return FALSE;
    }

    // add a redirect TODO KWALL check this
/*
    $destination = file_create_url($file->uri);
    $this->add_redirect($source, $destination);
*/

    return $file;
  }

  /**
   * Add a redirect
   * Assumes source is relative
   * @todo fix so it won't try to insert duplicate entries
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
    $redirect_object->setSource($source_url);
    $redirect_object->setRedirect($path['source']);
    $redirect_object->setLanguage('und');
    $redirect_object->setStatusCode(\Drupal::config('redirect.settings')->get('default_status_code'));
    $redirect_object->save();

    return $redirect_object;
  }

}
