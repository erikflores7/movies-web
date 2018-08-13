<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/12/18
 * Time: 9:52 PM
 */

$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "database";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $conn->prepare("SELECT *
FROM movies
WHERE upcoming = '0'");

    $result->execute();
    $return = $result->fetchAll();

    echo json_encode($return);

}
catch(PDOException $e)
{    echo $e->getMessage();
}

$conn = null;


?>