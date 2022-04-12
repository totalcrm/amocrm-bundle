<?php

namespace TotalCRM\AmoCRM\Entity;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Interface AmoCRMTokenInterface
 * @package TotalCRM\AmoCRM\Entity
 */
interface AmoCRMTokenInterface
{
    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function setAmoCRMToken(AccessToken $accessToken);

    /**
     * @return mixed
     */
    public function getAmoCRMToken();

}