<?php

namespace TotalCRM\AmoCRM\Token;

use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SessionStorage
 * @package TotalCRM\AmoCRM\Token
 */
class SessionStorage implements TokenStorageInterface
{
    private FilesystemAdapter $cacheAdapter;
    private string $cacheKey;
    private int $expires;

    /**
     * SessionStorage constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $config = $container->getParameter('amo_crm');

        $this->expires = 525600; //1 year
        $this->cacheKey = 'amo_crm';
        $cacheDirectory = $container->getParameter('kernel.project_dir') . ($config['cache_path'] ?? '/var/cache_adapter');
        $this->cacheAdapter = new FilesystemAdapter('app.cache.amo_crm', $this->expires, $cacheDirectory);
    }

    /**
     * @param AccessToken|AccessTokenInterface $token
     * @return void
     */
    public function setToken($token): void
    {
        $options = [
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires' => $token->getExpires(),
            'baseDomain' => 'vipflat.amocrm.ru',
        ];

        $cacheItem = $this->cacheAdapter->getItem($this->cacheKey);
        $cacheItem->expiresAfter($this->expires)->set($options);
        $this->cacheAdapter->save($cacheItem);

    }

    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        $options = [];
        $cacheItem = $this->cacheAdapter->getItem($this->cacheKey);
        if ($cacheItem && $cacheItem->isHit()) {
            $options = $cacheItem->get();
        }

        return new AccessToken($options);
    }
}
