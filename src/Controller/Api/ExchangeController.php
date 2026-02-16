<?php

namespace App\Controller\Api;

use App\Entity\CurrencyExchange;
use App\Service\ExchangeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExchangeController extends AbstractController
{
    public function __construct(
        private readonly ExchangeManager $exchangeManager
    ) {
    }

    public function __invoke(CurrencyExchange $data)
    {
        $this->exchangeManager->execute($data);

        return $data;
    }
}
