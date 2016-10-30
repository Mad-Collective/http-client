Feature: Simulate the interaction with an REST API
  As a developer
  In order to test the integration with REST APIs
  I Should be able to perform real http requests

  Scenario: I can list the users
    Given I create the request for "mmock" "list_users"
    When I execute the request
    And Parse the body as json
    Then I should have a status code of "200"
    And The contents should be:
      | id | first_name | last_name |
      | 1  | John       | Doe       |
      | 2  | Jane       | Roe       |

  Scenario: I can get a single user
    Given I create the request for "mmock" "get_user" with:
      | user_id |
      | 1       |
    When I execute the request
    And Parse the body as json
    Then I should have a status code of "200"
    And The contents should be:
      | id | first_name | last_name |
      | 1  | John       | Doe       |

  Scenario: I can put a a user
    Given I create the request for "mmock" "put_user" with:
      | user_id | first_name | last_name |
      | 1       | Tom        | Foobar    |
    When I execute the request
    And Parse the body as json
    Then I should have a status code of "200"
    And The contents should be:
      | id | first_name | last_name |
      | 1  | Tom        | Foobar    |

  Scenario: I can create a user
    Given I create the request for "mmock" "create_user" with:
      | first_name | last_name |
      | Foo        | Bar       |
    When I execute the request
    And Parse the body as json
    Then I should have a status code of "201"
    And The contents should be:
      | id | first_name | last_name |
      | 3  | Foo        | Bar       |

  Scenario: I can update a user
    Given I create the request for "mmock" "update_user" with:
      | user_id |
      | 1       |
    And I configure the request with a json body of:
      | last_name |
      | Bar       |
    When I execute the request
    And Parse the body as json
    Then I should have a status code of "200"
    And The contents should be:
      | id | first_name | last_name |
      | 1  | John       | Bar       |

  Scenario: I can delete a user
    Given I create the request for "mmock" "delete_user" with:
      | user_id |
      | 1       |
    When I execute the request
    Then I should have a status code of "204"