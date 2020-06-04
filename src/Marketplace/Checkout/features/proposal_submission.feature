Feature: Proposal Submission
  In order to allow a JobSeeker and a Client to dispute a Checkout
  we need to allow the Parties to submit proposal on an opened Checkout

  Scenario: Not allowed JobSeeker cannot submit proposal
    Given An opened Checkout
    When A job seeker that is not known in the Parties submits a proposal
    Then The submitting job seeker should not be allowed to submit proposal

  Scenario: Not allowed Client cannot submit proposal
    Given An opened Checkout
    When A client that is not known in the Parties submits a proposal
    Then The submitting client should not be allowed to submit proposal

  Scenario: JobSeeker cannot submit proposal on an already agreed Checkout
    Given An already agreed Checkout
    When The job seeker submits a proposal
    Then The submission should fail due to Checkout being already agreed

  Scenario: Client cannot submit proposal on an already agreed Checkout
    Given An already agreed Checkout
    When The client submits a proposal
    Then The submission should fail due to Checkout being already agreed

  Scenario: JobSeeker cannot submit proposal multiple times in a row
    Given A Checkout pending for Client Agreement
    When The job seeker submits a proposal
    Then The submission should fail due to Job seeker submitting multiple times in a row

  Scenario: Client cannot submit proposal multiple times in a row
    Given A Checkout pending for JobSeeker Agreement
    When The client submits a proposal
    Then The submission should fail due to Client submitting multiple times in a row

  Scenario: JobSeeker can submit proposal
    Given A Checkout pending for JobSeeker Agreement
    When The job seeker submits a proposal
    Then Submission should be successful
    And The "proposal_was_submitted_by_job_seeker" Domain Event should be raised

  Scenario: Client can submit proposal
    Given A Checkout pending for Client Agreement
    When The client submits a proposal
    Then Submission should be successful
    And The "proposal_was_submitted_by_client" Domain Event should be raised
