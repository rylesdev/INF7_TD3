<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\EvaluationProprietaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AnnonceRepository $annonceRepo, EvaluationProprietaireRepository $evalRepo): Response
    {
        $annonces = $annonceRepo->findDisponibles();
        $annoncesRecentes = array_slice($annonces, 0, 3);

        return $this->render('home/index.html.twig', [
            'annonces_recentes'      => $annoncesRecentes,
            'proprietaires_notes'    => $evalRepo->findTopRated(3),
        ]);
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(AnnonceRepository $annonceRepo): Response
    {
        $annonces = $annonceRepo->findDisponibles();
        $response = $this->render('home/sitemap.xml.twig', ['annonces' => $annonces]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots', defaults: ['_format' => 'txt'])]
    public function robots(): Response
    {
        $response = $this->render('home/robots.txt.twig');
        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }
}
