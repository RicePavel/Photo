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

    <?php if ($photo != null) { ?>
    <div>
        <div> <img  style='max-width: 500px; max-height: 500px;' src='<?= $photo->path ?>' /> </div>
        <div> <?= $photo->photoName ?> </div>
        <form action='?action=changePhoto' method='POST' >
            <div> <input type='text' name='header' value='<?= $photo->header ?>' placeholder='заголовок'  </div>
            <div> <textarea placeholder='описание' name='description' ><?= $photo->description ?></textarea>  </div>
            <input type='hidden' name='photoId' value='<?= $photo->photoId ?>' />
            <div> <input type='submit' name='submit' value='Изменить' /> </div>
        </form>
    </div>
    
    <?php } ?>
