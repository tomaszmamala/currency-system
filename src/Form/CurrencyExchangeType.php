<?php

namespace App\Form;

use App\Entity\BusinessPartner;
use App\Entity\CurrencyExchange;
use App\Enums\CurrencyEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyExchangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('businessPartner', EntityType::class, [
                'class' => BusinessPartner::class,
                'choice_label' => 'name',
            ])
            ->add('fromCurrency', EnumType::class, [
                'class' => CurrencyEnum::class,
            ])
            ->add('toCurrency', EnumType::class, [
                'class' => CurrencyEnum::class,
            ])
            ->add('fromAmount')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CurrencyExchange::class,
        ]);
    }
}
