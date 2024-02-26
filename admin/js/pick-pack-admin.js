(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    let categories_string = `<div class="point-allocator-container">
                                <select name="categories[name][]" class="col-lg-5 col-sm-12">
                                    <option value="" disabled selected>Select Category</option>`;

    for (var index in php_vars.categories_all) {
        if (php_vars.categories_all.hasOwnProperty(index)) {
            categories_string += `<option value="${php_vars.categories_all[index].term_id}">${php_vars.categories_all[index].name}</option>`;
        }
    }

    categories_string += `</select>`;

    categories_string += `<input type="number" class="col-lg-4 col-sm-12" name="categories[points][]">
                            <p  class="category-header-items category-remove-button pick-pack-close"></p></div>`;


    $(document).ready(
        function () {

            dashboard_setup_on_plugin_status(php_vars.plugin_status_array);

            if (php_vars.split_payment) {
                $('#split-payment-checkbox').prop('checked', true);
            }

            $('#category-header-add').click(
                function () {

                    $("#category-form").append(categories_string);
                }
            );

            $(document).on(
                'click', '.category-remove-button', function (event) {

                    $(this).parent('.point-allocator-container').remove();
                }
            );

            $('#multiple-category-product-add').click(
                function () {

                    let product_id = $('[name="multiple-category-product"]').find(":selected").val();
                    let product_name = $('[name="multiple-category-product"]').find(":selected").text();
                    let terms;

                    let multiple_categories_product_start = `<div class="selected-product-row">
                                                            <p class="col-lg-5 col-sm-12 pick-pack-selected-product">${product_name}</p>
                                                                <select class="col-lg-4 col-sm-12" name="choosen-category[${product_id}]">
                                                                    <option value="" disabled >Select Category</option>`;


                    let multiple_categories_product_end = `</select><p  class="pick-pack-close category-header-items multiple-category-product-remove"></p></div>`;

                    var data = {
                        action: 'get_multiple_categories_product_terms',
                        security: php_vars.nonce,
                        product_id: product_id
                    }

                    $.ajax(
                        {
                            type: 'post',
                            url: php_vars.ajaxurl,
                            data: data,
                            success: function (response) {
                                response = JSON.parse(response);


                                if (response.status) {

                                    terms = response.terms;

                                    let multiple_categories_product_middle = '';

                                    if (terms) {
                                        for (var index in terms) {
                                            if (terms.hasOwnProperty(index)) {
                                                multiple_categories_product_middle += `<option  value = "${terms[index].term_id}">${terms[index].name}</option>`;;
                                            }
                                        }

                                        $("#category-multiple-form").append(multiple_categories_product_start + multiple_categories_product_middle + multiple_categories_product_end);
                                    }

                                }
                                else {
                                    terms = false;
                                }

                            },
                            error: function (err) {
                                console.log(err);
                            }
                        }
                    );


                }
            );

            $(document).on(
                'click', '.multiple-category-product-remove', function (event) {

                    $(this).parent('.selected-product-row').remove();
                }
            );

            $("#split-payment-checkbox").change(
                function () {

                    let split_payment = false;

                    let data = {
                        action: 'change_split_payment',
                        security: php_vars.nonce,
                        split_payment: split_payment
                    }

                    $("#split-payment-checkbox").attr("disabled", true);

                    if (this.checked) {
                        split_payment = true;
                    }

                    data = {
                        action: 'change_split_payment',
                        security: php_vars.nonce,
                        split_payment: split_payment
                    }
                    jQuery.ajax(
                        {
                            type: 'post',
                            url: php_vars.ajaxurl,
                            data: data,
                            success: function (response) {
                                response = JSON.parse(response);
                                if (response.status) {
                                    dashboard_setup_on_plugin_status(response.plugin_status_array);
                                }
                                $("#split-payment-checkbox").removeAttr("disabled");
                            },
                            error: function (err) {
                                console.log(err);
                                $("#split-payment-checkbox").removeAttr("disabled");
                            }
                        }
                    );
                }
            );

            $("#header-editing").keyup(
                function () {
                    var content = $('#header-editing').val();
                    $('#header-text-preview').html(content.replace(/\n/g, "<br>"));
                }
            );

            $("#content-editing").keyup(
                function () {
                    var content = $('#content-editing').val();
                    $('#content-text-preview').html(content.replace(/\n/g, "<br>"));
                }
            );

            $('#popup-preview-button').click(
                function () {
                    $(".admin-overlay").show();

                }
            );

            $('#preview-close').click(
                function () {
                    $(".admin-overlay").hide();

                }
            );


        }
    );

    function dashboard_setup_on_plugin_status(plugin_status_array)
    {

        if (plugin_status_array['stock_empty'] == false) {
            $('#stock-warning').hide();
        }

        if (plugin_status_array['default_payment_complete']) {
            $('#popup-text-container').show();
            $('#default-payment-price').show();
        }
        else {
            $('#popup-text-container').hide();
            $('#default-payment-price').hide();
        }

        if (plugin_status_array['default_payment_incomplete']) {
            $('#default-payment-incomplete-warning').show();
        }
        else {
            $('#default-payment-incomplete-warning').hide();
        }

        if (plugin_status_array['split_payment_no_payment_method']) {
            $('#split-payment-no-payment-method-warning').show();
        }
        else {
            $('#split-payment-no-payment-method-warning').hide();
        }
        if (plugin_status_array['split_payment_both_left']) {
            $('#split-payment-both-left-warning').show();
        }
        else {
            $('#split-payment-both-left-warning').hide();
        }
        if (plugin_status_array['split_payment_complete']) {
            $('#split-payment-price').show();
        }
        else {
            $('#split-payment-price').hide();
        }



    }

})(jQuery);

