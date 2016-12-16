<?php

/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */
$string['pluginname'] = 'Refined Services';

// Settings
$string['settings'] = 'Refined Services';

$string['refinedservices_host'] = 'Refined Services Host';
$string['configrefinedservices_host'] = 'Refined Services Host';

$string['connect_service_account'] = 'Connect Service Account';
$string['explainconnect_service_account'] = 'These settings are for <strong>Connect Service</strong>.';
$string['connect_service_username'] = 'Connect Service Username';
$string['configconnect_service_username'] = 'Connect Service Username.';
$string['connect_service_password'] = 'Connect Service Password';
$string['configconnect_service_password'] = 'Connect Service Password.';
$string['report_service_account'] = 'Report Service Account';
$string['explainreport_service_account'] = 'These settings are for <strong>Report Service</strong>.';
$string['report_service_username'] = 'Report Service Username';
$string['configreport_service_username'] = 'Report Service Username.';
$string['report_service_password'] = 'Report Service Password';
$string['configreport_service_password'] = 'Report Service Password.';

$string['connect_credentials_form_title']   = "Adobe Connect Login Credentials Form";
$string['require_connect_credentials']      = "Your Adobe Connect login credentials are required for this action, you will be redirected to a form to enter them.";
$string['connect_credentials_form_header']  = "Please enter your Adobe Connect credentials to reset your password in Refined Services.   If you need assistance please contact: support@refineddata.com";
$string['connect_credentials_form_fail']    = "The Adobe Connect login credentials you entered are incorrect.  Please try again.";
$string['connect_credentials_form_success'] = "Your Adobe Connect login credentials have been updated in our system.  You will be redirected to your previous page.";

$string['created_by_rs_form_header'] = "Please enter the ID of the user to be created by Refined Services and to have their password reset";
$string['externalids'] = "User Id's ( seperated by comma )";
$string['change_rs_form_success'] = "Users where successfully updated";
$string['change_to_rs_formtitle'] = 'Change Users to Created BY RS';
$string['changed_by_rs'] = 'Change users to Create by RS';
$string['account_expired_soon_disabled'] = 'Your refined services account has expired and will soon be disabled.  Please check Pluging > Local > Refined Services for more detail or contact your system administrator';

$string['cachesettings'] = 'Refined Services Cache';
$string['connect_cachetime'] = 'Connect Calls Cache Time (Seconds)';
$string['report_cachetime'] = 'Report Calls Cache Time (Seconds)';

$string['connect_error_nonadmin'] = 'Page temporarily unavailable. If issue persists please contact {$a->contact}<br /><br />Error code: {$a->code}<br />Please try again in a few minutes.';

// Exceptions
$string['refinedserviceisdown'] = 'Refined service is down.';
$string['adobeserverisdown'] = 'Adobe server is down.';
$string['jasperserverisdown'] = 'Jasper server is down.';
$string['unauthorizedreportservice'] = 'Unauthorized of Report service.';
$string['unauthorizedconnectservice'] = 'Unauthorized of Connect service.';
$string['unauthorizedadobeconnect'] = 'Unauthorized of Adobe Connect.';

$string['cachedef_connectcall'] = 'Cache of connect calls to refined services';
$string['cachedef_reportcall'] = 'Cache of report calls to refined services';

$string['connect_service_credentials'] = 'Adobe Connect Server Information';

$string['refinedservices_disable_cache'] = 'Disable caching of calls to RS';

//Capabilities
$string['refinedservices:directacaccess'] = 'Direct AC access';
