(function ( $ ) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
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

    jQuery(document).ready(
        function () {

            jQuery(document).on(
                'click', '.toggle-2', function (event) {
                    event.preventDefault();        
            
                    jQuery(".pick-pack-container").show();
                }
            );
        

            jQuery(document).on(
                'click', '#pick_pack_popup .close', function (event) {
                    release_scroll();
                }
            );
            jQuery(document).on(
                'click', '#pick_pack_popup .toggle', function (event) {
                    release_scroll();
                }
            );
            jQuery(document).on(
                'click', '#pick_pack_popup .pick_pack_add', function (event) {
            
                    event.preventDefault();

                    if (!jQuery("#checkbox-pickpack").is(':checked')) {
                        return;
                    }
                    release_scroll();
                    product_add_to_cart();
                }
            );

            jQuery(document).on(
                'click', '#pick_pack_popup .pick_pack_add_checkout', function (event) {
            
                    event.preventDefault();

                    if (!jQuery("#checkbox-pickpack").is(':checked')) {
                        return;
                    }
                    release_scroll();
                    product_add_to_cart_checkout();
                }
            );

            $(document.body).on(
                'update_checkout', function (event) {
                    if (jQuery("[name='billing_country']").find(":selected").val() === 'CA') {
                
                        let state_name = jQuery("[name='billing_state']").find(":selected").val();

                        if (typeof php_vars.state_name_array[state_name] !== 'undefined') {
                    
                             jQuery("#state-tax-info").html(php_vars.state_name_array[state_name]);
                    
                        }
                    }
                    else{
                        jQuery("#state-tax-info").html('');
                    }
                }
            );

            $(document).ajaxSuccess(
                function (event,xhr) {
          

                    if (typeof xhr.responseJSON.fragments !== 'undefined') {
              
                        if('straight_to_checkout_popup' in xhr.responseJSON.fragments) {
                            jQuery(".pick-pack-container").show();
                        }
                
                    }
                }
            );

        }
    );


    function release_scroll()
    {
        jQuery('html, body').css(
            {
                overflow: 'auto',
                height: 'auto'
            }
        );
        jQuery(".pick-pack-container").hide();
    }

    function product_add_to_cart()
    {

        var data = {
            action: 'pick_pack_add_to_cart_product',
            security: php_vars.nonce,
        }

        jQuery.ajax(
            {
                type: 'post',
                url: php_vars.ajaxurl,
                data: data,
                success: function (response) {
                      response = JSON.parse(response);
                    if(response.status && response.product_add) {
                        jQuery("[name='update_cart']").removeAttr('disabled');
                        jQuery("[name='update_cart']").trigger("click");
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            }
        );

    }

    function product_add_to_cart_checkout()
    {

        var data = {
            action: 'pick_pack_add_to_cart_product',
            security: php_vars.nonce,
        }

        jQuery.ajax(
            {
                type: 'post',
                url: php_vars.ajaxurl,
                data: data,
                success: function (response) {
                      response = JSON.parse(response);
                    if(response.status && response.product_add) {
                        jQuery("#state-tax-info-container").html('<p id="state-tax-info"></p>');
                        jQuery(document.body).trigger("update_checkout");
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            }
        );

    }

})(jQuery);

