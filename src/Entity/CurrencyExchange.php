<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\ExchangeController;
use App\Enums\CurrencyEnum;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'currency_exchanges')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            controller: ExchangeController::class,
            denormalizationContext: ['groups' => ['ExchangeCreate']]
        ),
    ],
    normalizationContext: ['groups' => ['ExchangeView']]
)]
class CurrencyExchange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['ExchangeView'])]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 3, enumType: CurrencyEnum::class)]
    #[Assert\Type(type: CurrencyEnum::class)]
    #[Groups(['ExchangeView', 'ExchangeCreate'])]
    private CurrencyEnum $fromCurrency;

    #[ORM\Column(type: Types::STRING, length: 3, enumType: CurrencyEnum::class)]
    #[Assert\Type(type: CurrencyEnum::class)]
    #[Groups(['ExchangeView', 'ExchangeCreate'])]
    private CurrencyEnum $toCurrency;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    #[Groups(['ExchangeView', 'ExchangeCreate'])]
    private string $fromAmount;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['ExchangeView'])]
    private string $toAmount = '0';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    #[Groups(['ExchangeView'])]
    private string $exchangeRate = '0';

    #[ORM\ManyToOne(targetEntity: BusinessPartner::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['ExchangeView', 'ExchangeCreate'])]
    private BusinessPartner $businessPartner;

    #[ORM\OneToOne(targetEntity: Transaction::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['ExchangeView'])]
    private ?Transaction $sellTransaction = null;

    #[ORM\OneToOne(targetEntity: Transaction::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['ExchangeView'])]
    private ?Transaction $buyTransaction = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['ExchangeView', 'ExchangeCreate'])]
    private DateTimeImmutable $date;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['ExchangeView'])]
    private bool $executed = false;

    public function __construct()
    {
        $this->date = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFromCurrency(): CurrencyEnum
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(CurrencyEnum $fromCurrency): void
    {
        $this->fromCurrency = $fromCurrency;
    }

    public function getToCurrency(): CurrencyEnum
    {
        return $this->toCurrency;
    }

    public function setToCurrency(CurrencyEnum $toCurrency): void
    {
        $this->toCurrency = $toCurrency;
    }

    public function getFromAmount(): string
    {
        return $this->fromAmount;
    }

    public function setFromAmount(string $fromAmount): void
    {
        $this->fromAmount = $fromAmount;
    }

    public function getToAmount(): string
    {
        return $this->toAmount;
    }

    public function setToAmount(string $toAmount): void
    {
        $this->toAmount = $toAmount;
    }

    public function getExchangeRate(): string
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(string $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function getBusinessPartner(): BusinessPartner
    {
        return $this->businessPartner;
    }

    public function setBusinessPartner(BusinessPartner $businessPartner): void
    {
        $this->businessPartner = $businessPartner;
    }

    public function getSellTransaction(): ?Transaction
    {
        return $this->sellTransaction;
    }

    public function setSellTransaction(?Transaction $sellTransaction): void
    {
        $this->sellTransaction = $sellTransaction;
    }

    public function getBuyTransaction(): ?Transaction
    {
        return $this->buyTransaction;
    }

    public function setBuyTransaction(?Transaction $buyTransaction): void
    {
        $this->buyTransaction = $buyTransaction;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function setExecuted(bool $executed): void
    {
        $this->executed = $executed;
    }
}
