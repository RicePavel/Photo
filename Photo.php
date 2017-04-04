<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Photo {
    
    public $photoId;
    
    public $userId;
    
    public $photoName;
    
    public $header;
    
    public $description;
    
    public $path;
    
    public function __construct($photoId, $userId, $photoName, $header, $description, $path) {
        $this->photoId = $photoId;
        $this->userId = $userId;
        $this->photoName = $photoName;
        $this->header = $header;
        $this->description = $description;
        $this->path = $path;
    }
    
}



