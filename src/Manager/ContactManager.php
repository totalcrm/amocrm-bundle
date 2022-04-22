<?php

namespace TotalCRM\AmoCRM\Manager;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\EntitiesServices\Contacts;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\ContactsFilter;
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
    private AmoCRMClient $client;
    private AmoCRMApiClient $apiClient;
    private ?array $config = [];
    private ?array $fieldNames = [];
    private ?int $fieldEmails;
    private ?int $fieldPhones;

    /**
     * ContactManager constructor.
     * @param AmoCRMClient $client
     */
    public function __construct(AmoCRMClient $client)
    {
        $this->client = $client;
        $this->config = $client->getConfig();
        $this->apiClient = $this->client->getApiClient();

        $this->fieldPhones = $this->config['field_phones'] ?? null;
        $this->fieldEmails = $this->config['field_emails'] ?? null;
        $fieldNames = $this->config['field_names'] ?? [];
        foreach ($fieldNames as $fieldName) {
            $this->fieldNames[$fieldName['id']] = $fieldName;
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
    public function getContacts(?ContactsFilter $filter = null)
    {
        /** @var Contacts $contactsService */
        $contactsService = $this->apiClient->contacts();

        try {
            /** @var ContactsCollection $contacts */
            $contacts = $contactsService->get($filter);
        } catch (\Exception $exception) {
            return null;
        }

        $results = [];
        $page = 0;

        contacts_iteration:

        ++$page;

        /** @var ContactModel $contactModel */
        foreach ($contacts as $contactModel) {
            $results[] = $contactModel;
        }

        try {
            $contacts = $contactsService->nextPage($contacts);
            if ($page < 1000 && !$contacts->isEmpty()) {
                gc_collect_cycles();
                gc_mem_caches();

                goto contacts_iteration;
            }
        } catch (AmoCRMApiException $e) {
        }

        return $results ?: null;
    }

    /**
     * @param int|null $contactId
     * @return ContactModel|mixed
     * @throws \Exception
     */
    public function getContact(?int $contactId = null): ?ContactModel
    {
        if (!$contactId) {
            return null;
        }

        $filter = new ContactsFilter();
        $filter->setLimit(1)->setIds([$contactId]);

        /** @var Contacts $contactsService */
        $contactsService = $this->apiClient->contacts();

        try {
            /** @var ContactsCollection $contacts */
            $contacts = $contactsService->get($filter);
        } catch (\Exception $exception) {
            return null;
        }

        return $contacts->count() ? $contacts->first() : null;
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
            if ($value && ($value instanceof Carbon || in_array($key, ['created_at', 'updated_at', 'closest_task_at']))) {
                $item[$key] = date('c', $value);
            }
        }

        $itemPhones = [];
        $itemPhonesWork = [];
        $itemPhonesHome = [];
        $itemPhonesMobile = [];
        $itemPhonesOther = [];

        $itemEmails = [];
        $itemEmailsWork = [];
        $itemEmailsOther = [];
        $itemEmailsPriv = [];

        $customFields = $contact->getCustomFieldsValues();
        $customFieldsArray = $customFields ? $customFields->toArray() : [];
        foreach ($customFieldsArray as $customField) {

            if (!isset($this->fieldNames[$customField['field_id']])) {
                continue;
            }

            $configField = $this->fieldNames[$customField['field_id']];
            $configFieldId = $configField['id'] ?? null;
            $configFieldType = $configField['type'] ?? null;
            $configFieldName = $configField['name'] ?? null;

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


            if ((int)$configFieldId === (int)$this->fieldPhones && (int)$configFieldId && (int)$this->fieldPhones) {
                foreach ($values as $value) {
                    if (isset($value['enum_code'], $value['value']) && $value['enum_code'] && $value['value']) {
                        if (!in_array($value['value'], $itemPhones)) {
                            $itemPhones[] = $value['value'];
                        }

                        if (in_array($value['enum_code'], ['WORK', 'WORKDD'])) {
                            if (!in_array($value['value'], $itemPhonesWork)) {
                                $itemPhonesWork[] = $value['value'];
                            }
                            continue;
                        }

                        if (in_array($value['enum_code'], ['OTHER', 'FAX'])) {
                            if (!in_array($value['value'], $itemPhonesOther)) {
                                $itemPhonesOther[] = $value['value'];
                            }
                            continue;
                        }

                        if ($value['enum_code'] === 'HOME') {
                            if (!in_array($value['value'], $itemPhonesHome)) {
                                $itemPhonesHome[] = $value['value'];
                            }
                            continue;
                        }

                        if ($value['enum_code'] === 'MOB') {
                            if (!in_array($value['value'], $itemPhonesMobile)) {
                                $itemPhonesMobile[] = $value['value'];
                            }
                            continue;
                        }
                    }
                }
                //continue;
            }

            if ((int)$configFieldId === (int)$this->fieldEmails && (int)$configFieldId && (int)$this->fieldEmails) {
                foreach ($values as $value) {
                    if (isset($value['enum_code'], $value['value']) && $value['enum_code'] && $value['value']) {
                        if (!in_array($value['value'], $itemEmails)) {
                            $itemEmails[] = $value['value'];
                        }

                        if ($value['enum_code'] === 'WORK') {
                            if (!in_array($value['value'], $itemEmailsWork)) {
                                $itemEmailsWork[] = $value['value'];
                            }
                            continue;
                        }

                        if ($value['enum_code'] === 'OTHER') {
                            if (!in_array($value['value'], $itemEmailsOther)) {
                                $itemEmailsOther[] = $value['value'];
                            }
                            continue;
                        }

                        if ($value['enum_code'] === 'PRIV') {
                            if (!in_array($value['value'], $itemEmailsPriv)) {
                                $itemEmailsPriv[] = $value['value'];
                            }
                            continue;
                        }
                    }
                }
                //continue;
            }


            if (in_array($configFieldType, ['string', '', null]) && count($values) > 1) {
                $values[0]['value'] = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }

            if ($configFieldType === 'int') {
                $item[$configFieldName] = (int)$values[0]['value'];
                continue;
            }
            if ($configFieldType === 'string') {
                $item[$configFieldName] = trim($values[0]['value']);
                continue;
            }
            if ($configFieldType === 'bool') {
                $item[$configFieldName] = (bool)$values[0]['value'];
                continue;
            }
            if ($configFieldType === 'date') {
                try {
                    $item[$configFieldName] = (new \DateTime(trim($values[0]['value'])))->format('Y-m-d');
                } catch (\Exception $exception) {
                }
                continue;
            }
            if ($configFieldType === 'datetime') {
                try {
                    $item[$configFieldName] = (new \DateTime(trim($values[0]['value'])))->format('c');
                } catch (\Exception $exception) {
                }
                continue;
            }
            if ($configFieldType === 'array') {
                $item[$configFieldName] = is_array($values) ? $values : [$values];
                continue;
            }
            $item[$configFieldName] = trim($values[0]['value']);
        }

        foreach ($this->fieldNames as $configField) {
            if (isset($configField['name'])
                && $configField['name']
                && !isset($item[$configField['name']])
            ) {
                $item[$configField['name']] = null;
            }
        }

        $item['all_phones'] = $itemPhones ?: null;
        $item['all_phones_work'] = $itemPhonesWork ?: null;
        $item['all_phones_home'] = $itemPhonesHome ?: null;
        $item['all_phones_mobile'] = $itemPhonesMobile ?: null;
        $item['all_phones_other'] = $itemPhonesOther ?: null;

        $item['all_emails'] = $itemEmails ?: null;
        $item['all_emails_work'] = $itemEmailsWork ?: null;
        $item['all_emails_other'] = $itemEmailsOther ?: null;
        $item['all_emails_priv'] = $itemEmailsPriv ?: null;

        $item['all_tags'] = $contact->getTags() ? $this->clearArray($contact->getTags()->toArray()) : null;
        $item['all_leads'] = $contact->getLeads() ? $this->clearArray($contact->getLeads()->toArray()) : null;
        $item['all_customers'] = $contact->getCustomers() ? $this->clearArray($contact->getCustomers()->toArray()) : null;

        ksort($item);

        //dump($item);
        //dump($contact);

        return $item;
    }

    /**
     * @param array|null $array
     * @return array|null
     */
    private function clearArray($array)
    {
        if (!$array || !count($array)) {
            return null;
        }

        if (is_array($array)) {
            foreach ($array as $key => $item) {
                if (is_array($item)) {
                    $array[$key] = array_diff($item, [0, null, false, '']);
                }
            }
        }


        return $array;
    }
}