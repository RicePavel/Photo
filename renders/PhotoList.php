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
    <input type="file" name="photos[]" multiple /> <br/>
    <input type="submit" name="submit" value="Загрузить фотографии" />
</form>
    <br/>
    
    <form method="GET" action="?action=photosList" > 
        <input type="text" name="searchText" value="<?= isset($_REQUEST['searchText']) ? $_REQUEST['searchText'] : '' ?>" />
        <input type="submit" name="submit" value="Поиск" />
    </form>
    <br/>
    
    <form method="POST" action="?action=deletePhotos" id="deleteForm" >
        <input type="submit" name="submit" value="Удалить отмеченные фотографии" />
    </form>
    <br/>
    
    <?php  
    if ($photoArray != null) {
        $n = 1;
        foreach ($photoArray as $photo) {
    ?>
            <div class="photo_container">
                <div> <?= $photo->photoName ?> </div>
                <div> <img src='<?= $photo->path ?>' /> </div>
                <div> <b> <?= $photo->header ?> </b>  </div>
                <div> <?= $photo->description ?>  </div>
                <div> <a href='?action=changePhotoForm&photoId=<?= $photo->photoId ?>' >Изменить</a> </div>
                <div> <input type="checkbox" name="photoId[]" value="<?= $photo->photoId ?>" form="deleteForm" /> </div>
                <br/>
               
            </div>
            <?php 
                if ($n % 2 == 0) {
            ?>   
            <div style='clear: both;' > </div>
            <?php
                }
            ?>
    <?php 
            $n++;
        } 
    } 
        ?>

