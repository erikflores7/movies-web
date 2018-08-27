<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/25/18
 * Time: 7:12 PM
 */
   session_start();

   if(session_destroy()) {
      header("Location: login.php");
   }
?>

