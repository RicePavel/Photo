<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <script type="text/javascript" src="./jquery-3.2.0.min.js" > </script>
        <script type="text/javascript" src="./script.js" > </script>
        <link rel="stylesheet" type="text/css" href="./style.css" >
        <title></title>
    </head>
    <body>
        <?php
        
        require_once 'Controller.php';
        
        // put your code here
        
           // вывод информации
            // 
          $controller = new Controller();
          $controller->exec();
        
        ?>
    </body>
</html>
