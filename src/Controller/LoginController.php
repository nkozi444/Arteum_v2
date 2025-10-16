<?php
// src/Controller/LoginController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET','POST'])]
    public function login(
        Request $request,
        UserRepository $users,
        UserPasswordHasherInterface $hasher
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = (string) $request->request->get('email', '');
            $password = trim((string) $request->request->get('password', ''));
            $csrf = (string) $request->request->get('_csrf_token', '');

            // ===== DEV-ONLY HARDCODE (REMOVE WHEN DONE) =====
            if ($this->getParameter('kernel.environment') === 'dev') {
                // <-- Replace these with the test credentials you want to use:
                // Admin test credentials:
                if ($email === 'admin@local.test' && $password === 'adminpass') {
                    // Direct redirect to admin/tour template (no authentication)
                    return $this->redirectToRoute('app_tour_index');
                }

                // Regular user test credentials:
                if ($email === 'user@local.test' && $password === 'userpass') {
                    // Direct redirect to user dashboard template (no authentication)
                    return $this->redirectToRoute('user_dashboard');
                }

                // (Optional) If you want to use your real admin email instead:
                // if ($email === 'admin@arteum.com' && $password === 'adminpass') { ... }
            }
            // ===== END DEV-ONLY HARDCODE =====

            // Normal auth flow kept in place (if you later remove dev bypass)
            if (!$this->isCsrfTokenValid('authenticate', $csrf)) {
                $error = 'Invalid form token.';
            } else {
                $user = $users->findOneBy(['email' => $email]);

                if ($user && $hasher->isPasswordValid($user, $password)) {
                    // If you later want to programmatically login, add $security->login(...) here
                    // and ensure you have an authenticator configured for the 'main' firewall.
                    if (in_array('ROLE_ADMIN', $user->getRoles() ?? [], true)) {
                        return $this->redirectToRoute('app_tour_index');
                    }

                    return $this->redirectToRoute('user_dashboard');
                }

                $error = 'Invalid email or password';
            }
        }

        return $this->render('site/login.html.twig', [
            'error' => $error,
        ]);
    }
}
