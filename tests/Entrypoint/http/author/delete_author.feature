Feature: Delete an author
  In order to prove that library application is able to delete an author from id
  As a user
  I need to check the application response

  Scenario: Retrieving an author with bad parameters
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