<p>Dear <?= $mail_data['user']->name ?></p>
<p>
    Your password on CI4Blog was changed successfully. here are your new login credentials:
    <br>
    <p><b>Login ID: </b> <?= $mail_data['user']->username ?> or <?= $mail_data['user']->email ?>
    <br>
    <p><b>Password: </b> <?= $mail_data['new_password'] ?></p>
</p>
<br>
Please keep your credentials confidential!
<br>
Best regards,<br>
The CI4Blog system