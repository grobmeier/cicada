<?php
/*
 *  Copyright 2013 Christian Grobmeier
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */

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