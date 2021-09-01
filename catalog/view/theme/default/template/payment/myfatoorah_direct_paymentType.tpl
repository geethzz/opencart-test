<form id="form-myfatoorah_direct" class="form-horizontal" action="<?php echo $action; ?>" method="post">
    <fieldset>
        <legend><?php echo $text_credit_card; ?></legend>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-cc-type"><?php echo $entry_cc_type; ?></label>
            <div class="col-sm-10">
                <select name="cc_type" id="input-cc-type" class="form-control">
                    <?php foreach ($cards as $card) { ?>
                        <option value="<?php echo $card['value']; ?>"><?php echo $card['text']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-cc-owner"><?php echo $entry_cc_owner; ?></label>
            <div class="col-sm-10">
                <input type="text" name="cc_owner" value="" placeholder="<?php echo $entry_cc_owner; ?>" id="input-cc-owner" class="form-control" required="true"/>
            </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-cc-number"><?php echo $entry_cc_number; ?></label>
            <div class="col-sm-10">
                <input type="text" name="cc_number" value="" placeholder="<?php echo $entry_cc_number; ?>" id="input-cc-number" class="form-control" 
                       required="true" pattern="\d*" maxlength="20" title="<?php echo $error_cc_number_digits; ?>"/>
            </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-cc-expire-date"><?php echo $entry_cc_expire_date; ?></label>
            <div class="col-sm-3">
                <select name="cc_expire_date_month" id="input-cc-expire-date" class="form-control">
                    <?php foreach ($months as $month) { ?>
                        <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-3">
                <select name="cc_expire_date_year" class="form-control">
                    <?php foreach ($years as $year) { ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-cc-cvv2"><?php echo $entry_cc_cvv2; ?></label>
            <div class="col-sm-10">
                <input type="text" name="cc_cvv2" value="" placeholder="<?php echo $entry_cc_cvv2; ?>" id="input-cc-cvv2" class="form-control" 
                       required="true" pattern="\d*" maxlength="4" minlength="3" title="<?php echo $error_cc_cvv2_4_digits; ?>"/>
            </div>
        </div>
    </fieldset>
</form>    