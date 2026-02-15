Feature: Currency Exchange
  In order to convert between currencies
  As a user
  I need to be able to exchange currencies through REST API

  Background:
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario: Full exchange flow - payin CHF, exchange to EUR, payout EUR
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 0       | Baslerstrasse 60 | Zürich | 8048 | CH      |
    # Step 1: Payin 1000 CHF
    When I send a POST request to "/api/transactions/payin" with body:
    """
      {
        "amount": "1000",
        "name": "Payin CHF",
        "date": "2024-07-12T09:08:32.563Z",
        "currency": "CHF",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "executed" should be true
    And the JSON node "type" should be equal to the string "payin"
    And the JSON node "currency" should be equal to the string "CHF"
    # Step 2: Exchange CHF to EUR (rate 1.1)
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a POST request to "/api/currency_exchanges" with body:
    """
      {
        "fromCurrency": "CHF",
        "toCurrency": "EUR",
        "fromAmount": "1000",
        "date": "2024-07-12T09:08:32.563Z",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "executed" should be true
    And the JSON node "fromCurrency" should be equal to the string "CHF"
    And the JSON node "toCurrency" should be equal to the string "EUR"
    And the JSON node "toAmount" should be equal to "1100"
    # Step 3: Payout 1100 EUR
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a POST request to "/api/transactions/payout" with body:
    """
      {
        "amount": "1100",
        "name": "Payout EUR",
        "date": "2024-07-12T09:08:32.563Z",
        "currency": "EUR",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "type" should be equal to the string "payout"
    And the JSON node "currency" should be equal to the string "EUR"
    Given I add "Content-Type" header equal to "application/merge-patch+json"
    When I send a PATCH request to "/api/transactions/4/payout/execute" with body:
    """
      {

      }
    """
    Then the response status code should be 200
    And the JSON node "executed" should be true
    # Verify: CHF account shows +1000 (payin) and -1000 (exchange_out)
    When I send a GET request to "/api/transactions?currency=CHF"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements
    # Verify: EUR account shows +1100 (exchange_in) and -1100 (payout)
    When I send a GET request to "/api/transactions?currency=EUR"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements
