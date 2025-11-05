<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_HIRUGARRENAK')]
final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/{_locale}')]
    public function index(): Response
    {
        return $this->redirectToRoute('third_index');
    }
}
