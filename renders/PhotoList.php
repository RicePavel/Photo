<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'header.php';

?>


<form method="POST" enctype="multipart/form-data" action="?action=addPhotos" >
    <input type="file" name="photos[]" /> <br/>
    <input type="submit" name="submit" value="Загрузить фотографии" />
</form>

