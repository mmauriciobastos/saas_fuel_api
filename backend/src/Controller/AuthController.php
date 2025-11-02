<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): Response
    {
        // This method can be blank - it's never executed because the firewall intercepts this route.
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
