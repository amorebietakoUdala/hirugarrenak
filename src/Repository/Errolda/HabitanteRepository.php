<?php

namespace App\Repository\Errolda;

use App\Entity\Errolda\Habitante;
use Doctrine\ORM\EntityRepository;

/**
 * @method Habitante|null find($id, $lockMode = null, $lockVersion = null)
 * @method Habitante|null findOneBy(array $criteria, array $orderBy = null)
 * @method Habitante[]    findAll()
 * @method Habitante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HabitanteRepository extends EntityRepository
{
    public function findOneByDni($value): ?Habitante
    {

        $qb = $this->createQueryBuilder('h')
            ->andWhere('h.dni = :dni')
            ->setParameter('dni', $value)
            ->andWhere('h.activo = :activo')
            ->setParameter('activo', 1)
            ->andWhere('h.codigoVariacion != :codigoVariacion')
            ->setParameter('codigoVariacion', 'B');
        ;
        return $qb->getQuery()->getOneOrNullResult();

    }
}
