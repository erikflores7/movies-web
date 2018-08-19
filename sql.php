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
    $latest = 0;

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
        $latest = $_POST['latest'];
        addMovie($conn);
        break;
    case "updateMovie":
        $id = $_POST['id'];
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
        $latest = $_POST['latest'];
        updateMovie($conn);
        break;
    case "removeUpcomingTag":
        removeUpcomingTag($conn, $_POST['id']);
        break;
    case "removeLatestTag":
        removeLatestTag($conn, $_POST['id']);
        break;
    case "getLatest":
        getLatestList($conn);
        break;
    case "getByGenre":
        getByGenre($conn, $_POST['search']);
        break;
    case "getMissingIMDB":
        getMissingIMDB($conn);
        break;
    case "search":
        search($conn, $_POST['search'], $_POST['year']);
        break;

}

    function getUpcomingList($conn){

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE upcoming = '1'");

            $result->execute();
            $return = $result->fetchAll();

            echo json_encode($return);
            return;

        }
        catch(PDOException $e)
        {    echo $e->getMessage();
        }
    }

    function getLatestList($conn){

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE latest = '1'");

            $result->execute();
            $return = $result->fetchAll();

            echo json_encode($return);
            return;
        }
        catch(PDOException $e)
        {    echo $e->getMessage();
        }
    }

    function getByGenre($conn, $genre){

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE lower(genre) LIKE lower('%$genre%');");

            $result->execute();
            $return = $result->fetchAll();

            echo json_encode($return);
            return;
        }
        catch(PDOException $e)
        {    echo $e->getMessage();
            return;
        }
    }


// Uses title, Upcoming list does not return imdbID, to check if exists in database AND is to be released
// Same title could exist but not recent, reduces omdbapi usage
    function shouldAddUpcoming($conn, $title){
        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT 1 FROM movies WHERE title = '$title' AND upcoming = 1");
            $result->execute();
            $return = $result->fetchAll();

            if(empty($return)){
                echo 0;
                echo $title;
                return;
            }else{
                echo $return;
                return;
            }
            //echo "Movie '".$title."' already exists!";
        }catch(PDOException $e) {
            echo $e->getMessage();
            //echo $sql . "<br>" . $e->getMessage();
            return;
        }
    }

    // Checks if database contains the imdbID
    function movieExists($conn, $id){
        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT 1 FROM movies WHERE id = '$id'");
            $result->execute();
            $return = $result->fetchAll();

            if(empty($return)){
                echo false;
                return;
            }else{
                echo true;
                return;
            }
            //echo "Movie '".$title."' already exists!";
        }catch(PDOException $e) {
            echo $e->getMessage();
            return;
        }
    }

    function getLastUpcomingUpdate($conn){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE id='date'");
            $result->execute();
            $return = $result->fetchAll();

            echo $return[0]['release_date'];
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
            return;
        }

    }

    function updateUpcomingCheck($conn, $date){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("UPDATE movies SET release_date = '$date' WHERE id = 'date'");
            $result->execute();
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
            return;
        }
    }


    // Check if movie ID does not already exist in database
    function addMovie($conn)
    {

        global $id, $title, $release_date, $year, $genre, $imdb, $tomatoes, $metacritic, $dvd_release, $runtime, $poster, $summary, $upcoming, $latest;

        if (!movieExists($conn, $id)) {

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (isset($id)) {
                $sql = $conn->prepare("INSERT INTO movies (id, title, release_date, year, genre, imdb, tomatoes, metacritic, dvd_release, runtime, poster, summary, upcoming, latest)
    VALUES (:id, :title, :release_date, :year, :genre, :imdb, :tomatoes, :metacritic, :dvd_release, :runtime, :poster, :summary, :upcoming, :latest)");

                $sql->bindParam(':id', $id);
                $sql->bindParam(':title', $title);
                $sql->bindParam(':release_date', $release_date);
                $sql->bindParam(':year', $year);
                $sql->bindParam(':genre', $genre);
                $sql->bindParam(':imdb', $imdb);
                $sql->bindParam(':tomatoes', $tomatoes);
                $sql->bindParam(':metacritic', $metacritic);
                $sql->bindParam(':dvd_release', $dvd_release);
                $sql->bindParam(':runtime', $runtime);
                $sql->bindParam(':poster', $poster);
                $sql->bindParam(':summary', $summary);
                $sql->bindParam(':upcoming', $upcoming);
                $sql->bindParam(':latest', $latest);

                $sql->execute();
                return;
            }else{
                return;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return;
        }
    }
        return;
    }

    function removeUpcomingTag($conn, $imdbid){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("UPDATE movies SET upcoming = 0 WHERE id = '$imdbid'");
            $result->execute();
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }


    function removeLatestTag($conn, $imdbid){

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("UPDATE movies SET latest = '0' WHERE id = '$imdbid'");
            $result->execute();
            return;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getMissingIMDB($conn){

        try{
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE imdb='N/A'");
            $result->execute();
            $return = $result->fetchAll();

            echo json_encode($return);
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }

    function updateMovie($conn){


        global $id, $release_date, $year, $genre, $imdb, $tomatoes, $metacritic, $dvd_release, $runtime, $poster, $summary, $upcoming, $latest;

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if(isset($id)){
                $sql = $conn->prepare("UPDATE movies SET release_date=:release_date, year=:year, genre=:genre, imdb=:imdb, tomatoes=:tomatoes, metacritic=:metacritic, dvd_release=:dvd_release, runtime=:runtime, poster=:poster, summary=:summary, upcoming=:upcoming, latest=:latest WHERE id=:id");

                $sql->bindParam(':id', $id);
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
                $sql->bindParam(':latest', $latest);

                $sql->execute();
                return;
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            return;
        }

    }

    function search($conn, $search, $y){

        $year = "";
        if($y !== ""){
            $year = "AND year='$y'";
        }

        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $result = $conn->prepare("SELECT * FROM movies WHERE lower(title) LIKE lower('%$search%') $year;");

            $result->execute();
            $list = $result->fetchAll();

            echo json_encode($list);
            return;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return;
        }

    }

    $conn = null;

?>