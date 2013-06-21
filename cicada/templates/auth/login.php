<?php
$username = '';
 if (isset($this->values->username)) {
     $username = $this->values->username;
 }
?>

<h1>Login</h1>

<form action="/login/do" method="POST">
    Username: <input type="text" name="username" value="<?php echo $username; ?>" /><br/>
    Password: <input type="password" name="password" /><br/>
    <input type="submit" />
</form>