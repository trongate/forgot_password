<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trongate</title>
</head>
<body>

    <h1>Forgot Password</h1>
    <h2>by David Connelly</h2>
        <div class="container">
        <p>Here's a module for assisting users who have forgotten their passwords.</p>

        <p>Based on your current settings, the password recovery URL is:</p>

        <p><?= anchor($link, 'HERE (CLICK TO VIEW)') ?></p>
        <p style="margin-top: 2em;">Here's some PHP code that might be useful:</p>
        <?php
        $link = str_replace(BASE_URL, '', $link);
        ?>
        <pre><div id="copy"><img src="<?= BASE_URL ?>forgot_password_module/copy.png" onclick="copyCode()"></div>
<?php
$ditch = 'forgot_password/help/';
$enc_code = str_replace($ditch, '', $link);
?>
<div id="php-code">$password_recovery_url = BASE_URL.'forgot_password/help/';
$password_recovery_url.= '<?= $enc_code ?>';
echo anchor($password_recovery_url, 'Forgot your password?');</div></pre>

<p style="text-align: left;">INSTRUCTIONS: Open 'Forgot_password.php' (the controller file) and edit the settings at the top of the 'index()' method to suit your needs.</p>

<p style="text-align: left;">PLEASE NOTE: You'll need to complete the _send_email() method yourself to enable the app to send emails.  Email sending functionality is not included with this module.</p>
    </div>
    <style>
        body {
            font-size: 1.6em;
            background: #636ec6;
            color: #ddd;
            text-align: center;
            font-family: "Lucida Console", Monaco, monospace;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            margin-top: 2em;
        }

        h1, h2 {
            text-transform: uppercase;
        }

        p {
            font-size: 1.3rem;
        }

        a { color: white; }

        pre {
            background-color: #333;
            color: #eee;
            padding: 16px;
            text-align: left;
            line-height: 2em;
            margin: 0 auto;
            overflow: auto;
        }

        #copy {
            position: relative;
            padding: 0;
            margin: 0;
            height: 24.5px;
            text-align: right;
            font-size: 17px;
        }

        #copy img {
            width: 24.5px;
            height: 30px;
            width: auto;
            cursor: pointer;
        }

        #php-code {
            top: -3em;
            position: relative;
            font-size: 13px;
            line-height: 2em;
        }
    </style>

<script>
function _(str) {
    var firstChar = str.substring(0,1);
    if (firstChar == '.') {
        str = str.replace('.', '');
        return document.getElementsByClassName(str);
    } else {
        return document.getElementById(str);
    }
}

function copyCode() {
    var copyBtnText = _("copy").innerHTML;
    var codeEl = document.querySelector("#php-code");
    var text = codeEl.innerHTML;
    navigator.clipboard.writeText(text);

    _("copy").innerHTML = 'copied!';
    setTimeout(() => {
        _("copy").innerHTML = copyBtnText;
    }, 1500);
}
</script>

</body>
</html>