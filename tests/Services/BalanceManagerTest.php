<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\CurrencyAccount;
use App\Enums\CurrencyEnum;
use App\Service\BalanceManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BalanceManagerTest extends WebTestCase
{
    private BalanceManager $balanceManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = static::getContainer();

        $this->balanceManager = $container->get(BalanceManager::class);
    }

    public function testPayinBalanceChange(): void
    {
        $currencyAccount = $this->createCurrencyAccount();

        $this->balanceManager->increaseBalance($currencyAccount, '1000');

        $this->assertEquals('11000', $currencyAccount->getBalance());
    }

    public function testPayoutBalanceChange(): void
    {
        $currencyAccount = $this->createCurrencyAccount();

        $this->balanceManager->decreaseBalance($currencyAccount, '1000');

        $this->assertEquals('9000', $currencyAccount->getBalance());
    }

    public function testHasEnoughMoneyForPayout(): void
    {
        $currencyAccount = $this->createCurrencyAccount();

        $this->assertTrue($this->balanceManager->hasEnoughMoneyForPayout($currencyAccount, '1000'));
        $this->assertFalse($this->balanceManager->hasEnoughMoneyForPayout($currencyAccount, '11000'));
    }

    private function createCurrencyAccount(): CurrencyAccount
    {
        $currencyAccount = new CurrencyAccount();
        $currencyAccount->setCurrency(CurrencyEnum::CHF);
        $currencyAccount->setBalance('10000');

        return $currencyAccount;
    }
}
