
    function loadMovies(tag, userName){

        $.ajax({
            type: "POST",
            data: {"function": tag, "userName": userName},
            url: "userData.php",
            success: function(moviesRAW) {

                if (moviesRAW !== null && moviesRAW !== "[]" && moviesRAW !== "DNE") {

                    var movies = JSON.parse(moviesRAW);

                    for (i in movies) {

                        var style = "";
                        if((i % 2) == 1){
                            style = "style='background-color: ghostwhite'";
                        }

                        if (movies[i].poster !== "") {

                            document.getElementById("latest").innerHTML += "<div class='row row-equal-height movie' id='" + movies[i].id + "'" + style + "></div>";

                            document.getElementById(movies[i].id).innerHTML += "<div class='col-sm-3 poster'><span><img src='https://m.media-amazon.com/images" + movies[i].poster + "' align='right'></span> </div><div class='col-sm-4 info'><div class='row' id='title'><b>" + movies[i].title + " (" + movies[i].year + ")</b></div> <div class='row'>" + createRating(movies[i].imdb, movies[i].tomatoes, movies[i].metacritic) + "<div class='col-sm-6' id='releaseDate'>" + movies[i].runtime + "&nbsp;|&nbsp;&nbsp;" + movies[i].release_date + "</div></div><div class='row summary'>&nbsp;&nbsp;" + movies[i].summary + "</div><div class='row addInfo'> Genre: " + movies[i].genre + "<br> Physical Release: " + movies[i].dvd_release + "</div>" +  /*loadStars(movies[i].id) +*/ getButtons(movies[i].id) + "</div>" + trailer(movies[i].id) + "</div>";
                        }
                    }

                }else{
                        document.getElementById("latest").innerHTML = "<h3> You have no movies on your <b>Watchlist</b>!</h3>";
                }
            }
        });
    }


    // function to clean up HTML editing a bit
    // Goes through ratings and either adds/styles or puts N/A
    function createRating(imdb, tomatoes, metacritic){

        var stars = "<div class='col-sm-6'><ul class='list-inline'>";

        if(imdb !== "N/A") {
            stars += "<li class='imdb'><span class='glyphicon glyphicon-star checked' aria-hidden='true'></span> " + imdb + "</li>";
        }else{
            stars += "<li class='imdb'><span class='glyphicon glyphicon-star' aria-hidden='true'></span> N/A </li>";
        }
        if(tomatoes !== "") {
            stars += "<li class='tomatoes'><img src='https://www.rottentomatoes.com/assets/pizza-pie/images/icons/global/new-fresh.587bf3a5e47.png' style='max-height: 16px'> " + tomatoes + "</li>";
        }else{
            stars += "<li class='tomatoes'><img src='https://www.rottentomatoes.com/assets/pizza-pie/images/icons/global/new-fresh.587bf3a5e47.png' style='max-height: 16px'>  N/A </li>";
        }
        if(metacritic !== "N/A"){
            if(metacritic < 70) {
                stars += "<li class='metacritic ok'>" + metacritic + "</li>";
            }else{
                stars += "<li class='metacritic'>" + metacritic + "</li>";
            }
        }
        stars += "</ul></div>";
        return stars;
    }



    function getButtons(id){
        var buttons = "<div class='row buttons'><button class='btn btn-primary'>Watch Trailer</button>";
        buttons += "<button class='btn btn-primary disabled'>Buy Tickets</button>";
        buttons += "<button type='submit' name='removeWatchlist' class='btn btn-primary' value='" + id + "' id='watchlist" + id + "'>- Watchlist</button>";
        //buttons += "<button class='btn btn-primary'>Rate</button>";
        buttons += "</div>";
        return buttons;
    }

    function trailer(id){
        var video = "<video width='320' height='240' controls> <source src='' type='video/mp4'> Your browser does not support this video. </video>";
        return "";
    }

    function notSignedIn(){
        document.getElementById("latest").innerHTML = "<h1>You must be signed in to view your Watchlist!</h1><h2> <a href='login.php'>Sign in</a></h2>";
    }