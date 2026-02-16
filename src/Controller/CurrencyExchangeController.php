<?php

namespace App\Controller;

use App\Entity\CurrencyExchange;
use App\Form\CurrencyExchangeType;
use App\Service\ExchangeManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/exchange')]
class CurrencyExchangeController extends AbstractController
{
    #[Route('/new', name: 'app_exchange_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ExchangeManager $exchangeManager,
    ): Response {
        $exchange = new CurrencyExchange();
        $form = $this->createForm(CurrencyExchangeType::class, $exchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($exchange);
                $exchangeManager->execute($exchange);

                $request->getSession()->getFlashBag()->add('success',
                    sprintf('Exchange executed: %s %s -> %s %s (rate %s)',
                        $exchange->getFromAmount(), $exchange->getFromCurrency()->value,
                        $exchange->getToAmount(), $exchange->getToCurrency()->value,
                        $exchange->getExchangeRate()
                    )
                );

                return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
            } catch (Exception $exception) {
                $request->getSession()->getFlashBag()->add('danger', $exception->getMessage());
            }
        }

        return $this->render('exchange/new.html.twig', [
            'form' => $form,
        ]);
    }
}
