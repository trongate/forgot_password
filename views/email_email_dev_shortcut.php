<div class="dev-shortcut">
    <h2>*** 'dev' mode enabled ***</h2>
    <p style="text-align: left;">Since you are in 'dev' mod, you can go straight to the reset URL without 
    having to deal with sending and checking of emails.</p>
    <p><?= anchor($reset_url, 'Go Straight To Reset Password URL') ?></p>

    <p id="notice">~ This message will not be shown when you are out of 'dev' mode ~</p>
</div>

<style>
    .dev-shortcut {
        font-size: 1.4em;
        background: #636ec6;
        color: #ddd;
        text-align: center;
        font-family: "Lucida Console", Monaco, monospace;
        max-width: 690px;
        margin: 0 auto;
        padding: 12px;
    }

    .dev-shortcut h2 {
        text-transform: uppercase;
    }

    .dev-shortcut #notice {
        font-family: "Lucida Console", Monaco, monospace;
        text-transform: uppercase;
        font-size: 17px;
        font-weight: bold;
    }

    .dev-shortcut a {
        color: #fff;
        text-decoration: underline;
    }

</style>