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
        if ($this->esNumericoConLetra($nif)) {
            $nif = str_pad($nif, 9, '0', STR_PAD_LEFT);
        }
        $repo = $this->erroldaEntityManager->getRepository(Habitante::class);
        $habitante = $repo->findOneByDni($nif);
        return $habitante;
    }

    private function esNumericoConLetra($cadena): bool  {
        $cadenaSinUltima = substr($cadena, 0, -1);
        return is_numeric($cadenaSinUltima);
    }
}
