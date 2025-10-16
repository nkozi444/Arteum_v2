<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank: Symfony intercepts the /logout route automatically.
        // Do NOT put any code here.
    }
}
