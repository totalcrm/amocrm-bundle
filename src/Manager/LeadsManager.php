<?php

namespace TotalCRM\AmoCRM\Manager;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\EntitiesServices\Contacts;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TotalCRM\AmoCRM\DependencyInjection\AmoCRMClient;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Cursor;

class LeadsManager
{
    private AmoCRMClient $client;
    private AmoCRMApiClient $apiClient;
    private ?array $config = [];

    /**
     * ContactManager constructor.
     * @param AmoCRMClient $client
     */
    public function __construct(AmoCRMClient $client)
    {
        $this->client = $client;
        $this->config = $client->getConfig();
        $this->apiClient = $this->client->getApiClient();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getLeads(?LeadsFilter $filter = null)
    {
        /** @var Leads $leadsService */
        $leadsService = $this->apiClient->leads();

        try {
            /** @var LeadsCollection $leads */
            $leads = $leadsService->get($filter);
        } catch (\Exception $exception) {
            return null;
        }

        $results = [];
        $page = 0;

        leads_iteration:

        ++$page;

        /** @var LeadModel $leadModel */
        foreach ($leads as $leadModel) {
            $results[] = $leadModel;
        }

        try {
            $leads = $leadsService->nextPage($leads);
            if ($page < 1 && !$leads->isEmpty()) {
                gc_collect_cycles();
                gc_mem_caches();

                goto leads_iteration;
            }
        } catch (AmoCRMApiException $e) {
        }

        return $results ?: null;
    }

}