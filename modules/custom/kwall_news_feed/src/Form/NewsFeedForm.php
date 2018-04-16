<?php

namespace Drupal\kwall_news_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\media_entity\Entity\Media;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Class NewsFeedForm.
 */
class NewsFeedForm extends ConfigFormBase
{

    /**
     * Drupal\Core\Entity\EntityManagerInterface definition.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The alias manager.
     *
     * @var \Drupal\Core\Path\AliasManagerInterface
     */
    protected $aliasManager;
    /**
     * Constructs a new NewsFeedForm object.
     */

    /**
     * The path alias storage.
     *
     * @var \Drupal\Core\Path\AliasStorageInterface
     */
    protected $aliasStorage;


    public function __construct(
        ConfigFactoryInterface $config_factory,
        EntityManagerInterface $entity_manager,
        AliasManagerInterface $alias_manager,
        AliasStorageInterface $alias_storage

    )
    {
        parent::__construct($config_factory);
        $this->entityManager = $entity_manager;
        $this->aliasManager = $alias_manager;
        $this->aliasStorage = $alias_storage;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('entity.manager'),
            $container->get('path.alias_manager'),
            $container->get('path.alias_storage')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'news_feed_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('kwall_news_feed.newsfeed');
        $content_types_fields = $this->contentTypeFields('article');
        $form['field_image_fieldset'] = array(
            '#type' => 'fieldset',
            '#title' => $this
                ->t('Image'),
        );
        $form['field_image_fieldset']['field_image_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('field_image_url'),
            '#description' => $this->t('choose the field from json url to map with field_image URL'),
            '#placeholder' => $this->t('keep empty if there is no mapping.'),
            '#required' => true,
            '#default_value' => $config->get('field_image_url')
        ];
        $form['field_image_fieldset']['field_image_alt'] = [
            '#type' => 'textfield',
            '#title' => $this->t('field_image_alt'),
            '#description' => $this->t('choose the field from json url to map with field_image ALT'),
            '#placeholder' => $this->t('keep empty if there is no mapping.'),
            '#required' => true,
            '#default_value' => $config->get('field_image_alt')
        ];
        foreach ($content_types_fields as $machine_name_field => $field) {
            if ($machine_name_field != "field_image") {

                $form[$machine_name_field] = [
                    '#type' => 'textfield',
                    '#title' => $this->t($machine_name_field),
                    '#description' => $this->t('choose the field from json url to map with ' . $machine_name_field),
                    '#placeholder' => $this->t('keep empty if there is no mapping.')
                ];
                if ($machine_name_field == 'created' || $machine_name_field == 'path' || $machine_name_field == 'title') {
                    $form[$machine_name_field]['#required'] = true;
                }
                if ($machine_name_field == 'title') {
                    $form[$machine_name_field]['#default_value'] = $config->get('title');
                }
                if ($machine_name_field == 'path') {
                    $form[$machine_name_field]['#default_value'] = $config->get('path');
                }
                if ($machine_name_field == 'created') {
                    $form[$machine_name_field]['#default_value'] = $config->get('created');
                }
            }
        }
        $form['limit'] = [
            '#type' => 'number',
            '#title' => $this->t('Limit Article'),
            '#description' => $this->t('Choose the number of article you want to pull, Leave empty if you want to add all new contents.'),
            '#default_value' => $config->get('limit'),
            '#placeholder' => $this->t("empty to import all")
        ];
        $form['json_url'] = [
            '#type' => 'url',
            '#title' => $this->t('JSON URL'),
            '#description' => $this->t('Link of JSON URL.'),
            '#required' => true,
            '#default_value' => $config->get('json_url')
        ];

        $form['active_schedule'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Import new contents at midnight'),
            '#default_value' =>$config->get('active_schedule'),
        ];
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save configuration'),
            '#button_type' => 'primary',
            '#submit' => array('::submitForm')
        ];
        $form['actions']['pull_content'] = [
            '#type' => "submit",
            '#value' => $this->t('Pull content'),
            '#submit' => array('::saveContent')
        ];

        $form['#theme'] = 'system_config_form';
        return $form;


    }

    /**
     * Get all content fields.
     * @param $contentType
     * @return array
     */
    function contentTypeFields($contentType)
    {
        $entityManager = \Drupal::service('entity.manager');
        $fields = [];

        if (!empty($contentType)) {
            $fields = array_filter(
                $entityManager->getFieldDefinitions('node', $contentType), function ($field_definition) {
                return $field_definition;

            }
            );
        }
        unset($fields['nid']);
        unset($fields['uuid']);
        unset($fields['vid']);
        unset($fields['langcode']);
        unset($fields['type']);
        unset($fields['revision_timestamp']);
        unset($fields['revision_uid']);
        unset($fields['revision_log']);
        unset($fields['status']);
        unset($fields['uid']);
        unset($fields['promote']);
        unset($fields['changed']);
        unset($fields['sticky']);
        unset($fields['default_langcode']);
        unset($fields['revision_translation_affected']);
        unset($fields['moderation_state']);
        unset($fields['metatag']);
        unset($fields['publish_on']);
        unset($fields['unpublish_on']);
        unset($fields['menu_link']);

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Validate Json URL feed.
        $config = $this->config('kwall_news_feed.newsfeed');
        $config->set('json_url', $form_state->getValue('json_url'));
        if (file_get_contents($config->get('json_url')) == FALSE) {
            $form_state->setErrorByName('json_url', $this->t('You should set a valid URL feed !'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $content_types_fields = $this->contentTypeFields('article');

        foreach ($content_types_fields as $machine_name_field => $field) {
            if ($machine_name_field != "field_image") {
                $this->config('kwall_news_feed.newsfeed')
                    ->set($machine_name_field, $form_state->getValue($machine_name_field))
                    ->save();
            }

        }
        $this->config('kwall_news_feed.newsfeed')
            ->set('field_image_url', $form_state->getValue('field_image_url'))
            ->set('field_image_alt', $form_state->getValue('field_image_alt'))
            ->set('limit', $form_state->getValue('limit'))
            ->set('json_url', $form_state->getValue('json_url'))
            ->set('active_schedule', $form_state->getValue('active_schedule'))
            ->save();

        drupal_set_message($this->t('Feeds News Mapping configuration was added successfully!.'));

    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    function saveContent(array &$form, FormStateInterface $form_state)
    {   // Save content in db.
        pullContent();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'kwall_news_feed.newsfeed',
        ];
    }


}
