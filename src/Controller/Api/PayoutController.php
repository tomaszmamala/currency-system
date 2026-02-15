<?php

namespace App\Controller\Api;

use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;
use App\Service\BalanceManager;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PayoutController extends AbstractController
{
    public function __construct(private readonly BalanceManager $balanceManager)
    {
    }

    public function __invoke(Transaction $transaction)
    {
        if ($transaction->getDate() > (new DateTime())) {
            throw new TransactionExecutionException('Payout transaction date can be only on the current date');
        }

        $currencyAccount = $this->balanceManager->getOrCreateAccount(
            $transaction->getBusinessPartner(),
            $transaction->getCurrency()
        );

        if (!$this->balanceManager->hasEnoughMoneyForPayout(
            $currencyAccount,
            $transaction->getAmount()
        )) {
            throw new TransactionExecutionException('You do not have enough money for a payout');
        }

        $transaction->setType(TransactionTypeEnum::PAYOUT);

        return $transaction;
    }
}