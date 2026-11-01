Feature: Delete an author
  In order to prove that library application is able to delete an author from id
  As a user
  I need to check the application response

  Scenario: Deleting an existing author
    Given the environment with fixtures
    Given Given an authentificated user
    When I request "/v1/author/2edcd2aa-bc93-44dd-baa2-8fa2ce2a3cc4" using HTTP "DELETE"
    Then the response code is 204
    And the response reason phrase is "No Content"

  Scenario: Trying to delete an author with bad parameters
    When I request "/v1/author/non-id" using HTTP "DELETE"
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

  Scenario: Deleting an not existing author
    When I request "/v1/author/6016623c-4c30-4e50-9d44-d5621abac4b3" using HTTP "DELETE"
    Then the response code is 404
    And the response reason phrase is "Not Found"
    And the response body contains JSON:
    """
      {
          "error": "Author whit id: 6016623c-4c30-4e50-9d44-d5621abac4b3 does not exist on repository"
      }
    """