<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SsoController extends AbstractController
{
    #[Route('/sso/to-symfony', name: 'sso_to_symfony')]
    public function toSymfony(Request $request, JWTEncoderInterface $jwtEncoder, EntityManagerInterface $em, Security $security): Response
    {
        $token = $request->query->get('token');
        if ($token) {
            try {
                $payload = $jwtEncoder->decode($token);
                $email = $payload['username'] ?? $payload['email'] ?? null;
                
                if ($email) {
                    $user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
                    if ($user) {
                        $security->login($user, \App\Security\AdminAuthenticator::class, 'main');
                        return $this->redirectToRoute('admin');
                    }
                }
            } catch (\Exception $e) {
                // S'il y a la moindre erreur, elle s'affichera à l'écran au lieu de te renvoyer au login
                dd('Erreur SSO : ' . $e->getMessage()); 
            }
        }
        return $this->redirectToRoute('app_login'); 
    }

    #[Route('/sso/to-angular', name: 'sso_to_angular')]
    public function toAngular(Security $security, JWTTokenManagerInterface $jwtManager): Response
    {
        $user = $security->getUser();

        $frontUrl = $this->getParameter('app.angular_front_url');
        
        if ($user) {
            $token = $jwtManager->create($user);
            
            // On le redirige vers le front Angular avec le token dans l'URL
            return $this->redirect($frontUrl . '/?token=' . $token);
        }

        return $this->redirect($frontUrl);
    }

    #[Route('/sso/logout-redirect', name: 'sso_logout_redirect')]
    public function logoutRedirect(): Response
    {
        // On récupère l'URL d'Angular depuis les paramètres
        $frontUrl = $this->getParameter('app.angular_front_url');
        
        // On redirige proprement vers le front-office
        return $this->redirect($frontUrl . '/?action=logout');
    }
}