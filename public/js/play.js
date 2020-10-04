var currentlyplaying;
var currentplaylist;
var currentindex;
var audio;
var points;

var playtitle = function (index) {

    console.log(currentplaylist.tracks[index]);
    try {
        if (currentindex != 0) { audio.pause(); }
    } catch (e) {
        console.log("Can't stop the music");
    }
    $("input#YourGuess").first().val("");
    audio = new Audio('/deezer/blindtest/play/' + currentplaylist.tracks[index].id + '.mp3');
    currentlyplaying = currentplaylist.tracks[index];
    audio.play().then(() => {
        $("#answer").addClass("invisible");
        //Nothing to do
        $("#artistname").html(currentlyplaying.artist);
        $("#titlename").html(currentlyplaying.title);
    }).catch((error) => {
        // alert("Please allow your browser to autoplay music");
        $('#MainPage').addClass('invisible');
        $('#BrowserError').removeClass('invisible');
    });


};

function waitfor(seconds) {
    $("#waitbeforenext").html(seconds);
    i=seconds;
    (function loop(i) {
        setTimeout(function () {
            $("#waitbeforenext").html(i);
            if (--i) loop(i); // call the function until end
        }, 1000); // 1 second delay
    })();
    currentindex++;
    playtitle(currentindex);
}

function removeAccentsAndSpecialChars(input) {
    var r = input.toLowerCase();
    r = r.replace(new RegExp(/[àáâãäå]/g), "a");
    r = r.replace(new RegExp(/æ/g), "ae");
    r = r.replace(new RegExp(/ç/g), "c");
    r = r.replace(new RegExp(/[èéêë]/g), "e");
    r = r.replace(new RegExp(/[ìíîï]/g), "i");
    r = r.replace(new RegExp(/ñ/g), "n");
    r = r.replace(new RegExp(/[òóôõö]/g), "o");
    r = r.replace(new RegExp(/œ/g), "oe");
    r = r.replace(new RegExp(/[ùúûü]/g), "u");
    r = r.replace(new RegExp(/[ýÿ]/g), "y");
    r = r.replace(/[^\w\d\s]/gi, ' ')
    return r;
}

var Catalog = function () {
    var load_playlist = function () {
        points = 0;
        playlistid = $('#MainPage').attr('playlistid');
        console.log(playlistid);
        $.get('/deezer/playlist/' + playlistid + '/info.json', function (jsondata) {
            currentplaylist = jsondata;
            currentindex = 0;
            //play first title
            playtitle(currentindex);

        });
    };

    var handler_CheckAnswer = function () {
        $("form#FormGuess").submit(function (event) {
            guessentered = removeAccentsAndSpecialChars($("input#YourGuess").first().val().toLowerCase());
            console.log("Guess is " + guessentered);
            guesssplited = guessentered.split(" ");
            checkartist = false;
            checktitle = false;
            realartist = removeAccentsAndSpecialChars(currentlyplaying.artist);
            realtitle = removeAccentsAndSpecialChars(currentlyplaying.title);
            realartistsplitted = realartist.split(" ");
            realtitlesplitted = realtitle.split(" ");
            for (var i = 0; i < guesssplited.length; i++) {
                if (guesssplited[i].length<2) {break ;} //less than two chars don't compare
                if (realartist.indexOf(guesssplited[i]) != -1) {

                    checkartist = true;
                } else {
                    for (var j = 0; j < realartistsplitted.length; j++) {
                        if (getEditDistance(guesssplited[i], realartistsplitted[j]) <= 2) {
                            checkartist = true;
                        }
                    }
                }


                if (realtitle.indexOf(guesssplited[i]) != -1) {

                    checktitle = true;
                } else {
                    for (var j = 0; j < realtitlesplitted.length; j++) {
                        if (getEditDistance(guesssplited[i], realtitlesplitted[j]) <= 2) {
                            checktitle = true;
                        }
                    }
                }
                console.log("artist : " + currentlyplaying.artist.toLowerCase().indexOf(guesssplited[i]));
                console.log("title : " + currentlyplaying.title.toLowerCase().indexOf(guesssplited[i]));
                console.log(getEditDistance(guesssplited[i], currentlyplaying.artist));
                console.log(getEditDistance(guesssplited[i], currentlyplaying.title));
            }
            event.preventDefault();
            $("#artist").removeClass();
            $("#title").removeClass();
            if (checkartist) {
                $("#artist").addClass("form-control");
                $("#artist").addClass("is-valid");
                points++;
            } else {
                $("#artist").addClass("form-control");
                $("#artist").addClass("is-invalid");

            }
            if (checktitle) {
                $("#title").addClass("form-control");
                $("#title").addClass("is-valid");
                points++;
            } else {
                $("#title").addClass("form-control");
                $("#title").addClass("is-invalid");
            }
            $("#currentscore").html(points);
            $("#answer").removeClass("invisible");
            waitfor(4);
        });

    };


    return {
        init: function () {
            load_playlist();
            handler_CheckAnswer();
        }
    };
}();


$(document).ready(function () {
    Catalog.init();
});
