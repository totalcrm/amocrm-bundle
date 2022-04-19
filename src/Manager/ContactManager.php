<?php

namespace TotalCRM\AmoCRM\Manager;

use AmoCRM\Models\ContactModel;
use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TotalCRM\AmoCRM\DependencyInjection\AmoCRMClient;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Cursor;

/**
 * Class ContactManager
 * @package TotalCRM\AmoCRM\Manager
 */
class ContactManager
{
    private ?array $config = [];
    private ?array $fields = [];
    private AmoCRMClient $client;

    /**
     * ContactManager constructor.
     * @param AmoCRMClient $client
     */
    public function __construct(AmoCRMClient $client)
    {
        $this->client = $client;
        $this->config = $client->getConfig();

        $field_names = $this->config['field_names'] ?? [];
        foreach ($field_names as $field) {
            $this->fields[$field['id']] = $field;
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getContacts()
    {
    }

    /**
     * @param $contactId
     * @return Model\Contact|mixed
     * @throws \Exception
     */
    public function getContact($contactId = null)
    {
    }

    /**
     * Create an contact
     * @param Model\Contact $contact
     * @return mixed|array|void
     * @throws \Exception
     */
    public function addContact($contact = null)
    {
    }

    /**
     * Update an Contact
     * @param Model\Contact|null $contact
     * @return mixed|array|void
     * @throws \Exception
     */
    public function updateContact($contact = null)
    {
    }

    /**
     * Delete an contact
     * @param $id
     * @return mixed|array
     * @throws \Exception
     */
    public function deleteContact($id = null)
    {
    }

    /**
     * @param ContactModel $contact
     * @return array|null
     */
    public function parseContact(?ContactModel $contact = null): ?array
    {
        if (!$contact instanceof ContactModel) {
            return null;
        }

        $item = $contact->toArray();
        unset($item['custom_fields_values']);
        foreach ($item as $key => $value) {
            if ($value instanceof Carbon || in_array($key, ['created_at', 'updated_at'])) {
                $item[$key] = date('c', $value);
            }
        }

        $customFields = $contact->getCustomFieldsValues();
        $customFieldsArray = $customFields ? $customFields->toArray() : [];
        foreach ($customFieldsArray as $customField) {

            if (!isset($this->fields[$customField['field_id']])) {
                continue;
            }

            $config_field = $this->fields[$customField['field_id']];

            $customFieldValues = $customField['values'];
            $values = [];
            foreach ($customFieldValues as $customFieldValue) {
                if ($customFieldValue['value'] instanceof Carbon) {
                    $customFieldValue['value'] = $customFieldValue['value']->format('c');
                }
                $values[] = $customFieldValue;
            }

            if (!count($values)) {
                continue;
            }

            if ($config_field['type'] === 'int') {
                $item[$config_field['name']] = (int)$values[0]['value'];
                continue;
            }
            if ($config_field['type'] === 'string') {
                $item[$config_field['name']] = trim($values[0]['value']);
                continue;
            }
            if ($config_field['type'] === 'bool') {
                $item[$config_field['name']] = (bool)$values[0]['value'];
                continue;
            }
            if ($config_field['type'] === 'date') {
                $item[$config_field['name']] = (new \DateTime(trim($values[0]['value'])))->format('Y-m-d');
                continue;
            }
            if ($config_field['type'] === 'array') {
                $item[$config_field['name']] = $values;
                continue;
            }
            $item[$config_field['name']] = trim($values[0]['value']);
        }

        foreach ($this->fields as $config_field) {
            if (!isset($item[$config_field['name']])) {
                $item[$config_field['name']] = null;
            }
        }

        ksort($item);

        dump($item);
        //dump($contact);

        return $item;
    }
}