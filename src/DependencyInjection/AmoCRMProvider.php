<?php

namespace TotalCRM\AmoCRM\DependencyInjection;

use TotalCRM\AmoCRM\DependencyInjection\AmoCRMResourceOwner;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AmoCRMProvider
 * @package TotalCRM\AmoCRM\DependencyInjection
 */
class AmoCRMProvider extends GenericProvider
{
    public const AUTHORITY_URL = 'https://login.amocrm.com/common';
    public const RESOURCE_ID = 'https://amocrm.com';
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * AmoCRMProvider constructor.
     * @param array|null $options
     */
    public function __construct(?array $options = null)
    {
        if (!$options) {
            $options = [];
        }

        parent::__construct($options);
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return AmoCRMResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): AmoCRMResourceOwner
    {
        return new AmoCRMResourceOwner($response, self::ACCESS_TOKEN_RESOURCE_OWNER_ID);
    }

}