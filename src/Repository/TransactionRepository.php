<?php

namespace App\Repository;

use App\Entity\BusinessPartner;
use App\Entity\Transaction;
use App\Enums\CurrencyEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByBusinessPartner(BusinessPartner $businessPartner, ?CurrencyEnum $currency = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.businessPartner = :businessPartner')
            ->setParameter('businessPartner', $businessPartner);

        if ($currency !== null) {
            $qb->andWhere('t.currency = :currency')
                ->setParameter('currency', $currency);
        }

        return $qb->getQuery()->getResult();
    }
}
