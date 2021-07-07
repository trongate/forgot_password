<div id="forgot-password">
<h1>Check Your Email</h1>
<p>An email has been sent to you with instructions on how to reset your password.</p>  
<p>Please check your email and follow the instructions provided.</p>

<p>If your email has not arrived after a few minutes, please check your 'spam' folder.</p>

<?php
if (ENV == 'dev') {
    echo Modules::run('forgot_password/_check_email_dev_shortcut');
}
?>
</div>