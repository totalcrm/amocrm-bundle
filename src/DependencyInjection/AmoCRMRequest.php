<?php

namespace TotalCRM\AmoCRM\DependencyInjection;

use TotalCRM\AmoCRM\DependencyInjection\AmoCRMClient;

use DateTime;
use Exception;

/**
 * Class AmoCRMRequest
 * @package TotalCRM\AmoCRM\DependencyInjection
 */
class AmoCRMRequest
{
    private AmoCRMClient $client;

    /**
     * AmoCRMRequest constructor.
     * @param AmoCRMClient $client
     */
    public function __construct(AmoCRMClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $version
     */
    public function setVersion($version = ""): void
    {
        if (in_array($version, ['v1.0', 'beta'])) {
            $this->graph->setApiVersion($version);
        } else {
            $version = $this->client->getConfig()['version'];
            if (in_array($version, ['v1.0', 'beta'])) {
                $this->graph->setApiVersion();
            }
        }
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public function getToken()
    {
        return $this->client->getNewToken()->getToken();
    }

    /**
     * @throws Exception
     */
    public function setTokenGraph(): void
    {
        $this->graph->setAccessToken($this->getToken());
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->client->getConfig()[$key];
    }

    /**
     * @return string
     */
    public function getPreferTimeZone(): string
    {
        return 'outlook.timezone="' . $this->getConfig('prefer_time_zone') . '"';
    }

    /**
     * @param $requestType
     * @param $endpoint
     * @param bool $preferedTimeZone
     * @return GraphRequest
     * @throws Exception
     */
    public function createRequest($requestType, $endpoint, $preferedTimeZone = false): GraphRequest
    {
        $this->setTokenGraph();
        $request = $this->graph->createRequest($requestType, $endpoint);
        if ($preferedTimeZone) {
            $request->addHeaders(["Prefer" => $this->getPreferTimeZone()]);
        }

        return $request;
    }

    /**
     * @param $requestType
     * @param $endpoint
     * @param bool $preferedTimeZone
     * @return GraphCollectionRequest
     * @throws Exception
     */
    public function createCollectionRequest($requestType, $endpoint, $preferedTimeZone = false): GraphCollectionRequest
    {
        $this->setTokenGraph();
        $createCollectionRequest = $this->graph->createCollectionRequest($requestType, $endpoint);
        if ($preferedTimeZone) {
            $createCollectionRequest->addHeaders(["Prefer" => $this->getPreferTimeZone()]);
        }
                
        return $createCollectionRequest;
    }

    /**
     * Format
     * @param DateTime $date
     * @return string
     */
    public function getDateFormat(DateTime $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    /**
     * @param DateTime $date
     * @return Model\DateTimeTimeZone
     */
    public function getDateTimeTimeZone(DateTime $date): Model\DateTimeTimeZone
    {
        $dateTime = $this->getDateFormat($date);
        $timezone = $this->getConfig('prefer_time_zone');

        return new Model\DateTimeTimeZone(['dateTime' => $dateTime, 'timezone' => $timezone]);
    }
}