<div id="pick_pack_popup" class=" pick-pack-container">
    <div class="popup-header">
        <span class="close toggle" >close</span>
    </div>
        <div class="popup-body">
            <!-- <div class="logo-container"> -->
                <!-- <img class="pick-pack-logo" src="https://phpstack-851887-2938889.cloudwaysapps.com/wp-plugin-server/assets/Logo_Officiel_PickPack_2-removebg-preview.png" alt=""> -->
            <h3><?php esc_attr_e("Do you want a PickPack reusable package?","pick-pack") ?></h3>

            <h4 id="header-text-preview">
                <?php echo nl2br($popup_header) ?>
            </h4>
            <!-- </div> -->
           
            <div class="popup_flex">

                <img class="pick-pack-logo" src="https://www.ecobagapplication.pick-pack.ca//wp-plugin-server/assets/WhatsApp Image 2022-12-14 at 21.19.54.jpeg" alt="">
                

                <div class="popup_col">
                    <div class="cnt">
                        <p id="content-text-preview"><?php echo nl2br($popup_text) ?></p>
                    </div>
                    
                </div>

                <label class="form-control-checkbox">
                  <input type="checkbox" name="checkbox-pickpack"  id="checkbox-pickpack"/>
                  <?php esc_attr_e("My order will be delivered in Canada", "pick-pack") ?>
                </label>

                <div class="popup-footer">
                    <div class="footer-flex">
                        <button class=" pick_pack_button pick_pack_add_checkout" data-target="pick_pack_popup"><?php esc_attr_e("Yes, I want to make a difference.", "pick-pack") ?></button>

                        <button type='button' class="toggle cancel-button"><?php esc_attr_e("No thank you", "pick-pack") ?></button>
                    </div>
                    <div class="centered">
                        <p class="inline-text"><?php esc_attr_e("Initiative powered by PickPack", "pick-pack") ?> </p>
                        <img class="inline-image" src="https://phpstack-851887-2938889.cloudwaysapps.com/wp-plugin-server/assets/Logo_Officiel_PickPack_2-removebg-preview.png" alt="">
                    </div>
                    
                    <p class="conditions"><?php 
                        $span_text = '<span>CONDITIONS : </span>';
                        $string = esc_attr__("CONDITIONS : You must choose the PickPack option AND return your packaging as agreed.", "pick-pack");
                        $string = str_replace("CONDITIONS : ", "",$string);
                        echo $span_text . $string;
                     ?>
                    </p>
                    <!-- <h5>Prix : $3 <span><button data-target="pick_pack_popup" class="close toggle pick_pack_button pick_pack_add" data-target="pick_pack_popup">Ajouter au panier</button></span></h5> -->

                </div>
            </div>
        </div>
        
    </div>