<!DOCTYPE html>
<html lang="en">

    <?php
        session_start();
    ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Latest Movies</title>
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
                <a class="navbar-brand" href="index.php">Moovies </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="upcoming.php">Upcoming <span class="sr-only">(current)</span></a></li>
                    <li class="active"><a href="latest.php">Latest</a></li>
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
                        echo "<li><a href='login.php'>Log In</a></li>";
                    }
                    ?>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>

    <div class="container" id="latest">
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="sql.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <script>
        initiate("getLatest");
    </script>

    </body>
</html>