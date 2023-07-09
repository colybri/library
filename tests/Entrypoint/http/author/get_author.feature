Feature: Retrieve an author
    In order to prove that library application is able to retrieve an author from id
    As a user
    I need to check the author attributes and application response

  Scenario: Retrieving an existent author
    Given the environment with fixtures
    When I request "/v1/author/09b87ec5-59d7-49a4-96ff-dcbfe92f5b43"
    Then the response code is 200
    And the response reason phrase is "OK"
    And the response body contains JSON:
      """
        {
            "id": "09b87ec5-59d7-49a4-96ff-dcbfe92f5b43",
            "firstName": "Autor ficticio",
            "lastName": null,
            "countryId": "3cfc35e3-84f5-4589-b164-d6bf50ff02bc",
            "isPseudonymOf": null,
            "bornAt": 1900,
            "deathAt": 1987
        }
      """

  Scenario: Retrieving an author with bad parameters
    When I request "/v1/author/non-id"
    Then the response code is 400
    And the response reason phrase is "Bad Request"
    And the response body contains JSON:
    """
      {
        "error": "The following assertions failed",
        "messages": [
            "Value \"non-id\" is not a valid UUID."
            ]
      }
    """

  Scenario: Retrieving an non existent author
    When I request "/v1/author/47f9587f-423d-48dd-bba3-99897f5c91f3"
    Then the response code is 404
    And the response reason phrase is "Not Found"
    And the response body contains JSON:
    """
      {
        "error": "Author whit id:47f9587f-423d-48dd-bba3-99897f5c91f3 does not exist on repository"
      }
    """
