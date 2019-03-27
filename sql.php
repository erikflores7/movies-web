<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/12/18
 * Time: 9:52 PM
 */

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

    $conn = null;

    function getConnection(){

        global $conn;

        if($conn != null){
            return $conn;
        }

        include_once('../config.php');

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }

if(isset($_POST['function'])) {
    switch ($_POST['function']) {

        case "getUpcoming":
            getUpcomingList();
            break;
        case "shouldAddUpcoming":
            shouldAddUpcoming($_POST['title']);
            break;
        case "lastUpcomingCheck":
            getLastUpcomingUpdate();
            break;
        case "updateUpcomingCheck":
            updateUpcomingCheck($_POST['date']);
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
            addMovie();
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
            updateMovie();
            break;
        case "removeUpcomingTag":
            removeUpcomingTag($_POST['id']);
            break;
        case "removeLatestTag":
            removeLatestTag($_POST['id']);
            break;
        case "getLatest":
            getLatestList();
            break;
        case "getByGenre":
            getByGenre($_POST['search']);
            break;
        case "getMissingIMDB":
            getMissingIMDB();
            break;
        case "search":
            search($_POST['search']);
            break;
        case "getKeys":
            getKeys();

    }
}

    function getUpcomingList(){

        try {
            $conn = getConnection();
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

    function getLatestList(){

        try {
            $conn = getConnection();
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

    function getByGenre($genre){

        if(isset($_POST['page'])){
            $lowerLimit = ($_POST['page'] - 1) * 5;
        }else{
            $lowerLimit = 0;
        }
        settype($lowerLimit, "integer");
        $genre = '%' . $genre . '%';

        try {
            $conn = getConnection();
            $sql = $conn->prepare("SELECT * FROM movies WHERE lower(genre) LIKE lower( :genre ) ORDER BY year DESC LIMIT 5 OFFSET :lowerLimit ;");
            $sql->bindParam(':genre', $genre);
            $sql->bindParam(':lowerLimit', $lowerLimit, PDO::PARAM_INT);

            $sql->execute();
            $result = $sql->fetchAll();
            echo json_encode($result);
            return;
        }
        catch(PDOException $e)
        {    echo $e->getMessage();
            return;
        }
    }


// Uses title, Upcoming list does not return imdbID, to check if exists in database AND is to be released
// Same title could exist but not recent, reduces omdbapi usage
    function shouldAddUpcoming($title){
        try{
            $conn = getConnection();
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
    function movieExists($id){
        try{
            $conn = getConnection();
            $result = $conn->prepare("SELECT 1 FROM movies WHERE id = '$id'");
            $result->execute();
            $return = $result->fetchAll();

            if(empty($return)){
                return false;
            }else{
                return true;
            }
            //echo "Movie '".$title."' already exists!";
        }catch(PDOException $e) {
            echo $e->getMessage();
            return true;
        }
    }

    // Last time movies were updated through daily update
    function getLastUpcomingUpdate(){

        try{
            $conn = getConnection();
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

    function updateUpcomingCheck($date){

        try{
            $conn = getConnection();
            $result = $conn->prepare("UPDATE movies SET release_date = '$date' WHERE id = 'date'");
            $result->execute();
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
            return;
        }
    }


    // Check if movie ID does not already exist in database
    function addMovie()
    {

        global $id, $title, $release_date, $year, $genre, $imdb, $tomatoes, $metacritic, $dvd_release, $runtime, $poster, $summary, $upcoming, $latest;

        if (!movieExists($id)) {

        try {
            $conn = getConnection();

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

    function removeUpcomingTag($imdbid){

        try{
            $conn = getConnection();
            $result = $conn->prepare("UPDATE movies SET upcoming = 0 WHERE id = '$imdbid'");
            $result->execute();
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }


    function removeLatestTag($imdbid){

        try {
            $conn = getConnection();
            $result = $conn->prepare("UPDATE movies SET latest = '0' WHERE id = '$imdbid'");
            $result->execute();
            return;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getMissingIMDB(){

        try{
            $conn = getConnection();
            $result = $conn->prepare("SELECT * FROM movies WHERE imdb='N/A'");
            $result->execute();
            $return = $result->fetchAll();

            echo json_encode($return);
            return;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }

    }

    function updateMovie(){


        global $id, $release_date, $year, $genre, $imdb, $tomatoes, $metacritic, $dvd_release, $runtime, $poster, $summary, $upcoming, $latest;

        try {
            $conn = getConnection();

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

    function search($search){

        $year = "";
        if(isset($_POST['year']) && $_POST['year'] != "") {
            $year = $_POST['year'];
        }

        // Allows searching 'Spider Man' to show results for 'Spider-Man' as well
        $search = str_replace(' ', '%', $search);
        $search = '%' . $search . '%';

        try {
            $conn = getConnection();
            if($year == ""){
                $sql = $conn->prepare("SELECT * FROM movies WHERE lower(title) LIKE lower(:search);");
                $sql->bindParam(':search', $search);
            }else{
                $sql = $conn->prepare("SELECT * FROM movies WHERE lower(title) LIKE lower(:search) AND year= :year;");
                $sql->bindParam(':search', $search);
                $sql->bindParam(':year', $year);
            }

            $sql->execute();
            $list = $sql->fetchAll();

            echo json_encode($list);
            return;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return;
        }

    }

    function getKeys(){
        include_once('../config.php');
        echo $openmoviedb;
        echo $themoviedb;
        return;
    }

    $conn = null;

?>