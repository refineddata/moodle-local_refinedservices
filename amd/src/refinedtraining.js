/**
 * Created by ras on 7/14/2016.
 * Here we will initialize all the javascripts that we need for refined training
 */

define(['jquery', 'jqueryui'], function ($, ui) {

    var refinedtraining = {
        init: function () {

            /* General */
            $.widget.bridge('uibutton', $.ui.button);
            $.widget.bridge('uitooltip', $.ui.tooltip);

            /* Filter connect */
            require(['filter_connect/filter_connect'], function (connect) {
                connect.init();
            });

            /* Refined services */
            require(['local_refinedservices/refinedservices'], function (refinedservices) {
                refinedservices.init();
            });

            /* Connect activities */
            require(['local_connect/connect'], function (local_connect) {
                local_connect.init();
            });

        }
    };

    return refinedtraining;
});