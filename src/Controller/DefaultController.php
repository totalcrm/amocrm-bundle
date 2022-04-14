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
     * @return RedirectResponse
     * @throws \Exception
     */
    public function requestAction(Request $request): RedirectResponse
    {
        /** @var AmoCRMClient $client */
        $client = $this->get('amo_crm.client');

        return new RedirectResponse($client->redirect());
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function authAction(Request $request): RedirectResponse
    {
        /** @var AmoCRMClient $client */
        $client = $this->get('amo_crm.client');
        $authorizationCode = $request->get('code');

        try {
            $client->setAuthorizationCode($authorizationCode);
        } catch (\Exception $e) {
        }

        try {
            $token = $client->refreshToken();
        } catch (\Exception $e) {
        }

        $redirectPage = $this->container->getParameter("amo_crm")["home_page"];

        return new RedirectResponse($this->generateUrl($redirectPage, $request->query->all()));
    }
}
