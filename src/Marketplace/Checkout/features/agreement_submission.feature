Feature: Agreement Submission
  In order to allow a JobSeeker to get paid for a worked shift
  we need to allow the Parties to submit an agreement on a Checkout

  Scenario: Not allowed JobSeeker cannot submit agreement
    Given An opened Checkout
    When A job seeker that is not known in the Parties submits agreement
    Then The job seeker should not be allowed to submit agreement

  Scenario: Not allowed Client cannot submit proposal
    Given An opened Checkout
    When A client that is not known in the Parties submits agreement
    Then The client should not be allowed to submit agreement

  Scenario: JobSeeker cannot submit agreement on an already agreed Checkout
    Given An already agreed Checkout
    When The job seeker submits the agreement
    Then The agreement submission should fail due to Checkout being already agreed

  Scenario: Client cannot submit agreement on an already agreed Checkout
    Given An already agreed Checkout
    When The client submits the agreement
    Then The agreement submission should fail due to Checkout being already agreed

  Scenario: JobSeeker cannot submit agreement on a Checkout without proposals yet
    Given A Checkout with no proposals
    When The job seeker submits the agreement
    Then The agreement submission should fail due to Checkout not having proposals

  Scenario: Client cannot submit agreement on a Checkout without proposals yet
    Given A Checkout with no proposals
    When The client submits the agreement
    Then The agreement submission should fail due to Checkout not having proposals

  Scenario: JobSeeker cannot agree on own proposal
    Given A Checkout pending for Client Agreement
    When The job seeker submits the agreement
    Then The submission should fail due to JobSeeker submitting agrement on own proposal

  Scenario: Client cannot agree on own proposal
    Given A Checkout pending for JobSeeker Agreement
    When The client submits the agreement
    Then The submission should fail due to Client submitting agrement on own proposal

  Scenario: JobSeeker can submit agreement
    Given A Checkout pending for JobSeeker Agreement
    When The job seeker submits the agreement
    Then Agreement submission should be successful
    And The "agreement_was_submitted_by_job_seeker" Domain Event should be raised

  Scenario: Client can submit agreement
    Given A Checkout pending for Client Agreement
    When The client submits the agreement
    Then Agreement submission should be successful
    And The "agreement_was_submitted_by_client" Domain Event should be raised
