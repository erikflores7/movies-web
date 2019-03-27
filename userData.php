<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/24/18
 * Time: 7:02 PM
 */

    session_start();

    if(isset($_POST['function'])) {
        switch ($_POST['function']) {

            case "watchList":
                getWatchlist();
                break;
            case "addRating":
                addRating();
        }
    }

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

    function logIn($username, $password){
        $conn = getConnection();
        if(isset($username) && isset($password)) {
            try {
                $result = $conn->prepare("SELECT password FROM user WHERE userName = :userName");
                $result->bindParam(':userName', $username);

                $result->execute();
                $return = $result->fetchAll();

                if(!empty($return) && password_verify($password, $return[0]['password'])){
                    $_SESSION['userName'] = $username;
                    unset($_SESSION['registerError']);
                    unset($_SESSION['loginError']);
                    header("location: watchlist.php");
                    return 1;
                }else{
                    return 0;
                }

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     *  Makes sure username and email have not been used yet
     *
     * @param $username
     * @param $email
     * @param $password
     * @return int
     */
    function register($username, $email, $password){
        $conn = getConnection();
        if(isset($username) && isset($password)) {
            try {
                $result = $conn->prepare("SELECT * FROM user WHERE userName = :userName OR email = :email");
                $result->bindParam(':userName', $username);
                $result->bindParam(':email', $email);

                $result->execute();
                $return = $result->fetchAll();

                if(empty($return)){
                    addUser($username, $email, password_hash($password, PASSWORD_DEFAULT));
                }else{
                    return 0;
                }

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     *  Adds user info to database
     *
     * @param $username
     * @param $email
     * @param $password - should be hashed already
     * @return int Returns 1 if successful
     */
    function addUser($username, $email, $password){
        $conn = getConnection();
        if(isset($username) && isset($password)) {
            try {
                $result = $conn->prepare("INSERT INTO user (userName, email, password) VALUES (:userName, :email, :password)");
                $result->bindParam(':userName', $username);
                $result->bindParam(':email', $email);
                $result->bindParam(':password', $password);

                $result->execute();

                $_SESSION['userName'] = $username;
                header("location: watchlist.php");
                return 1;

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    function addToWatchlist($username, $id){

        $conn = getConnection();

        if(isset($username) && isset($id)) {

            if(existsInWatchlist($username, $id)){
                return;
            }

            try {
                $result = $conn->prepare("INSERT INTO watchlist (movieID, userName) VALUES (:movieID, :userName)");
                $result->bindParam(':movieID', $id);
                $result->bindParam(':userName', $username);

                $result->execute();
                return;

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    function removeFromWatchlist($username, $id){

        $conn = getConnection();

        if(isset($username) && isset($id)) {


            if(!existsInWatchlist($username, $id)){
                return;
            }

            try {
                $result = $conn->prepare("DELETE FROM watchlist where movieID = :movieID AND userName = :userName");
                $result->bindParam(':movieID', $id);
                $result->bindParam(':userName', $username);

                $result->execute();
                return;

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * @return User's watchlist, DNE if there are none
     */
    function getWatchlist(){

        if(!isset($_POST['userName'])){
            return null;
        }

        $conn = getConnection();
        try {
            $result = $conn->prepare("SELECT * FROM watchlist w INNER JOIN movies m ON w.movieID = m.id WHERE w.userName = :userName");
            $result->bindParam(':userName', $_POST['userName']);

            $result->execute();
            $return = $result->fetchAll();

            if(!empty($return)){
                echo json_encode($return);
            }else{
                echo "DNE";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  Checks if there is any movie with the IMDB id '$id' in user's database
     *
     * @param $username
     * @param $id
     * @return bool|null
     */
    function existsInWatchlist($username, $id){

        if(!isset($username) || !isset($id)){
            return null;
        }

        $conn = getConnection();
        try {
            $result = $conn->prepare("SELECT 1 FROM watchlist w WHERE movieID = :id AND userName = :userName");
            $result->bindParam(':id', $id);
            $result->bindParam(':userName', $username);

            $result->execute();
            $return = $result->fetchAll();

            return (!empty($return));

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  Handles outside calls for adding/removing movies from user's watchlist
     */
    if(isset($_POST['addToWatchlist']) && isset($_SESSION['userName'])){
        addToWatchlist($_SESSION['userName'], $_POST['addToWatchlist']);
        echo "added";
        echo $_POST['addToWatchlist'];
    }else if(isset($_POST['removeWatchlist']) && isset($_SESSION['userName'])){
        removeFromWatchlist($_SESSION['userName'], $_POST['removeWatchlist']);
        echo "removed";
        echo $_POST['removeWatchlist'];
    }else{
        echo "";
    }

    function getRatings($sort){

        if(!isset($_SESSION['userName'])){
            return null;
        }

        $conn = getConnection();
        try {
            if(!isset($sort) || $sort != 0) {
                $result = $conn->prepare("SELECT * FROM ratings r INNER JOIN movies m ON r.movieID = m.id WHERE r.userName = :userName");
                $result->bindParam(':userName', $_POST['userName']);
            }else{
                $result = $conn->prepare("SELECT * FROM ratings r INNER JOIN movies m ON r.movieID = m.id WHERE r.userName = :userName AND r.rating = :sort");
                $result->bindParam(':userName', $_POST['userName']);
                $result->bindParam(':sort', $sort);
            }

            $result->execute();
            $return = $result->fetchAll();

            if(!empty($return)){
                echo json_encode($return);
            }else{
                echo "DNE";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    function existsRating($username, $id){

        if(!isset($username) || !isset($id)){
            return null;
        }

        $conn = getConnection();
        try {
            $result = $conn->prepare("SELECT 1 FROM rating WHERE movieID = :id AND userName = :userName");
            $result->bindParam(':id', $id);
            $result->bindParam(':userName', $username);

            $result->execute();
            $return = $result->fetchAll();

            /*if($rating != 0){
                return ($return[0] == $rating);
            }*/
            return (!empty($return));

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function addRating(){

        $conn = getConnection();

        if(isset($_SESSION['userName']) && isset($_POST['id'])) {
            $username = $_SESSION['userName'];
            $id = $_POST['id'];
            if(isset($_POST['rating']) && $_POST['rating'] != 0){
                $rating = $_POST['rating'];
            }else{
                $rating = 0;
            }
            try {
                if(existsRating($username, $id)) {
                    $result = $conn->prepare("UPDATE rating SET rating = :rating WHERE movieID = :movieID AND userName = :userName");
                    $result->bindParam(':movieID', $id);
                    $result->bindParam(':userName', $username);
                    $result->bindParam(':rating', $rating);
                }else{
                    $result = $conn->prepare("INSERT INTO rating (movieID, userName, rating) VALUES (:movieID, :userName, :rating)");
                    $result->bindParam(':movieID', $id);
                    $result->bindParam(':userName', $username);
                    $result->bindParam(':rating', $rating);
                }

                $result->execute();
                return;

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }


    $conn  = null;

?>