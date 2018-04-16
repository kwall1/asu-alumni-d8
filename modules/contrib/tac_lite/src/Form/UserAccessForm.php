<?php
namespace Drupal\tac_lite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\tac_lite\Form\SchemeForm;

/**
 * Builds the form for User Access.
 */
class UserAccessForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tac_lite_user_access_form';
  }
  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['tac_lite.settings'];
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $user = 0) {
    $this->uid = $user;
    $vocabularies = Vocabulary::loadMultiple();
    $config = \Drupal::config('tac_lite.settings');
    $vids = $config->get('tac_lite_categories');
    $schemes = $config->get('tac_lite_schemes');
    if (count($vids)) {
      for ($i = 1; $i <= $schemes; $i++) {
        $config = SchemeForm::tacLiteConfig($i);
        if ($config['name']) {
          $perms = $config['perms'];
          if ($config['term_visibility']) {
            $perms[] = t('term visibility');
          }
          $form['tac_lite'][$config['realm']] = array(
            '#type' => 'details',
            '#title' => $config['name'],
            '#description' => t('This scheme controls %perms.', array('%perms' => implode(' and ', $perms))),
            '#open' => TRUE,
            '#tree' => TRUE,
          );
          // Create a form element for each vocabulary.
          foreach ($vids as $vid) {
            $v = $vocabularies[$vid];
            $default_values = array();
            $data = \Drupal::service('user.data')->get('tac_lite', $user, 'tac_lite_scheme_' . $i) ?: array();
            if (!empty($data) && $data[$vid]) {
              $default_values = $data[$vid];
            }
            $form['tac_lite'][$config['realm']][$vid] = SchemeForm::tacLiteTermSelect($v, $default_values);
            $form['tac_lite'][$config['realm']][$vid]['#description']
              = t('Grant permission to this user by selecting terms.  Note that permissions are in addition to those granted based on user roles.');
          }
        }
      }
      $form['tac_lite'][0] = array(
        '#type' => 'markup',
        '#markup' => '<p>' . t('You may grant this user access based on the schemes and terms below.  These permissions are in addition to <a href=":url">role based grants on scheme settings pages</a>.',
          array(':url' => \Drupal::url('tac_lite.scheme_1'))) . "</p>\n",
        '#weight' => -1,
      );
    }
    else {
      $form['tac_lite_help'] = array(
        '#type' => 'markup',
        '#prefix' => '<p>',
        '#suffix' => '</p>',
        '#markup' => t('First, select one or more vocabularies on the <a href=:url>settings page</a>. Then, return to this page to complete configuration.', array(':url' => \Drupal::url('tac_lite.administration'))),
      );
    }
    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $this->uid;
    // Go through each scheme and copy the form value into the data element.
    $settings = \Drupal::config('tac_lite.settings');
    $schemes = $settings->get('tac_lite_schemes');
    for ($i = 1; $i <= $schemes; $i++) {
      $config = SchemeForm::tacLiteConfig($i);
      if ($config['name']) {
        \Drupal::service('user.data')->set('tac_lite', $uid, $config['realm'], $form_state->getValue($config['realm']));
      }
    }
  }

}
