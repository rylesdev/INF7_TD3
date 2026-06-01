<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    #[Route('/locale/{locale}', name: 'app_set_locale', requirements: ['locale' => 'fr|en'])]
    public function setLocale(string $locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);

        $referer = $request->headers->get('referer', $this->generateUrl('app_home'));
        $response = $this->redirect($referer);

        // Cookie survives logout (1 year)
        $response->headers->setCookie(
            Cookie::create('_locale', $locale, time() + 365 * 24 * 3600, '/', null, false, false)
        );

        return $response;
    }
}
