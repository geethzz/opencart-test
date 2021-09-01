<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i> 
        <?php echo $error; ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
<?php else: ?>
    <?php echo $directPaymentForm; ?>
    <div class="buttons">
        <div class="pull-right">
            <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" form="form-myfatoorah_direct"/>
        </div>
    </div>
<?php endif; ?>