<div class=" pick-pack-container pick-pack-container-checkout" style="display: none">

    <div class="overlay_pick_pack ">
    </div>
    <div id="pick_pack_popup" class="popup ">
        <div class="popup-header">
            <span class="close toggle" data-target="pick_pack_popup">close</span>
        </div>
        <div class="popup-body">
            
            <h3><?php esc_attr_e("Do you want a PickPack reusable package?", "pick-pack") ?></h3>

            <h4>
                <?php if ($split_payment) : ?>
                    <?php esc_attr_e("As a thank you for using the PickPack service, the online store where you are shopping will offer you 10% off your next order*.", "pick-pack") ?>
                <?php else : ?>
                    <?php echo nl2br($popup_header) ?>
                <?php endif; ?>
            </h4>
            

            <div class="popup_flex">

                <img class="pick-pack-logo" src="https://www.ecobagapplication.pick-pack.ca//wp-plugin-server/assets/WhatsApp Image 2022-12-14 at 21.19.54.jpeg" alt="">


                <div class="popup_col">
                    <div class="cnt">
                        <?php if ($split_payment) : ?>
                            <p><?php esc_attr_e("The PickPack option allows you to reduce your environmental footprint by returning your packaging after receiving your package.", "pick-pack") ?>
                            </p>
                            <p><?php esc_attr_e("TRULY be part of the environmental solution for only $3.99.", "pick-pack") ?>
                            </p>
                            <p><?php esc_attr_e("The return is FREE and SIMPLE.", "pick-pack") ?>
                            </p>
                            <p><?php esc_attr_e("See the return procedure (Collaboration with Canada Post)", "pick-pack") ?>
                            </p>
                        <?php else : ?>
                            <p><?php echo nl2br($popup_text) ?></p>
                        <?php endif; ?>


                    </div>

                </div>

                <label class="form-control-checkbox">
                    <input type="checkbox" name="checkbox-pickpack" id="checkbox-pickpack" />
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
                                            $string = str_replace("CONDITIONS : ", "", $string);
                                            echo $span_text . $string;
                                            ?>
                    </p>

                </div>
            </div>
        </div>

    </div>
</div>