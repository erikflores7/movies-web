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

        }
    }

    $conn = null;

    function getConnection(){

        global $conn;

        if($conn != null){
            return $conn;
        }

        include 'config.php';

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }

    function logIn($username, $password){
        $conn = getConnection();
        if(isset($username) && isset($password)) {
            try {
                $result = $conn->prepare("SELECT 1 FROM user WHERE userName = :userName AND password = :password");
                $result->bindParam(':userName', $username);
                $result->bindParam(':password', $password);

                $result->execute();
                $return = $result->fetchAll();

                if(!empty($return)){
                    $_SESSION['userName'] = $username;
                    header("location: watchlist.php");
                    echo 1;
                }else{
                    echo "Does not exist!";
                }

                return;

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

    function getWatchlist(){

        if(!isset($_POST['userName'])){
            return null;
        }

        $conn = getConnection();
        try {
            $result = $conn->prepare("SELECT * FROM watchlist w INNER JOIN movies m ON w.movieID = m.id");
            $result->bindParam(':userName', $_POST['userName']);

            $result->execute();
            $return = $result->fetchAll();

            if(!empty($return)){
                echo json_encode($return);
            }else{
                echo "Does not exist!";
            }
            return;

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

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

    $conn  = null;

?>