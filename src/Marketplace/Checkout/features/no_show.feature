Feature: No Show notification
  Whenever a job seeker has not submitted any proposal after 7 days from the start of the shift,
  We want the system to notify a NoShow for the FF

  Scenario: Not allowed to notify NoShow on an already agreed Checkout
    Given An already agreed Checkout
    When The system marks the Checkout as NoShow
    Then NoShow should fail

  Scenario: Not allowed to notify NoShow when a Shift is not yet ready for Checkout
    Given A checkout not ready yet to be modified
    When The system marks the Checkout as NoShow
    Then NoShow should fail

  # Not sure about this, to be checked
  Scenario: Not allowed to notify NoShow when a Checkout has a Proposal
    Given A Checkout pending for Client Agreement
    When The system marks the Checkout as NoShow
    Then NoShow should fail

  # Not sure about this, to be checked
  Scenario: Not allowed to notify NoShow when a Checkout has a Proposal
    Given A Checkout pending for JobSeeker Agreement
    When The system marks the Checkout as NoShow
    Then NoShow should fail

  Scenario: Not allowed to notify NoShow before 7 days of inactivity by job seeker
    Given A Checkout for a Shift started less than 7 days ago
    When The system marks the Checkout as NoShow
    Then NoShow should fail

  Scenario: A Checkout is marked as NoShow after 7 days of inactivity by job seeker
    Given A Checkout inactive for more than 7 days
    When The system marks the Checkout as NoShow
    And The "job_seeker_was_marked_as_no_show" Domain Event should be raised
