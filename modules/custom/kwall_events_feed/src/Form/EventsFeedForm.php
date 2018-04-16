<?php

namespace Drupal\kwall_events_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;

/**
 * Class EventsFeedForm.
 */
class EventsFeedForm extends ConfigFormBase
{

    /**
     * Drupal\Core\Entity\EntityManagerInterface definition.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;
    /**
     * Drupal\Core\Path\AliasStorageInterface definition.
     *
     * @var \Drupal\Core\Path\AliasStorageInterface
     */
    protected $pathAliasStorage;

    /**
     * Constructs a new EventsFeedForm object.
     */
    public function __construct(
        ConfigFactoryInterface $config_factory,
        EntityManagerInterface $entity_manager,
        AliasStorageInterface $path_alias_storage
    )
    {
        parent::__construct($config_factory);
        $this->entityManager = $entity_manager;
        $this->pathAliasStorage = $path_alias_storage;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('entity.manager'),
            $container->get('path.alias_storage')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'events_feed_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('kwall_events_feed.eventsfeed');

        $form['all_events_xml_url'] = [
            '#type' => 'url',
            '#title' => $this->t('All events xml URL'),
            '#description' => $this->t('Link of All events xml URL.'),
            '#required' => true,
            '#default_value' => $config->get('all_events_xml_url'),
            '#weight' => 20
        ];
        $form['alumni_events_xml_url'] = [
            '#type' => 'url',
            '#title' => $this->t('Alumni events xml URL'),
            '#description' => $this->t('Link of Alumni events xml URL.'),
            '#required' => true,
            '#default_value' => $config->get('alumni_events_xml_url'),
            '#weight' => 21
        ];


        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save configuration'),
            '#button_type' => 'primary',
            '#submit' => array('::submitForm')
        ];
        $form['actions']['pull_content_all_events'] = [
            '#type' => "submit",
            '#value' => $this->t('Pull All Events'),
            '#submit' => array('::saveContentAllEvents')
        ];
        $form['actions']['pull_content_alumni_events'] = [
            '#type' => "submit",
            '#value' => $this->t('Pull Alumni Events'),
            '#submit' => array('::saveContentAlumniEvents')
        ];
        $form['actions']['update_alumni_events'] = [
            '#type' => "submit",
            '#value' => $this->t('Update Alumni Events'),
            '#submit' => array('::updateAlumni_events')
        ];
        $form['#theme'] = 'system_config_form';


        return $form;

    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Validate XML URLs feed.
        $config = $this->config('kwall_events_feed.eventsfeed');
        $config->set('all_events_xml_url', $form_state->getValue('all_events_xml_url'));
        $config->set('alumni_events_xml_url', $form_state->getValue('alumni_events_xml_url'));

        if (file_get_contents($config->get('all_events_xml_url')) == FALSE) {
            $form_state->setErrorByName('all_events_xml_url', $this->t('You should set a valid XML All events URL feed !'));
        }
        if (file_get_contents($config->get('alumni_events_xml_url')) == FALSE) {
            $form_state->setErrorByName('alumni_events_xml_url', $this->t('You should set a valid XML Aall events URL feed !'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $config = $this->config('kwall_events_feed.eventsfeed');
        $config
            ->set('all_events_xml_url', $form_state->getValue('all_events_xml_url'))
            ->set('alumni_events_xml_url', $form_state->getValue('alumni_events_xml_url'))
            ->save();

        drupal_set_message($this->t('Feeds Events Mapping configuration was added successfully!.'));
    }
    public function saveContentAllEvents(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('kwall_events_feed.eventsfeed');
        $index = 0;
        $all_events_mapped = [];

        while(file_get_contents($config->get('all_events_xml_url')."?page=".$index)!== false && $index < 21){

            $all_events = file_get_contents($config->get('all_events_xml_url')."?page=".$index);
            $xml_all_events = simplexml_load_string($all_events);
            $all_events_mapped = array_merge($all_events_mapped,$this->getAllEventNodesFromXml($xml_all_events));
            \Drupal::logger('kwall_events_feed')->notice('page: '.$index);
            $index++;
        }


        $batch = array(
            'operations' => array(),
            'finished' => '_batch_import_all_finished',
            'title' => $this->t('Import All new events'),
            'init_message' => $this->t('Creating events is starting'),
            'progress_message' => 'Processed @current out of @total reste @percentage',
            'error_message' => $this->t('It has an error.'),
        );
        foreach ($all_events_mapped as $event){
            $batch['operations'][] = array('_batch_import_all_events_progress', array($event));
        }
        batch_set($batch);
    }

    /**
     * Get event from xml url.
     * @param $events
     * @return array
     */
    function getAllEventNodesFromXml($xml_all_events)
    {
        $res = [];
        $results = [];
        foreach ($xml_all_events as $index => $event) {
            $res['nid'] = $event->nid->__toString();
            $res['title'] = $event->title->__toString();
            $res['body_summary'] = $event->body_summary->__toString();
            $res['full_body'] = $event->full_body->__toString();
            $res['phone'] = $event->phone->__toString();
            $res['contactname'] = $event->contactname->__toString();
            $res['email'] = $event->email->__toString();
            $res['alias'] = str_replace("https://asuevents.asu.edu", "", $event->alias->__toString());
            $res['website'] = isset($event->website)?$event->website->__toString():"";
            $res['link_text'] = isset($event->ticketing_rsvp_txt)?$event->ticketing_rsvp_txt->__toString():"";
            $res['link_url'] = isset($event->ticketing_rsvp_url)?$event->ticketing_rsvp_url->__toString():"";
            $res['image_url'] = isset($event->image_url)?$event->image_url->__toString():"";
            $res['image_alt'] = isset($event->image_alt) ? $event->image_alt->__toString() : "";
            $res['image_title'] = isset($event->image_title) ? $event->image_title->__toString() : "";
            $res['start_date'] = isset($event->start_date) ? $event->start_date->__toString() : "";
            $res['end_date'] = isset($event->end_date) ? $event->end_date->__toString() : "";
            $res['location_term'] = $event->location_term->__toString();

            unset($res['audiences']);
            if (isset($event->audiences->audience)) {
                $res['audiences'] = (array)$event->audiences->audience;
            }

            unset($res['event_map_url']);
            unset($res['event_map_title']);

            if (isset($event->event_map_url) && $event->event_map_url->__toString() !== "") {
                $res['event_map_url'] = $event->event_map_url->__toString();
                $res['event_map_title'] = $event->event_map_title->__toString();
            }
            if (isset($event->map)) {
                $res['map'] = $event->map->__toString();
            }
            $results[] = $res;
        }
        return $results;
    }

    public function updateAlumni_events(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('kwall_events_feed.eventsfeed');
        $index = 0;
        $alumni_events_mapped = [];

        while (file_get_contents($config->get('all_events_xml_url') . "?page=" . $index) !== false && $index < 21) {

            $alumni_events = file_get_contents($config->get('all_events_xml_url') . "?page=" . $index);
            $xml_alumni_events = simplexml_load_string($alumni_events);
            $alumni_events_mapped = array_merge($alumni_events_mapped, $this->getAllEventNodesFromXml($xml_alumni_events));
            \Drupal::logger('kwall_events_feed')->notice('page: ' . $index);
            $index++;
        }
        $batch = array(
            'operations' => array(),
            'finished' => '_batch_import_all_finished',
            'title' => $this->t('Import Alumni new events'),
            'init_message' => $this->t('Updating events is starting'),
            'progress_message' => 'Processed @current out of @total reste @percentage',
            'error_message' => $this->t('It has an error.'),
        );
        foreach ($alumni_events_mapped as $event) {
            $batch['operations'][] = array('_batch_update_if_alumni_events_progress', array($event));
        }
        batch_set($batch);

    }

    /**
     * {@inheritdoc}
     */
    public function saveContentAlumniEvents(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('kwall_events_feed.eventsfeed');
        $index = 0;
        $alumni_events_mapped = [];

        while (file_get_contents($config->get('alumni_events_xml_url') . "?page=" . $index) !== false && $index < 11) {

            $alumni_events = file_get_contents($config->get('alumni_events_xml_url') . "?page=" . $index);
            $xml_alumni_events = simplexml_load_string($alumni_events);
            $alumni_events_mapped = array_merge($alumni_events_mapped, $this->getAllEventNodesFromXml($xml_alumni_events));
            \Drupal::logger('kwall_events_feed')->notice('page: ' . $index);
            $index++;
        }
        $batch = array(
            'operations' => array(),
            'finished' => '_batch_import_all_finished',
            'title' => $this->t('Import Alumni new events'),
            'init_message' => $this->t('Creating events is starting'),
            'progress_message' => 'Processed @current out of @total reste @percentage',
            'error_message' => $this->t('It has an error.'),
        );
        foreach ($alumni_events_mapped as $event) {
            $batch['operations'][] = array('_batch_import_all_events_progress', array($event));
        }
        batch_set($batch);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'kwall_events_feed.eventsfeed',
        ];
    }

}