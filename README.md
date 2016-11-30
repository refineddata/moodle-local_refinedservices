Installing your Connect2Moodle plugin:
======================================

     1. Install moodle-local_refinedservices plugin: https://github.com/refineddata/moodle-local_refinedservices
     2. Install moodle-local_connect         plugin: https://github.com/refineddata/moodle-local_connect
     3. Install moodle-mod_connectmeeting    plugin: https://github.com/refineddata/moodle-mod_connectmeeting
     4. Install moodle-mod_connectquiz       plugin: https://github.com/refineddata/moodle-mod_connectquiz
     5. Install moodle-mod_connectslide      plugin: https://github.com/refineddata/moodle-mod_connectslide
     6. Install moodle-mod_rtrecording       plugin: https://github.com/refineddata/moodle-mod_rtrecording
     7. Install moodle-mod_rtvideo           plugin: https://github.com/refineddata/moodle-mod_rtvideo
     8. Install moodle-filter_connect        plugin: https://github.com/refineddata/moodle-filter_connect
     9. Install moodle-auth_connect          plugin: https://github.com/refineddata/moodle-auth_connect
    10. Install moodle-block_refinedtools    plugin: https://github.com/refineddata/moodle-block_refinedtools
    11. Install moodle-local_reminders       plugin: https://github.com/refineddata/moodle-local_reminders

Setting up Refined Services:
============================

Refined Services is an integration service to manage the integration between your LMS and Adobe Connect.
This service provides added security, flexibility and simplifies the process for updates and maintenance by keeping all data centralized.

It is essential that you follow the instructions below on your LMS to ensure all your users and content are updated in Refined Services:

    1. Go to Site admin >> Plugins >> Local plugins >> Refined Services
    Click first on "Create Connect Account" this creates your Adobe Connect account in RS. (credentials to be added later)
    Manually input your Adobe Connect credentials then click "Update Adobe Connect Settings".
    2. Go to Site admin >> Plugins >> Filters >> Connect
    Make one change to the page (suggestion: Mouse-over for students).
    Then save and Moodle will then trigger an update to RS for all AC filters (once complete the setting can be changed back and saved again)
    3. Return to Site admin >> Plugins >> Local plugins >> Refined Services
        a. Click "Sync Users with Adobe Connect" and wait for the update to stop running.
        b. Click on "Sync Courses with Adobe Connect" and wait for the update to stop running.
        c. Click on "Sync Connect Activities with Adobe Connect" and wait for the update to stop running.

For more information and a User Manual please visit our support site:
http://support.refineddata.com/hc/en-us/categories/200134280-Refined-Training
