(function ($) {
    'use strict';
    
    function open_pickpack_popup()
    {
        $("#pickpack-popup").show();
    }
    
    function close_pickpack_popup()
    {
        $("#pickpack-popup").hide();
    }

    function add_pickpack_to_cart()
    {
        var current_page = '';
        if ($('body').hasClass('woocommerce-cart')) {
            current_page = 'cart';
        }
        if ($('body').hasClass('woocommerce-checkout')) {
            current_page = 'checkout';
        }
    
        $.ajax(
            {
                type: 'post',
                url: pickpack_vars.ajax_url,
                data: {
                    action: 'pick_pack_add_to_cart',
                    security: pickpack_vars.nonce,
                    page: current_page
                },
                success: function (response) {
                    if (response.success) {
                        if (current_page == 'cart') {
                            $("[name='update_cart']").removeAttr('disabled');
                            $("[name='update_cart']").trigger("click");
                        }
                        if (current_page == 'checkout') {
                            $("#state-tax-info-container").html('<p id="state-tax-info"></p>');
                            $(document.body).trigger("update_checkout");
                        }
                    }
                }
            }
        );
    }
    
    $(document).ready(
        function () {
            $('#open-pickpack-button').on(
                'click', function (e) {
                    e.preventDefault();
                    open_pickpack_popup();
                }
            );
    
            $('#pickpack-popup .pickpack-close, #pickpack-popup .pickpack-overlay, #pickpack-popup .pickpack-cancel').on(
                'click', function (e) {
                    e.preventDefault();
                    close_pickpack_popup(); 
                }
            );
    
            $('#pickpack-popup .pickpack-add').on(
                'click', function (e) {
                    e.preventDefault();
        
                    if (!$("#pickpack-popup #pickpack-checkbox").is(':checked')) {
                        $("#pickpack-popup .pickpack-checkbox-wrap").css("color", "#b21d1d");
                        return;
                    }
                    $("#pickpack-popup .pickpack-checkbox-wrap").css("color", "");
        
                    add_pickpack_to_cart();
                    close_pickpack_popup();
                }
            );
    
            $('#pickpack-popup .pickpack-howto-toggle').on(
                'click', function () {
                    if ($('#pickpack-popup .pickpack-howto').hasClass('is-open')) {
                        $('#pickpack-popup .pickpack-howto').removeClass('is-open');
                        $('#pickpack-popup .pickpack-howto-content').hide();
                    } else {
                        $('#pickpack-popup .pickpack-howto').addClass('is-open');
                        $('#pickpack-popup .pickpack-howto-content').show();
                    }
                }
            );
    
            $(document).ajaxSuccess(
                function (event,xhr) {
                    if (typeof xhr.responseJSON.fragments !== 'undefined') {
                        if('straight_to_checkout_popup' in xhr.responseJSON.fragments) {
                            open_pickpack_popup();
                        }
                    }
                }
            );
        }
    );
})(jQuery);
