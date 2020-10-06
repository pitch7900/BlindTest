var currentlyplaying;
var currentplaylist;
var currentindex;
var audio;
var points;

var playtitle = function (index) {
    $('#Start').addClass('invisible');
    $('#Play').removeClass('invisible');
    console.log(currentplaylist.tracks[index]);
    try {
        if (currentindex != 0) { audio.pause(); }
    } catch (e) {
        console.log("Can't stop the music");
    }
    $("input#YourGuess").first().val("");
    audio.src='/deezer/blindtest/play/' + currentplaylist.tracks[index].id + '.mp3';
    currentlyplaying = currentplaylist.tracks[index];
    audio.play().then(() => {
        $("#answer").addClass("invisible");
        //Nothing to do
        $("#artistname").html(currentlyplaying.artist);
        $("#titlename").html(currentlyplaying.title);
        $("#trackimage").attr('src',currentlyplaying.coverurl);
    }).catch((error) => {
        // alert("Please allow your browser to autoplay music");
        $('#MainPage').addClass('invisible');
        $('#BrowserError').removeClass('invisible');
    });
};


var playpause = function (){
    if (!audio.paused) {
        audio.pause();
        $('#audiocontroller>i.far.fa-pause-circle').addClass('invisible');
        $('#audiocontroller>i.far.fa-play-circle').removeClass('invisible');
    } else {
        audio.play();
        $('#audiocontroller>i.far.fa-pause-circle').removeClass('invisible');
        $('#audiocontroller>i.far.fa-play-circle').addClass('invisible');
    }
}

function waitfor(seconds) {
    $("#waitbeforenext").html(seconds);
    var i = seconds;
    $('#btnsubmitanswer').prop('disabled', true);
    var countdown = setInterval(function () {
        i--;
        $("#waitbeforenext").html(i);
        if (i <= 0) {
            clearInterval(countdown);
            currentindex++;
            $('#btnsubmitanswer').prop('disabled', false);
            playtitle(currentindex);
        }
    }, 1000);
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
    r = r.replace(new RegExp(/[-\/]/g), "");
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
            audio=new Audio();
            //play first title
            $('#startbutton').prop('disabled', false);

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
                console.log("Guess Splited :"+guesssplited[i]+" ("+guesssplited[i].length+")");
                if (guesssplited[i].length<=1) {break;}
                if (guesssplited[i].length > 1 && guesssplited[i].length <= 4) {
                    for (var j = 0; j < realartistsplitted.length; j++) {
                        if (guesssplited[i] === realartistsplitted[j]) {
                            console.log(guesssplited[i] +','+ realartistsplitted[j])
                            checkartist = true;
                        }
                    }
                    for (var j = 0; j < realtitlesplitted.length; j++) {
                        if (guesssplited[i] === realtitlesplitted[j]) {
                            console.log(guesssplited[i] +','+ realtitlesplitted[j])
                            checktitle = true;
                        }
                    }

                } else {

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
                $("#artist").addClass("alert alert-success");

                points++;
            } else {
                $("#artist").addClass("alert alert-danger");


            }
            if (checktitle) {
                $("#title").addClass("alert alert-success");

                points++;
            } else {
                $("#title").addClass("alert alert-danger");

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
