<?php

namespace App\Services;

use App\Entity\Errolda\Habitante;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_HIRUGARRENAK')]
final class ErroldaApiService extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $erroldaEntityManager
    ) {}

    public function getActiveCitizenByNif(string $nif): ?Habitante
    {
        $repo = $this->erroldaEntityManager->getRepository(Habitante::class);
        $habitante = $repo->findOneByDni($nif);
        return $habitante;
    }

}
