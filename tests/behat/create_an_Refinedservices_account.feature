@refined_training @local_refinedservices @local @create_refined_services_account
  Feature: create_refined_services_account
    In order to create a Refined Services Account
    As an admin
    I need to navigate to Refined Services and create an account

  Background:
    Given I log in as "admin"
    Then I press "Add a new course"
    And I set the following fields to these values:
     | Course full name | Course 1 |
     | Course short name | C1 |
     | Course category | Miscellaneous |
    And I press "Save changes"
    And I press "Return to course"
    And I follow "Turn editing on"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Local plugins" node
    And I follow "Refined Services"
    And I press "Create Connect Account"
    Then I should see "The account is activated and will be expired"
    And I set the following fields to these values:
     | Protocol | https:// |
     | AC server hostname | rds.adobeconnect.com |
     | AC account ID | 20566794 |
     | AC admin login | moodleadmin |
     | AC admin password | assent6245 |
     | Username prefix | dev_test- |
    And I log out



  @javascript
  Scenario: Check if Refined Services account is created
    Given I log in as "admin"
    And I am on homepage
    Then I should see "Course 1"
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Local plugins" node
    And I follow "Refined Services"
    And I log out

