/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 * Converted to AMD by Ras 2016 July 13
 */
define(['jquery'], function ($) {

    var refinedservices = {
        init: function () {

            $(document).ready(function () {

                // PROGRESS
                function moveProgressBarUsers(percent) {
                    var progressTotal = (percent / 100) * $('#progress-wrap-users').width();
                    var animationLength = 2500;

                    $('#progress-bar-users').stop().animate({
                        left: progressTotal
                    }, animationLength);
                }

                function moveProgressBarCourses(percent) {
                    var progressTotal = (percent / 100) * $('#progress-wrap-courses').width();
                    var animationLength = 2500;

                    $('#progress-bar-courses').stop().animate({
                        left: progressTotal
                    }, animationLength);
                }

                function moveProgressBarActivities(percent) {
                    var progressTotal = (percent / 100) * $('#progress-wrap-activities').width();
                    var animationLength = 2500;

                    $('#progress-bar-activities').stop().animate({
                        left: progressTotal
                    }, animationLength);
                }

                var progressInterval = 0;

                function checkProgress() {
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/connect_update_status.php',
                        success: function (data) {
                            if (data.user) {
                                $('#update_users_message').html(data.user);
                            }
                            if (data.userpercentage) {
                                moveProgressBarUsers(data.userpercentage);
                            }
                            if (data.group) {
                                $('#update_courses_message').html(data.group);
                            }
                            if (data.grouppercentage) {
                                moveProgressBarCourses(data.grouppercentage);
                            }
                            if (data.activity) {
                                $('#update_activities_message').html(data.activity);
                            }
                            if (data.activitypercentage) {
                                moveProgressBarActivities(data.activitypercentage);
                            }
                            if (data.nocheck) {
                                if (progressInterval) {
                                    clearInterval(progressInterval);
                                    progressInterval = 0;
                                }
                            } else {
                                if (!progressInterval) {
                                    progressInterval = setInterval(checkProgress, 15000);
                                }
                            }
                        }
                    });
                }

                /* Get Connect Service Settings */
                if ($('#connect_service_account_comment').length) {
                    $('#connect_service_account_comment').addClass('rt-loading-image');
                    $('#id_s__refinedservices_host').prop('disabled', true);
                    $('#id_s__connect_service_username').prop('disabled', true);
                    $('#id_s__connect_service_password').prop('disabled', true);
                    $('.form-buttons').hide();
                    var vars = {};
                    vars['plugintype'] = $('#connect_service_account_comment').attr('data-plugintype');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/get_account.php',
                        data: vars,
                        success: function (data) {
                            console.log(data);
                            $('#connect_service_account_comment').removeClass('rt-loading-image');
                            $('#connect_service_account_comment').html(data.message);
                            var today = new Date();
                            var expireDate = new Date(data.expired);
                            if (today > expireDate) {
                                $('#connect_service_account_comment').removeClass('label-success');
                                $('#connect_service_account_comment').addClass('label-important');
                            }
                            if (data.username == '') {
                                if (data.credentialsinuse || data.domaininuse) {
                                    $('#connect_service_credentials_button').show();
                                    $('#connect_service_account_button').hide();
                                    $('#id_s__connect_service_username').removeProp('disabled');
                                    $('#id_s__connect_service_password').removeProp('disabled');
                                } else {
                                    $('#connect_service_credentials_button').hide();
                                    $('#connect_service_account_button').show();
                                }
                                $('#connect_service_update_users').hide();
                                $('#connect_service_update_courses').hide();
                                $('#connect_service_update_activities').hide();
                            } else {
                                $('#connect_service_account_button').hide();
                                $('#connect_service_credentials_button').hide();
//                    $('#connect_service_update_users').show();
//                    $('#connect_service_update_courses').show();
//                    $('#connect_service_update_activities').show();
                            }
                        }
                    });
                }

                /* Get Reports Service Settings */
                if ($('#reports_service_account_comment').length) {
                    $('#reports_service_account_comment').addClass('rt-loading-image');
                    $('#id_s__report_service_username').prop('disabled', true);
                    $('#id_s__report_service_password').prop('disabled', true);
                    $('#form-buttons').hide();
                    var vars = {};
                    vars['plugintype'] = $('#reports_service_account_comment').attr('data-plugintype');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/get_account.php',
                        data: vars,
                        success: function (data) {
                            $('#reports_service_account_comment').removeClass('rt-loading-image');
                            $('#reports_service_account_comment').html(data.message);
                            if (data.username == '') {
                                $('#reports_service_account_button').show();
                            } else {
                                $('#reports_service_account_button').hide();
                            }
                        }
                    });
                }

                /* Set Connect Service Settings */
                $('#connect_service_account_button').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    vars['plugintype'] = $('#connect_service_account_button').attr('data-plugintype');
                    $('#connect_service_account_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/create_account.php',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_account_comment').removeClass('rt-loading-image');
                            $('#connect_service_account_comment').html(data.message);
                            $('#id_s__connect_service_username').attr('value', data.username);
                            $('#id_s__connect_service_password').attr('value', data.password);
                            if (data.username != '') {
                                $('#connect_service_account_button').hide();
                            }
                        }
                    });
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/update_all_connect_settings.php',
                        success: function (data) {
                        }
                    });
                });

                /* Set Connect Service Settings */
                $('#connect_service_credentials_button').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    vars['plugintype'] = $('#connect_service_credentials_button').attr('data-plugintype');
                    vars['username'] = $('#id_s__connect_service_username').val();
                    vars['password'] = $('#id_s__connect_service_password').val();
                    $('#connect_service_account_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/check_credentials.php',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_account_comment').removeClass('rt-loading-image');
                            $('#connect_service_account_comment').html(data.message);
                            if (data.username) {
                                $('#connect_service_credentials_button').hide();
                                $('#id_s__connect_service_username').prop('disabled', true);
                                $('#id_s__connect_service_password').prop('disabled', true);
                                $('#id_s__connect_service_username').val(data.username);
                                $('#id_s__connect_service_password').val(data.password);
                            } else {
                                $('#connect_service_credentials_button').show();
                                $('#id_s__connect_service_username').val('');
                                $('#id_s__connect_service_password').val('');
                            }
                        }
                    });
                });

                /* Set Reports Service Settings */
                $('#reports_service_account_button').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    vars['plugintype'] = $('#reports_service_account_button').attr('data-plugintype');
                    $('#reports_service_account_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/create_account.php',
                        data: vars,
                        success: function (data) {
                            $('#reports_service_account_comment').removeClass('rt-loading-image');
                            $('#reports_service_account_comment').html(data.message);
                            $('#id_s__report_service_username').attr('value', data.username);
                            $('#id_s__report_service_password').attr('value', data.password);
                            if (data.username != '') {
                                $('#reports_service_account_button').hide();
                            }
                        }
                    });
                });

                /* Update Users on RS */
                $('#connect_service_update_users').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    $('#connect_service_update_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/connect_update.php?action=users',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_update_comment').removeClass('rt-loading-image');
                            $('#connect_service_update_comment').html(data.message);
                            setTimeout(checkProgress, 2000);
                        }
                    });
                });

                /* Update Courses on RS */
                $('#connect_service_update_courses').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    $('#connect_service_update_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/connect_update.php?action=coursegroups',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_update_comment').removeClass('rt-loading-image');
                            $('#connect_service_update_comment').html(data.message);
                            setTimeout(checkProgress, 2000);
                        }
                    });
                });

                /* Update Users on RS */
                $('#connect_service_update_activities').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    $('#connect_service_update_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/connect_update.php?action=activities',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_update_comment').removeClass('rt-loading-image');
                            $('#connect_service_update_comment').html(data.message);
                            setTimeout(checkProgress, 2000);
                        }
                    });
                });


                $('#connect_service_credentials').click(function (e) {
                    e.preventDefault();
                    var vars = {};
                    vars['protocol'] = $('#id_s__connect_protocol').val();
                    vars['server'] = $('#id_s__connect_server').val();
                    vars['account'] = $('#id_s__connect_account').val();
                    vars['admin_login'] = $('#id_s__connect_admin_login').val();
                    vars['admin_password'] = $('#id_s__connect_admin_password').val();
                    vars['prefix'] = $('#id_s__connect_prefix').val();
                    vars['connect_emailaslogin'] = $('#id_s__connect_emailaslogin').is(':checked') ? 1 : 0;
                    vars['connect_unameaslogin'] = $('#id_s__connect_unameaslogin').is(':checked') ? 1 : 0;


                    $('#connect_service_credentials_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/update_connect_server_info.php',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_credentials_comment').removeClass('rt-loading-image');
                            $('#connect_service_credentials_comment').html(data.message);
                            if (data.auth == true) {
                                $('#connect_service_credentials_comment').removeClass('label-important');// current bootstrap is danger, older is important,
                                $('#connect_service_credentials_comment').removeClass('label-danger');// so for backward compatability we have both
                                $('#connect_service_credentials_comment').addClass('label-success');
                                $('#connect_service_update_users').show();
                                $('#connect_service_update_courses').show();
                                $('#connect_service_update_activities').show();
                                checkProgress();
                            } else {
                                $('#connect_service_credentials_comment').removeClass('label-success');
                                $('#connect_service_credentials_comment').addClass('label-important');// current bootstrap is danger, older is important,
                                $('#connect_service_credentials_comment').addClass('label-danger');// so for backward compatability we have both
                                $('#connect_service_update_users').hide();
                                $('#connect_service_update_courses').hide();
                                $('#connect_service_update_activities').hide();
                            }
                        }
                    });

                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/update_all_connect_settings.php',
                        success: function (data) {
                        }
                    });
                });

                if ($('#connect_service_credentials_comment').length) {
                    $('#connect_service_update_users').hide();
                    $('#connect_service_update_courses').hide();
                    $('#connect_service_update_activities').hide();
                    $('#connect_service_credentials_comment').html('');
                    $('#connect_service_credentials_comment').addClass('rt-loading-image');
                    $.ajax({
                        type: 'GET',
                        url: '../local/refinedservices/ajax/check_connect_server_info.php',
                        data: vars,
                        success: function (data) {
                            $('#connect_service_credentials_comment').removeClass('rt-loading-image');
                            $('#connect_service_credentials_comment').html(data.message);
                            if (data.auth == true) {
                                $('#connect_service_credentials_comment').removeClass('label-important');// current bootstrap is danger, older is important,
                                $('#connect_service_credentials_comment').removeClass('label-danger');// so for backward compatability we have both
                                $('#connect_service_credentials_comment').addClass('label-success');
                                $('#connect_service_update_users').show();
                                $('#connect_service_update_courses').show();
                                $('#connect_service_update_activities').show();
                                checkProgress();
                            } else {
                                $('#connect_service_credentials_comment').removeClass('label-success');
                                $('#connect_service_credentials_comment').addClass('label-important');// current bootstrap is danger, older is important,
                                $('#connect_service_credentials_comment').addClass('label-danger');// so for backward compatability we have both
                            }
                        }
                    });
                }

            });

        }
    };

    return refinedservices;
});