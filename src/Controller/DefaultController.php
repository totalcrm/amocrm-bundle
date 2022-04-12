<?php

namespace TotalCRM\AmoCRM\Controller;

use TotalCRM\AmoCRM\DependencyInjection\AmoCRMClient;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTime;
use Exception;

/**
 * Class DefaultController
 * @package TotalCRM\AmoCRM\Controller
 */
class DefaultController extends AbstractController
{

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function indexAction(Request $request)
    {
        $client = $this->get('amo_crm.client');
        $session = $this->get('session');

    }

    public function connectAction()
    {
        return $this->get('amo_crm.client')->redirect();
    }

    /**
     * After going to Office365, you're redirected back here
     * because this is the "graph_check" you configured
     * in config.yml
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function connectCheckAction(Request $request): RedirectResponse
    {
        /** @var AmoCRMClient $client */
        $client = $this->get('amo_crm.client');
        $token = $client->getAccessToken();
        $tokenStorage = $this->get("amo_crm.session_storage");
        $tokenStorage->setToken($token);
        $homePage = $this->getParameter("amo_crm")["home_page"];

        return new RedirectResponse($this->generateUrl($homePage));
    }
}
