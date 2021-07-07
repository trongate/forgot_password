<div id="forgot-password">
    <h1>Forgot Password</h1>
    <?= validation_errors() ?>
    <p><?= $form_intro ?></p>
    <?php
    echo form_open($form_location);
    foreach($fields as $field) {
        $field_label = str_replace('_', ' ', $field);
        $placeholder = 'Enter your '.strtolower($field_label).' here';

        echo form_label(ucwords($field_label));

        $strpos = strpos($field, 'email');
        if (is_numeric($strpos)) {
            echo form_email($field, '', array('placeholder' => $placeholder));
        } else {
            echo form_input($field, '', array('placeholder' => $placeholder));
        }
    }
    echo form_submit('submit', 'Submit');
    echo anchor(BASE_URL, 'Cancel', array('class' => 'button alt'));
    echo form_close();
    ?>
</div>