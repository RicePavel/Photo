<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>

<?php
    if (isset($error)) {
?>
    <div> <?= $error ?></div>
<?php 
    }
?>

    <a href="?action=forNotAuthUser">В начало</a> <br/><br/>
    
<form method="POST" action="?action=registration" >
    Логин: <input type="text" name="login" /> <br/>
    Пароль: <input type="password" name="password" /> <br/>
    <input type="submit" value="Зарегистрироваться" name="submit" />
</form>