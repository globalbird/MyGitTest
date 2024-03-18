<p>Dear <?= $mail_data['user']->name ?></p>
<p>We received a request to reset password for CI4Blog account associated with <i><?= $mail_data['user']->email ?></i>.</p>
<p>Reset password by clicking the button below:
    <br><br>
    <a href="<?= $mail_data['actionLink']?>" style="color:#fff; border-color:#22bc66; border-style:solid; border-width:5px 10px; background-color:#22bc66; display:inline-block; text-decoration:none; border-radius:3px; box-shadow:0 2px 3px rgba(0,0,0,0.16);-webkit-text-size-adjust:none; box-sizing:border-box;" target="blank">Reset password</a>
    <br><br>
    <b>NB:</b> This link will still be valid within 15 minutes.
    <br><br>
    If you did not request this password reset, please ignore this mail.
</p>