<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('', name: 'home', methods: ['GET'])]
    public function homePage(): Response
    {
        return new Response(
            '<html>
            <body>
                Welcome :)
            </body>
            </html>'
        );
    }
}
