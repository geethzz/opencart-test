<?php foreach ($styles as $style) { ?>
    <link href="<?php echo $style['href']; ?>" type="text/css" rel="<?php echo $style['rel']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<?php echo $title; ?>
<div class="mfContainer">
    <?php
    foreach ($gateways as $gateway) {
        $alt = ($language == 'ar') ? $gateway->PaymentMethodAr : $gateway->PaymentMethodEn;
        ?>
        <div class="mfDiv">
            <input type="radio" class="mfInput" id="<?php echo $id . '_' . $gateway->PaymentMethodId; ?>" value="<?php echo $gateway->PaymentMethodId; ?>" name="<?php echo $id; ?>_payment">
            <label for="<?php echo $id . '_' . $gateway->PaymentMethodId; ?>" class="mfLabel">
                <?php echo $alt; ?>
                <img src="<?php echo $gateway->ImageUrl; ?>" width="40px" title="<?php echo $alt; ?>" alt="<?php echo $alt; ?>">
            </label>
        </div>
    <?php } ?>
</div>
<?php foreach ($scripts as $script) { ?>
    <script src="<?php echo $script; ?>" type="text/javascript"></script>
<?php } ?>