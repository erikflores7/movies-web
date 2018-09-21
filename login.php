<!DOCTYPE html>
<html>

<?php
/**
 * Created by PhpStorm.
 * User: erikflores
 * Date: 8/24/18
 * Time: 5:45 PM
 */

include("userData.php");

    if(isset($_SESSION['userName'])){
        header('location: watchlist.php');
        return;
    }

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['userName']) && isset($_POST['password'])){
        $userName = $_POST['userName'];
        $password = $_POST['password'];
        if(!empty($userName) && !empty($password)) {
            if (logIn($_POST['userName'], $_POST['password']) == 0) {
                unset($_SESSION['registerError']);
                $_SESSION['loginError'] = "Username/Password is incorrect!";
            }
        }else{
            $_SESSION['loginError'] = 'Missing items!';
        }
    }
}


?>


<head>
    <title>Moovies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>

<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand active" href="index.php">Moovies </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a href="upcoming.php">Upcoming <span class="sr-only">(current)</span></a></li>
                <li><a href="latest.php">Latest</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Movies <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Top Rated</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="genre.php?genre=Horror">Horror </a></li>
                        <li><a href="genre.php?genre=Comedy">Comedy </a></li>
                        <li><a href="genre.php?genre=Action">Action </a></li>
                        <li><a href="genre.php?genre=Animation">Animation </a></li>

                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li>

                    <form class="navbar-form" onsubmit="return false" id="search">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Find Movies..." id="search2">
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                </li>

                <?php
                if(isset($_SESSION['userName'])){
                    echo "<li class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown' role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Account <span class=\"caret\"></span></a>
                        <ul class=\"dropdown-menu\">
                        <li><a href='watchlist.php'>Watchlist</a></li>
                        <li role=\"separator\" class=\"divider\"></li>
                        <li><a href='signout.php'>Sign Out </a></li>
                        </ul>
                       </li>";
                }else{
                    echo "<li class='active'><a href='login.php'>Log In</a></li>";
                }
                ?>
            </ul>


        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<header class="row">
    <div class="col-sm-4"></div>
    <div class="col-sm-4">
        <h1>Log In</h1>
    </div>
</header>
<br>

<div class="row">
    <div class="col-sm-4"></div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">

        <form method="post">
            <div class="form-group">
                <label>User Name</label>
                <input type="text" class="form-control" name="userName" placeholder="Username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <b class="errorMessage" id="loginError"> <?php if(isset($_SESSION['loginError'])){ echo $_SESSION['loginError']; }?></b>
            </div>
            <div class="form-check">
                <button type="submit" class="btn btn-primary">Log In</button>
            </div>

        </form>
        Don't have an account? <a href="register.php"> Register!</a>

    </div>

</div>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="userData.js"></script>
<script src="sql.js"></script>

</html>