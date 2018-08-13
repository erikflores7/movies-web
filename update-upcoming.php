<?php

$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "database";

$id = "";
$title = "";
$year = "";
$release_date = "";
$genre = "";
$imdb = "";
$tomatoes = "";
$metacritic = "";
$dvd_release = "";
$runtime = "";
$poster = "";
$summary = "";

$id = $_POST['id'];
$title = $_POST['title'];
$year = $_POST['year'];
$release_date = $_POST['release_date'];
$genre = $_POST['genre'];
$imdb = $_POST['imdb'];
$tomatoes = $_POST['tomatoes'];
$metacritic = $_POST['metacritic'];
$dvd_release = $_POST['dvd_release'];
$runtime = $_POST['runtime'];
$poster = $_POST['poster'];
$summary = $_POST['summary'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if(isset($id)){
    $sql = $conn->prepare("INSERT INTO movies (id, title, release_date, year, genre, imdb, tomatoes, metacritic, dvd_release, runtime, poster, summary)
    VALUES (:id, :title, :release_date, :year, :genre, :imdb, :tomatoes, :metacritic, :dvd_release, :runtime, :poster, :summary)");

        $sql->bindParam(':id', $id);
        $sql->bindParam(':title', $title);
        $sql->bindParam(':release_date', $release_date);
        $sql->bindParam(':year',$year);
        $sql->bindParam(':genre',$genre);
        $sql->bindParam(':imdb',$imdb);
        $sql->bindParam(':tomatoes',$tomatoes);
        $sql->bindParam(':metacritic',$metacritic);
        $sql->bindParam(':dvd_release',$dvd_release);
        $sql->bindParam(':runtime',$runtime);
        $sql->bindParam(':poster',$poster);
        $sql->bindParam(':summary',$summary);

        $sql->execute();
    echo "Movie '".$title."' added successfully!";
    }
}
catch(PDOException $e)
{
    echo $e->getMessage();
    //echo $sql . "<br>" . $e->getMessage();
}


$conn = null;
?>


