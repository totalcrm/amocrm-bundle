<?php

namespace TotalCRM\AmoCRM\DependencyInjection;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\OAuth2\Client\Provider\AmoCRMException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiClientFactory;

use TotalCRM\AmoCRM\Token\SessionStorage;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use RuntimeException;
use Exception;

/**
 * Class AmoCRMClient
 * @package TotalCRM\AmoCRM\DependencyInjection
 */
class AmoCRMClient
{
    private AmoCRMApiClient $apiClient;
    private FilesystemAdapter $cacheAdapter;
    private array $config;
    private SessionStorage $storageManager;

    private int $expires;
    private string $cacheDirectory;

    /**
     * AmoCRMClient constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->getParameter('amo_crm');
        $this->storageManager = $container->get("amo_crm.session_storage");
        $this->expires = 525600; //1 year
        $this->cacheDirectory = $container->getParameter('kernel.project_dir') . ($this->config['cache_path'] ?? '/var/cache_adapter');
        $this->cacheAdapter = new FilesystemAdapter('app.cache.amo_crm', $this->expires, $this->cacheDirectory);

        $clientId = $this->config['client_id'];
        $clientSecret = $this->config['client_secret'];
        $redirectUri = $this->config['redirect_uri'];
        $baseDomain = $this->config['base_domain'];
        
        $this->apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $this->apiClient->setAccountBaseDomain($baseDomain);
    }

    /**
     * @return AmoCRMApiClient
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @return SessionStorage
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }
    
    /**
     *  Return the configuration of AmoCRM
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param string|null $code
     */
    public function setAuthorizationCode(?string $code = ''): void
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->apiClient->getOAuthClient()->getAccessTokenByCode($code);
        $this->storageManager->setToken($accessToken);
    }

    /**
     * Creates a RedirectResponse that will send the user to the OAuth2 server (e.g. send them to Facebook).
     * @return string
     */
    public function redirect(): string
    {
        $state = bin2hex(random_bytes(16));

        $options = [
            'state' => $state,
            'mode' => 'post_message',
        ];

        $url = $this->apiClient->getOAuthClient()->getAuthorizeUrl($options);
        
        
        return $url;
    }

    /**
     * Call this after the user is redirected back to get the access token.
     * @return AccessToken
     * @throws Exception
     */
    public function getAccessToken(): AccessToken
    {
        $cacheKey = 'authorization_code';
        $authorizationCode = null;

        $cacheItem = $this->cacheAdapter->getItem($cacheKey);
        if ($cacheItem && $cacheItem->isHit()) {
            $authorizationCode = $cacheItem->get();
        }

        $token = $this->apiClient->getOAuthClient()->getAccessTokenByCode($authorizationCode);

        $this->storageManager->setToken($token);

        return $token;
    }

    /**
     * @return AccessToken
     * @throws Exception|AmoCRMException
     */
    public function refreshToken(): AccessToken
    {
        /** @var AccessToken $accessToken */
        $accessToken = $this->storageManager->getToken();

        $this->apiClient->setAccessToken($accessToken)
            ->setAccountBaseDomain($this->config['base_domain'])
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken) {
                    $this->storageManager->setToken($accessToken);
                }
            )
        ;

        return $accessToken;
    }

}