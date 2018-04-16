<?php

namespace Drupal\linkchecker\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Builds edit link settings form.
 */
class LinkCheckerEditLinkSettingsForm {

  /**
   * Edit link settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $link) {
    $config = $this->config('linkchecker.settings');

    $form['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#description' => $this->t('The link <a href=":url">:url</a> was last checked on @last_checked and failed @fail_count times.', [':url' => $link->url, '@fail_count' => $link->fail_count, '@last_checked' => DateFormatter::format($link->last_checked)]),
      '#open' => TRUE,
    ];

    $form['settings']['lid'] = ['#type' => 'value', '#value' => $link->lid];
    $form['settings']['url'] = ['#type' => 'value', '#value' => $link->url];

    $form['settings']['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Select request method'),
      '#default_value' => $link->method,
      '#options' => [
        'HEAD' => $this->t('HEAD'),
        'GET' => $this->t('GET'),
      ],
      '#description' => $this->t('Select the request method used for link checks of this link. If you encounter issues like status code 500 errors with the HEAD request method you should try the GET request method before ignoring a link.'),
    ];

    $form['settings']['status'] = [
      '#default_value' => $link->status,
      '#type' => 'checkbox',
      '#title' => $this->t('Check link status'),
      '#description' => $this->t('Uncheck if you wish to ignore this link. Use this setting only as a last resort if there is no other way to solve a failed link check.'),
    ];

    $form['maintenance'] = [
      '#type' => 'details',
      '#title' => $this->t('Maintenance'),
      '#open' => TRUE,
    ];

    $form['maintenance']['recheck'] = [
      '#default_value' => 0,
      '#type' => 'checkbox',
      '#title' => $this->t('Re-check link status on next cron run'),
      '#description' => $this->t('Enable this checkbox if you want to re-check the link during the next cron job rather than wait for the next scheduled check on @date.', ['@date' => DateFormatter::format($link->last_checked + $config->get('check.interval'))]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Edit link settings form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Force link re-check asap.
    if ($form_state->getValue('recheck')) {
      db_update('linkchecker_link')
        ->condition('lid', $form_state->getValue('lid'))
        ->fields(array('last_checked' => 0))
        ->execute();
      drupal_set_message(t('The link %url will be checked again on the next cron run.', ['%url' => $form_state->getValue('url')]));
    }

    if ($form_state->getValue('method') != $form['settings']['method']['#default_value']) {
      // Update settings and reset statistics for a quick re-check.
      db_update('linkchecker_link')
        ->condition('lid', $form_state->getValue('lid'))
        ->fields(array(
          'method' => $form_state->getValue('method'),
          'fail_count' => 0,
          'last_checked' => 0,
          'status' => $form_state->getValue('status'),
        ))
        ->execute();
      drupal_set_message(t('The link settings for %url have been saved and the fail counter has been reset.', array('%url' => $form_state->getValue('url'))));
    }
    else {
      // Update setting only.
      db_update('linkchecker_link')
        ->condition('lid', $form_state->getValue('lid'))
        ->fields(array(
          'method' => $form_state->getValue('method'),
          'status' => $form_state->getValue('status'),
        ))
        ->execute();
      drupal_set_message(t('The link settings for %url have been saved.', array('%url' => $form_state->getValue('url'))));
    }
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  // @FIXME
  public function access($link) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.
    return AccessResult::allowedIf($account->hasPermission('edit link settings') && _linkchecker_link_access($link));
        //$this->someOtherCustomCondition());
  }

}
