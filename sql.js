openKey = "";
movieKey = "";

servername= "";
username = "";
password = "";
database = "";

// Loads passwords from file if not already and load page/check for updates
function initiate(tag, title) {

    if (openKey !== "") {
        if(tag === 'search'){
            search(title);
            return;
        }
        loadMovies(tag, title);
    }else{
    $.ajax({
        type: "GET",
        data: {},
        url: "passwords.json",
        success: function (passwords) {
            if (passwords !== null) {

                openKey = passwords.openmoviedb.api_key;
                movieKey = passwords.themoviedb.api_key;

                servername = passwords.sql.servername;

                username = passwords.sql.username;
                password = passwords.sql.password;

                database = passwords.sql.database;

                hasOneDayPassed();

                if(tag === 'search'){
                    search(title);
                   return;
                }
               loadMovies(tag, title);

            }
        }
    });
    }
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


// Gets list of "Upcoming Movies" from API
// However it returns movies already released as well as no imdbID
function getUpcomingMovies(){

    var upcomingMovies = {
        "async": true,
        "crossDomain": true,
        "url": "https://api.themoviedb.org/3/movie/upcoming?page=1&language=en-US&api_key=" + movieKey,
        "method": "GET",
        "headers": {},
        "data": "{}"
    }


    $.ajax(upcomingMovies).done(function (response) {

        var size = response.results.length - 1;

        for(i in response.results){

            var title = response.results[i].title;

            $.ajax({
                type: "POST",
                data: {"servername":servername,"username":username, "password":password, "database":database, "function":"shouldAddUpcoming","title":title},
                url: "sql.php",
                success: function(data){
                    if(data[0] == 0){
                        var movie = data.slice(1);
                        addMovie(movie, true);
                    }
                }
            });
        }
    });
}

// Adds movie by title, used after using getUpcoming as that returns title
// Adding by other criteria to be added
function addMovie(title, upcoming){

    var year = "";
    if(upcoming){
        year = "&y=2018";
    }

    var openDatabase = {
        "async": true,
        "crossDomain": true,
        "url": "http://www.omdbapi.com/?t='" + title + "'&type=movie" + year + "&plot=short&apikey=" + openKey,
        "method": "GET",
        "headers": {},
        "data": "{}"
    }

    $.ajax(openDatabase).done(function (movie) {

        if (movie.Response === "True" && movie.imdbID !== null) {

            title = movie.Title;
            var id = movie.imdbID;
            var release_date = movie.Released;
            var year = movie.Year;
            var genre = movie.Genre;
            var imdb = movie.imdbRating;
            var tomatoes = "";
            if(movie.Ratings !== null){
                for(i in movie.Ratings){
                    if(movie.Ratings[i].Source === "Rotten Tomatoes"){
                        tomatoes = movie.Ratings[i].Value;
                    }
                }
            }
            var metacritic = movie.Metascore;
            var dvd_release = movie.DVD;
            var runtime = movie.Runtime;
            var poster  = "";
            if(movie.Poster !== null){
                poster = movie.Poster.slice(33);
            }
            var summary = movie.Plot;

            var upcoming = 0;
            if(isUpcoming(release_date)){
                upcoming = 1;
            }
            var latest = 0;
            if(isLatest(release_date)){
                latest = 1;
            }

            $.ajax({
                type: "POST",
                data: {"servername":servername,"username":username, "password":password, "database":database, "function":"addMovie", "id":id, "title": title, "release_date":release_date, "year":year, "genre":genre, "imdb":imdb, "tomatoes":tomatoes, "metacritic":metacritic, "dvd_release":dvd_release, "runtime":runtime, "poster":poster, "summary":summary, "upcoming":upcoming, "latest":latest},
                url: "sql.php",
                success: function (data) {
                    if(upcoming){
                        loadMovies("getUpcoming");
                    }else{
                        loadMovies("search", title);
                    }
                }
            });

        }else{
            document.getElementById("latest").innerHTML = "<h1> Movie '<b>" + title + "'</b> not found!</h1>";
        }

    });
}

// Load movies, currently getUpcoming and getLatest are options
// edits HTML to display movies returned from list
// (Could be cleaned up)
function loadMovies(tag, title){

    $.ajax({
        type: "POST",
        data: {"servername":servername,"username":username, "password":password, "database":database, "function": tag, "search": title},
        url: "sql.php",
        success: function(moviesRAW) {

            if (moviesRAW !== null && moviesRAW !== "[]") {

                var movies = JSON.parse(moviesRAW);

                for (i in movies) {
                    if (movies[i].poster !== "") {
                        document.getElementById("latest").innerHTML += "<div class='row row-equal-height movie' id='" + i + "'></div>";

                        document.getElementById(i).innerHTML += "<div class='col-sm-3 poster'><span><img src='https://m.media-amazon.com/images" + movies[i].poster + "'></span> </div><div class='col-sm-4 info'><div class='row' id='title'><b>" + movies[i].title + " (" + movies[i].year + ")</b></div> <div class='row'>" + createRating(movies[i].imdb, movies[i].tomatoes, movies[i].metacritic) + "<div class='col-sm-6' id='releaseDate'>" + movies[i].runtime + "&nbsp;|&nbsp;&nbsp;" + movies[i].release_date + "</div></div><div class='row summary'>&nbsp;&nbsp;" + movies[i].summary + "</div><div class='row addInfo'> Genre: " + movies[i].genre + "<br> Physical Release: " + movies[i].dvd_release + "</div>" +  getButtons() + "</div>" + trailer(movies[i].id) + "</div>";
                    }

                }
            }
        }
    });
}

// Checks SQL database to get date of last time once per day function was called
// If it was past today, run the function and update date
function hasOneDayPassed(){

    var date = new Date();

    $.ajax({
        type: "POST",
        data: {"servername":servername,"username":username, "password":password, "database":database, "function":"lastUpcomingCheck"},
        url: "sql.php",
        success: function(lastDate) {

            var last = new Date(lastDate);

            if(date.getFullYear() > last.getFullYear()){
                runOncePerDay();
            }else if(date.getFullYear() >= last.getFullYear() && date.getMonth() > last.getMonth()){
                runOncePerDay();
            }else if(date.getFullYear() >= last.getFullYear() && date.getMonth() >= last.getMonth() && date.getDate() > last.getDate()){
                runOncePerDay();
            }
        }
    });

}

// Called only one time a day
// Will get list of Upcoming Movies from API
// Then remove any Upcoming movies that were released (Could be modified to be called once a week in future)
// Updates movies with missing imdbRating (can change to other missing data in future)
function runOncePerDay(){

    getUpcomingMovies();
    purgeUpcoming();
    updateMissing();

    var date = new Date().toLocaleDateString();

    $.ajax({
        type: "POST",
        data: {"servername":servername,"username":username, "password":password, "database":database, "function":"updateUpcomingCheck", "date":date},
        url: "sql.php",
        success: function(data) {

        }
    });
}


// Cleanses any movie with "Upcoming" that is no longer
// Same for latest soon
function purgeUpcoming(){

    $.ajax({
        type: "POST",
        data: {"servername":servername,"username":username, "password":password, "database":database, "function":"getUpcoming"},
        url: "sql.php",
        success: function(moviesRAW) {

            if (moviesRAW !== null) {

                var movies = JSON.parse(moviesRAW);

                for (i in movies) {

                    if(!isUpcoming(movies[i].release_date)){

                        $.ajax({
                            type: "POST",
                            data: {"servername":servername,"username":username, "password":password, "database":database, "function":"removeUpcomingTag", "id":movies[i].id},
                            url: "sql.php",
                            success: function(success) {
                            }
                        });
                    }
                }
            }
        }
    });

}


// Movie that just released or is yet to be released
function isUpcoming(date){

    var today = new Date();
    var movieRelease = new Date(date);

    if(movieRelease.getFullYear() > today.getFullYear()){
        return true;
    }else if(movieRelease.getFullYear() >= today.getFullYear() && movieRelease.getMonth() > today.getMonth()){
        return true;
    }else if(movieRelease.getFullYear() >= today.getFullYear() && movieRelease.getMonth() >= today.getMonth() && movieRelease.getDate() >= today.getDate()){
        return true;
    }
    return false;
}

// Criteria for being considered "latest", 3 month old movies or newer
function isLatest(date){
    var today = new Date();
    var movieRelease = new Date(date);

    return (movieRelease.getFullYear() >= today.getFullYear() && movieRelease.getMonth() >= (today.getMonth() - 3));
}



// Gets list of movies missing IMDB rating, then will look them up using api, one by one, updating each
// Other missing data could be added to be checked in future but this seems to be good for now
function updateMissing(){

    $.ajax({
        type: "POST",
        data: {"servername":servername,"username":username, "password":password, "database":database, "function":"getMissingIMDB"},
        url: "sql.php",
        success: function (data) {

            if(data !== null){

                var movies = JSON.parse(data);

                for (i in movies) {

                    var openDatabase = {
                        "async": true,
                        "crossDomain": true,
                        "url": "http://www.omdbapi.com/?i=" + movies[i].id + "&type=movie&plot=short&apikey=" + openKey,
                        "method": "GET",
                        "headers": {},
                        "data": "{}"
                    }

                    $.ajax(openDatabase).done(function (movie) {

                        if (movie.Response === "True") {

                            var id = movie.imdbID;
                            var release_date = movie.Released;
                            var year = movie.Year;
                            var genre = movie.Genre;
                            var imdb = movie.imdbRating;
                            var tomatoes = "";
                            if (movie.Ratings !== null) {
                                for (i in movie.Ratings) {
                                    if (movie.Ratings[i].Source === "Rotten Tomatoes") {
                                        tomatoes = movie.Ratings[i].Value;
                                    }
                                }
                            }
                            var metacritic = movie.Metascore;
                            var dvd_release = movie.DVD;
                            var runtime = movie.Runtime;
                            var poster = "";
                            if (movie.Poster !== null) {
                                poster = movie.Poster.slice(33);
                            }
                            var summary = movie.Plot;
                            document.getElementById("latest").innerText += "Length: " + summary.length + "   ";


                            var upcoming = 0;
                            if (isUpcoming(release_date)) {
                                upcoming = 1;
                            }
                            var latest = 0;
                            if (isLatest(release_date)) {
                                latest = 1;
                            }

                            $.ajax({
                                type: "POST",
                                data: {
                                    "servername": servername,
                                    "username": username,
                                    "password": password,
                                    "database": database,
                                    "function": "updateMovie",
                                    "id": id,
                                    "release_date": release_date,
                                    "year": year,
                                    "genre": genre,
                                    "imdb": imdb,
                                    "tomatoes": tomatoes,
                                    "metacritic": metacritic,
                                    "dvd_release": dvd_release,
                                    "runtime": runtime,
                                    "poster": poster,
                                    "summary": summary,
                                    "upcoming": upcoming,
                                    "latest": latest
                                },
                                url: "sql.php",
                                success: function (data) {

                                    document.getElementById("latest").innerText += data;

                                }
                            });

                        }

                    });

                }
            }

        }
    });
}

function getButtons(){
    var buttons = "<div class='row buttons'><button class='btn btn-primary'>Watch Trailer</button>";
        buttons += "<button class='btn btn-primary disabled'>Buy Tickets</button>";
        buttons += "</div>";
        return buttons;
}

function trailer(id){

     var video = "<video width='320' height='240' controls> <source src='' type='video/mp4'> Your browser does not support this video. </video>";
    return "";
}


    // addMovie will search if movie exists in database, if not it will look up keyword and add it
    // Changed addMovie to load movies once again after adding new, reset old loaded movies
    // Might need to redirect to home page/movie page instead of staying on Latest/Upcoming
    function search(search){

    document.getElementById("latest").innerText = "";
     addMovie(search, false);
    }

    document.getElementById('search').addEventListener('submit',function(e) {
    e.preventDefault();

    var keywords = document.getElementById('search2').value;
    if(keywords != null && keywords !== ""){
    initiate("search", keywords);
    }
    });