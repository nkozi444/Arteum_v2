<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('user/dashboard.html.twig', [
            'title' => 'User Dashboard',
        ]);
    }
}
