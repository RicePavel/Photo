<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Controller.php';

session_start();
// put your code here
        
// вывод информации
 
$controller = new Controller();
$controller->outputFile();
