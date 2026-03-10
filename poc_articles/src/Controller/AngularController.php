<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AngularController extends AbstractController
{
    #[Route('/app/{route}', name: 'app_angular', requirements: ['route' => '.*'], defaults: ['route' => ''])]
    public function index(): Response
    {
        $indexPath = $this->getParameter('kernel.project_dir') . '/public/app/index.html';

        if (!file_exists($indexPath)) {
            throw $this->createNotFoundException('Le build Angular est introuvable. Avez-vous lancé "ng build" ?');
        }

        return new Response(
            file_get_contents($indexPath), 
            200, 
            ['Content-Type' => 'text/html']
        );
    }
}