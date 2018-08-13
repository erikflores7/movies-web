<?php

$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "database";

$title = $_POST['title'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $conn->prepare("SELECT 1
FROM movies
WHERE title = '$title'");
    // use exec() because no results are returned
    $result->execute();
    $return = $result->fetchAll();

    if(empty($return)){
        echo 0;
        echo $title;
    }else{
        echo 1;
    }
    //echo "Movie '".$title."' already exists!";
}
catch(PDOException $e)
{    echo $e->getMessage();
    //echo $sql . "<br>" . $e->getMessage();
}

$conn = null;

?>