<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require 'header.php';

?>

<?php
    if (isset($error)) {
?>
    <div> <?= $error ?></div>
<?php 
    }
?>

<form method="POST" enctype="multipart/form-data" action="?action=addPhotos" >
    <input type="file" name="photos[]" /> <br/>
    <input type="submit" name="submit" value="Загрузить фотографии" />
</form>
    <br/> <br/>
    
    <?php  
    if ($photoArray != null) {
        foreach ($photoArray as $photo) {
    ?>
    <div>
        <div> <img  style='max-width: 500px; max-height: 500px;' src='<?= $photo->path ?>' /> </div>
        <div> <?= $photo->photoName ?> </div>
        <div> <?= $photo->header ?>  </div>
        <div> <?= $photo->description ?>  </div>
        <div> <a href='?action=changePhotoForm&photoId=<?= $photo->photoId ?>' >Изменить</a> </div>
        <br/>
    </div>
    <?php } } ?>

