Connect-2-Moodle
================

The world's most flexible, live virtual classroom solution (Adobe® Connect™) meets the world's most popular LMS (Moodle®) in a seamless integration from Refined Data Solutions!

With the Connect-2-Moodle plugin, users can enter Adobe Connect meetings, launch Presenter and Captivate presentations and watch event recordings with a single click from inside of Moodle.

Connect-2-Moodle provides a Single Sign On (SSO) environment in which information passes effortlessly in both directions between the two platforms so users cannot tell where Moodle ends and Adobe Connect begins. 

Installing your Connect2Moodle plugin:
======================================

Please install plugins in following order:

1. Install moodle-local_refinedservices plugin: [local_refinedservices](https://github.com/refineddata/moodle-local_refinedservices)
2. Install moodle-local_connect plugin: [local_connect](https://github.com/refineddata/moodle-local_connect)
3. Install moodle-mod_connectmeeting plugin: [mod_connectmeeting](https://github.com/refineddata/moodle-mod_connectmeeting)
4. Install moodle-mod_connectquiz plugin: [mod_connectquiz](https://github.com/refineddata/moodle-mod_connectquiz)
5. Install moodle-mod_connectslide plugin: [mod_connectslide](https://github.com/refineddata/moodle-mod_connectslide)
6. Install moodle-mod_rtrecording plugin: [mod_rtrecording](https://github.com/refineddata/moodle-mod_rtrecording)
7. Install moodle-mod_rtvideo plugin: [mod_rtvideo](https://github.com/refineddata/moodle-mod_rtvideo)
8. Install moodle-filter_connect plugin: [filter_connect](https://github.com/refineddata/moodle-filter_connect)
9. Install moodle-auth_connect plugin: [auth_connect](https://github.com/refineddata/moodle-auth_connect)
10. Install moodle-block_refinedtools plugin: [block_refinedtools](https://github.com/refineddata/moodle-block_refinedtools)
11. Install moodle-local_reminders plugin: [local_reminders](https://github.com/refineddata/moodle-local_reminders)

Setting up Refined Services:
============================

Refined Services is an integration service to manage the integration between your LMS and Adobe Connect.
This service provides added security, flexibility and simplifies the process for updates and maintenance by keeping all data centralized.

It is essential that you follow the instructions below on your LMS to ensure all your users and content are updated in Refined Services:

1. Go to Site admin >> Plugins >> Local plugins >> Refined Services
  1. Click first on "Create Connect Account" this creates your Refined Services account with trial subscription.
  2. Manually input your Adobe Connect credentials then click "Update Adobe Connect Settings".
2. Sync existing data with Adobe Connect:
  1. Click "Sync Users with Adobe Connect" and wait for the update to stop running.
  2. Click on "Sync Courses with Adobe Connect" and wait for the update to stop running.
  3. Click on "Sync Connect Activities with Adobe Connect" and wait for the update to stop running.

For more information and a User Manual please visit our support site:
http://support.refineddata.com/hc/en-us/articles/203481340-Connect-2-Moodle-Plugin-by-Refined-Training

**Note: these plugins require Refined Services subscription. Please contact Refined Data for more information and pricing: http://www.refineddata.com/contact/**
