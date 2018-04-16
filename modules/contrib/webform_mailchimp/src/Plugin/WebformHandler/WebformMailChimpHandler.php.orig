<?php

namespace Drupal\webform_mailchimp\Plugin\WebformHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form submission to MailChimp handler.
 *
 * @WebformHandler(
 *   id = "mailchimp",
 *   label = @Translation("MailChimp"),
 *   category = @Translation("MailChimp"),
 *   description = @Translation("Sends a form submission to a MailChimp list."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class WebformMailChimpHandler extends WebformHandlerBase {

  /**
   * The token manager.
   *
   * @var \Drupal\webform\WebformTranslationManagerInterface
   */
  protected $token_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, WebformSubmissionConditionsValidatorInterface $conditions_validator, WebformTokenManagerInterface $token_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
    $this->tokenManager = $token_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('webform.token_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $lists = mailchimp_get_lists();
    return [
      '#theme' => 'markup',
      '#markup' => '<strong>' . $this->t('List') . ': </strong>' . (!empty($lists[$this->configuration['list']]) ? $lists[$this->configuration['list']]->name : ''),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'list' => [],
      'email' => '',
      'double_optin' => TRUE,
      'mergevars' => '',
      'interest_groups' => [],
      'control' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $lists = mailchimp_get_lists();

    $options = [];
    $options[''] = $this->t('- Select an option -');
    foreach ($lists as $list) {
      $options[$list->id] = $list->name;
    }

    $form['mailchimp'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('MailChimp settings'),
    ];
    $form['mailchimp']['list'] = [
      '#type' => 'webform_select_other',
      '#title' => $this->t('List'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['list'],
      '#options' => $options,
      '#description' => $this->t('Select the list you want to send this submission to. Alternatively, you can also use the Other field for token replacement.'),
    ];

    $fields = $this->getWebform()->getElementsDecoded();
    $options = [];
    $options[''] = $this->t('- Select an option -');
    foreach ($fields as $field_name => $field) {
      if ($field['#type'] == 'email') {
        $options[$field_name] = $field['#title'];
      }
    }

    $form['mailchimp']['email'] = [
      '#type' => 'select',
      '#title' => $this->t('Email field'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['email'],
      '#options' => $options,
    ];

    $options = [];
    $options[''] = $this->t('- Select an option -');
    foreach ($fields as $field_name => $field) {
      if ($field['#type'] == 'checkbox') {
        $options[$field_name] = $field['#title'];
      }
    }

    $form['mailchimp']['control'] = [
      '#type' => 'select',
      '#title' => $this->t('Control field'),
      '#default_value' => $this->configuration['control'],
      '#options' => $options,
      '#description' => $this->t('An optional control checkbox field to prevent unwanted subscriptions.'),
    ];

    $form['mailchimp']['mergevars'] = [
      '#type' => 'webform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Merge vars'),
      '#default_value' => $this->configuration['mergevars'],
      '#description' => $this->t('Enter the mergevars that will be sent to mailchimp, each line a <em>margevar: \'value\'</em>. You may use tokens.'),
    ];

    $form['mailchimp']['interest_groups'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Interest groups'),
    ];

    if ($form['list']['#default_value']) {
      $list = mailchimp_get_list($form['list']['#default_value']);
      if (!empty($list->intgroups)) {
        $groups_default = $this->configuration['interest_groups'];

        if (empty($groups_default)) {
          $groups_default = [];
        }
        $form['mailchimp']['interest_groups'] += mailchimp_interest_groups_form_elements($list, $groups_default);
      }
    }
    else {
      $form['mailchimp']['interest_groups']['#description'] = $this->t('Will appear the next time you edit this handler with the list selected.');
    }

    $form['mailchimp']['double_optin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Double opt-in'),
      '#default_value' => $this->configuration['double_optin'],
    ];

    $form['mailchimp']['token_tree_link'] = $this->tokenManager->buildTreeLink();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    foreach ($this->configuration as $name => $value) {
      if (isset($values['mailchimp'][$name])) {
        $this->configuration[$name] = $values['mailchimp'][$name];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // If update, do nothing
    if ($update) {
      return;
    }

    $fields = $webform_submission->toArray(TRUE);

    // If there's a checkbox configured, check for its value
    if (!empty($this->configuration['control']) && empty($fields['data'][$this->configuration['control']])) {
      return;
    }

    // Replace tokens.
    $configuration = $this->tokenManager->replace($this->configuration, $webform_submission);

    $email = $fields['data'][$configuration['email']];

    $mergevars = Yaml::decode($configuration['mergevars']);

    // Allow other modules to alter the merge vars.
    // @see hook_mailchimp_lists_mergevars_alter().
    $entity_type = 'webform_submission';
    \Drupal::moduleHandler()->alter('mailchimp_lists_mergevars', $mergevars, $webform_submission, $entity_type);

    mailchimp_subscribe($configuration['list'], $email, $mergevars, $configuration['interest_groups'], $configuration['double_optin']);
  }

}
