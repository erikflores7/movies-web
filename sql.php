<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/12/18
 * Time: 9:52 PM
 */

$servername = $_POST['servername'];
$username = $_POST['username'];
$password = $_POST['password'];
$dbname = $_POST['database'];


$function = $_POST['function'];

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
$upcoming = 0;

$conn = new PDO("mysql:host=$servername;dbname=$dbname;", $username, $password);


switch ($function){

    case "getUpcoming":
        getUpcomingList($conn);
        break;
    case "shouldAddUpcoming":
        shouldAddUpcoming($conn, $_POST['title']);
        break;
    case "lastUpcomingCheck":
        getLastUpcomingUpdate($conn);
        break;
    case "updateUpcomingCheck":
        updateUpcomingCheck($conn, $_POST['date']);
        break;
    case "addMovie":
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
        $upcoming = $_POST['upcoming'];
        addMovie($conn);
        break;
    case "removeUpcomingTag":
        removeUpcomingTag($conn, $_POST['id']);
        break;

}

function getUpcomingList($conn){

    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $conn->prepare("SELECT *
FROM movies
WHERE upcoming = '1'");

        $result->execute();
        $return = $result->fetchAll();

        echo json_encode($return);

    }
    catch(PDOException $e)
    {    echo $e->getMessage();
    }
}


// Uses title, Upcoming list does not return imdbID, to check if exists in database AND is to be released
// Same title could exist but not recent, reduces omdbapi usage
    function shouldAddUpcoming($conn, $title){
        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT 1
FROM movies
WHERE title = '$title' AND upcoming = 1");
            $result->execute();
            $return = $result->fetchAll();

            if(empty($return)){
                echo 0;
                echo $title;
            }else{
                echo $return;
            }
            //echo "Movie '".$title."' already exists!";
        }catch(PDOException $e) {
            echo $e->getMessage();
            //echo $sql . "<br>" . $e->getMessage();
        }
    }

    function getLastUpcomingUpdate($conn){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT *
FROM movies
WHERE id='date'");
            $result->execute();
            $return = $result->fetchAll();

            echo $return[0]['release_date'];

        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }

    function updateUpcomingCheck($conn, $date){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("UPDATE movies
SET release_date = '$date'
WHERE id = 'date'");
            $result->execute();

        }catch(PDOException $e) {
            echo $e->getMessage();
        }


    }

    function addMovie($conn){

        global $id, $title, $release_date, $year, $genre, $imdb, $tomatoes, $metacritic, $dvd_release, $runtime, $poster, $summary, $upcoming;

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if(isset($id)){
                $sql = $conn->prepare("INSERT INTO movies (id, title, release_date, year, genre, imdb, tomatoes, metacritic, dvd_release, runtime, poster, summary, upcoming)
    VALUES (:id, :title, :release_date, :year, :genre, :imdb, :tomatoes, :metacritic, :dvd_release, :runtime, :poster, :summary, :upcoming)");

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
                $sql->bindParam(':upcoming', $upcoming);


                $sql->execute();
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }

    }

    function removeUpcomingTag($conn, $imdbid){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("UPDATE movies
SET upcoming = 0
WHERE id = '$imdbid'");
            $result->execute();

        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }


    $conn = null;



?>