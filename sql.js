openKey = "";
movieKey = "";

// Loads passwords from file if not already and load page/check for updates
function initiate(tag, title, year) {
    printTime("First");

    if (openKey !== "") {

        hasOneDayPassed();

        if(tag === 'search'){
            search(title, year);
            return;
        }
        if(tag === 'getByGenre'){
            loadMovies(tag, title);
            return;
        }
        loadMovies(tag);
    }else{
    $.ajax({
        type: "POST",
        data: {"function": "getKeys"},
        url: "sql.php",
        success: function (passwords) {
            if (passwords !== null) {

                openKey = passwords.slice(0, 8);
                movieKey = passwords.slice(8);

                hasOneDayPassed();

                if(tag === 'search'){
                    search(title, year);
                   return;
                }
                if(tag === 'getByGenre'){
                    loadMovies(tag, title);
                    return;
                }
               loadMovies(tag);

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
                data: {"function":"shouldAddUpcoming","title":title},
                url: "sql.php",
                success: function(data){
                    if(data[0] == 0){
                        var movie = data.slice(1);
                        addMovie(movie, "2018");
                    }
                }
            });
        }
    });
}

// Adds movie by title, used after using getUpcoming as that returns title
// Adding by other criteria to be added
function addMovie(title, yea){

    var y = "";
    if(yea !== ""){
        y = "&y=" + yea;
    }

    var openDatabase = {
        "async": true,
        "crossDomain": true,
        "url": "https://www.omdbapi.com/?t='" + title + "'&type=movie" + y + "&plot=short&apikey=" + openKey,
        "method": "GET",
        "headers": {},
        "data": "{}"
    }

    $.ajax(openDatabase).done(function (movie) {

        printTime("Third");

        if (movie.Response === "True" && movie.imdbID !== null) {

            var realTitle= movie.Title;
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
                if(!poster.startsWith('/')){
                    poster = "/" + poster;
                }
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
                data: {"function":"addMovie", "id":id, "title": realTitle, "release_date":release_date, "year":year, "genre":genre, "imdb":imdb, "tomatoes":tomatoes, "metacritic":metacritic, "dvd_release":dvd_release, "runtime":runtime, "poster":poster, "summary":summary, "upcoming":upcoming, "latest":latest},
                url: "sql.php",
                success: function (data) {
                    printTime("Fourth");
                    if(upcoming){
                        loadMovies("getUpcoming");
                    }else{
                        loadMovies("search", realTitle, year);
                    }
                }
            });

        }else{
         //   loadMovies("search", title);
        }

    });
}

    // Load movies, currently getUpcoming and getLatest are options
    // edits HTML to display movies returned from list
    // (Could be cleaned up)
    function loadMovies(tag, title, year, page){

        var y = "";
        if(year !== ""){
            y = year;
        }
        if(page == null){
            page = 1;
        }

        $.ajax({
            type: "POST",
            data: {"function": tag, "search": title, "year": y, "page": page},
            url: "sql.php",
            success: function(moviesRAW) {

                printTime("Fifth");

                if (moviesRAW !== null && moviesRAW !== "[]") {

                    var movies = JSON.parse(moviesRAW);

                    if(page == 1){
                        document.getElementById("latest").innerHTML = "";
                    }
                    for (i in movies) {
                        printTime("Sixth");
                        var style = "";
                        if((i % 2) == 1){
                            style = "style='background-color: ghostwhite'";
                        }
                        if (movies[i].poster !== "") {
                            document.getElementById("latest").innerHTML += "<div class='row row-equal-height movie' id='" + movies[i].id + "'" + style + "></div>";

                            document.getElementById(movies[i].id).innerHTML += "<div class='col-sm-3 poster'><span><img src='https://m.media-amazon.com/images" + movies[i].poster + "' align='right'></span> </div><div class='col-sm-4 info'><div class='row' id='title'><b>" + movies[i].title + " (" + movies[i].year + ")</b></div> <div class='row'>" + createRating(movies[i].imdb, movies[i].tomatoes, movies[i].metacritic) + "<div class='col-sm-6' id='releaseDate'>" + movies[i].runtime + "&nbsp;|&nbsp;&nbsp;" + movies[i].release_date + "</div></div><div class='row summary'>&nbsp;&nbsp;" + movies[i].summary + "</div><div class='row addInfo'> Genre: " + movies[i].genre + "<br> Physical Release: " + movies[i].dvd_release + "</div>" +  getButtons(movies[i].id) + "</div>" + trailer(movies[i].id) + "</div>";
                        }

                    }

                    if(tag === "search") {
                        document.getElementById("latest").innerHTML += "<br> <h3>Not the movie you were looking for? </h3> <h4>Try adding the year!</h4><br>";
                    }
                }else{
                    if(tag === "getByGenre"){
                        // endless scrolling done
                        return;
                    }
                    if(tag !== "getUpcoming"){
                        document.getElementById("latest").innerHTML = "<h1> Movie '<b>" + title + "'</b> not found!</h1>";
                        addMovie(title, y);
                    }else{
                        document.getElementById("latest").innerHTML = "<h1> No <b>Upcoming Movies</b> found!</h1>";
                    }
                }
            }
        });
    }

    // Checks SQL database to get date of last time once per day function was called
    // If it was past today, run the function and update date
    function hasOneDayPassed(){

        $.ajax({
            type: "POST",
            data: {"function":"lastUpcomingCheck"},
            url: "sql.php",
            success: function(lastDate) {

                var date = new Date();
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
            data: {"function":"updateUpcomingCheck", "date":date},
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
            data: {"function":"getUpcoming"},
            url: "sql.php",
            success: function(moviesRAW) {

                if (moviesRAW !== null) {

                    var movies = JSON.parse(moviesRAW);

                    for (i in movies) {

                        if(!isUpcoming(movies[i].release_date)){

                            $.ajax({
                                type: "POST",
                                data: {"function":"removeUpcomingTag", "id":movies[i].id},
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
        data: {"function":"getMissingIMDB"},
        url: "sql.php",
        success: function (data) {

            if(data !== null){

                var movies = JSON.parse(data);

                for (i in movies) {

                    var openDatabase = {
                        "async": true,
                        "crossDomain": true,
                        "url": "https://www.omdbapi.com/?i=" + movies[i].id + "&type=movie&plot=short&apikey=" + openKey,
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
                                if(!poster.startsWith('/')){
                                    poster = "/" + poster;
                                }
                            }
                            var summary = movie.Plot;

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

                                    //document.getElementById("latest").innerText += data;

                                }
                            });

                        }

                    });

                }
            }

        }
    });
}

    function loadStars(movieID){

            return "<div class='rating'>\n" +
            "    <span><input type=\"radio\" name=" + movieID + " id='str5' value='5'><label for='str5'></label></span>\n" +
            "    <span><input type=\"radio\" name=" + movieID + " id=\"str4\" value='4'><label for=\"str4\"></label></span>\n" +
            "    <span><input type=\"radio\" name=" + movieID + " id=\"str3\" value='3'><label for=\"str3\"></label></span>\n" +
            "    <span><input type=\"radio\" name=" + movieID + " id=\"str2\" value='2'><label for=\"str2\"></label></span>\n" +
            "    <span><input type='radio' name=" + movieID + " id=\"str1\" value='1'><label for=\"str1\"></label></span>\n" +
            "</div>";
    }

function getButtons(id){
    var buttons = "<div class='row buttons'><button class='btn btn-primary'>Watch Trailer</button>";
        buttons += "<button class='btn btn-primary disabled'>Buy Tickets</button>";
        buttons += "<button name='addToWatchlist' class='btn btn-primary' value='" + id + "' id='watchlist" + id + "'>+ Watchlist</button>";

    buttons += "</div>";
        return buttons;
}

function trailer(id){

     var video = "<video width='320' height='240' controls> <source src='' type='video/mp4'> Your browser does not support this video. </video>";
    return "";
}

    // addMovie will search if movie exists in database, if not it will look up keyword and add it
    function search(title, year) {
        document.getElementById("latest").innerHTML = "";
        printTime("Second");
        if (title !== "" && year !== "") {
            loadMovies("search", title, year);
        } else if (title !== "") {
            loadMovies("search", title);
            loadMovies("search", title);
        }

    }

    // Pulls data in URL then initiates
    function extractSearch(url){

        let params = new URL(url).searchParams;

        if(params.has("t") && params.has("y")){
            initiate("search", params.get("t"), params.get("y"));
        }else if(params.get("t")){
            initiate("search", params.get("t"), "");
        }

    }

    // Called when Search bar is used
    // Will redirect to home page with title
    // Future option to be more specific such as year or genre?
    function redirect(title, year){
        var y = "";
        if(year !== ""){
             y = "&y=" + year;
        }
        window.location.replace("index.php?t=" + title + y);
    }



    $("#latest").on('click', 'button', function(e){
        e.preventDefault();

        let tag = $(this).attr("name");
        var data = {};
        data[tag] = $(this).attr("value");

        $.ajax({
            type: 'POST',
            url: 'userData.php',
            data: data ,
            success: function (success) {
                if(success.includes("removed")){
                    let buttonID = "#" + success.slice(7);
                    $(buttonID).fadeOut();
                }else if(success.includes("added")){
                    let buttonID = "#watchlist" + success.slice(5);
                    $(buttonID).text("Added!");
                    $(buttonID).addClass("disabled");
                }
            }
        });

    });

    // Listener for search function, redirects page with search info
    document.getElementById('search').addEventListener('submit',function(e) {
        e.preventDefault();

        let title = document.getElementById('search2').value;

        if(title != null && title !== ""){

            if(document.getElementById('search3') != null){
                let year = document.getElementById('search3').value;
                redirect(title, year);
                document.getElementById("search3").value = "";
            }else{
                redirect(title, "");
                document.getElementById("search2").value = "";
            }
        }
    });


    // Waits for rating clicking to change
    $('#latest').on('ready', '.buttons',function(){
        $(".rating input:radio").attr("checked", false);
    });

    $('#latest').on('click', '.rating input',function(){

        $(".rating span").removeClass('checked');
        $(this).parent().addClass('checked');

    });

    // On change, change user's rating for that movie
    // Will have to have movie's id in radio
    $('#latest').on('change', 'input:radio',function(){

        let userRating = this.value;
        $.ajax({
            type: 'POST',
            url: 'userData.php',
            data: {"function": "addRating", "rating": userRating, "id": $(this).attr("name")} ,
            success: function (success) {
            }
        });

    });


    function printTime(name){
        let date = new Date();
        //document.getElementById("latest").innerText += name + ": " + date.getSeconds() + "." + date.getMilliseconds();
    }