var currenttrackid;
var audio;
var points;
var gamesid;
var countdown;
var timer;
var currentplaylistid;
var userid;
var writing;
var answergiven;
var everyoneready;

/**
 * Update hiscore during the game
 * @param {json} highscore 
 */
var updateHiscoreDisplay = function (highscore) {
  $("#highscorenickname").html(highscore.nickname);
  $("#highscore").html(highscore.score);
}

/**
 * Countdown for this game
 */
var StartCountDown = function (seconds) {
  var i = seconds;
  $("#countdown").attr("aria-valuenow", 30);
  $("#countdown").attr("style", "width: 100%");

  countdown = setInterval(function () {
    i--;

    $("#countdown").attr("aria-valuenow", i);
    $("#countdown").attr("style", "width: " + Math.floor(i / seconds * 100) + "%");
    $("#countdown").html(i + " s");
    if (i <= 0) {
      // clearInterval(countdown);
      // guessentered = $("input#YourGuess").first().val().toLowerCase();
      // console.log("Guess is " + guessentered);
      postcheckanswer("");
    }
  }, 1000);
}

var StartAnserTimer = function () {
  var time = 0;
  timer = setInterval(function () {
    $("#answerTimer").html((time / 100).toFixed(2) + " s");
    $("#answerTimer").attr('time-in-ms',time);
    time++;
  }, 10);
}

/**
 * CountDown before next song
 * @param {integer} seconds 
 */
var waitfor = function (seconds, object) {
  postready = false;
  //initialize the countdown

  //Let's start countdown
  var i = seconds;

  var countdownwaitfor = setInterval(function () {

    object.find('.progress-bar').attr('aria-valuenow', i);
    object.find('.progress-bar').attr('aria-valuenow', i);
    object.find('.progress-bar').css('width', (Math.round((i / seconds) * 100)) + "%");


    if (i <= 0) {
      clearInterval(countdownwaitfor);

      playtitle();

    }
    i--;
  }, 1000);
}

/**
 * Remove Accent and special chars
 * @param {string} input 
 */
var removeAccentsAndSpecialChars = function (input) {
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
  r = r.replace(/[^\w\d\s\'']/gi, ' ')
  return r;
}

/**
 * Play the title that is returned by currenttrack.json
 */
var playtitle = function () {
  writing = false;
  answergiven = false;
  everyoneready = false;
  $("#Start").addClass("invisible");
  $('#userspane').removeClass('invisible');
  $("#countdownprogress").removeClass("invisible");
  //Try to set the JS audio player
  try {
    audio.pause();
  } catch (e) {
    console.log("Can't stop the music");
  }


  $.get("/blindtest/game/" + gamesid + "/currenttrack.json", function (jsondata) {
    // console.log(audio);
    if (typeof audio === 'undefined') {
      // console.log("Creating new Audio Stream");
      audio = new Audio();
    }
    //We haven't reached the last song of the game. Play
    if (jsondata.trackid != -1) {
      $.get("/blindtest/game/" + gamesid + "/suggestions.html", function (suggestionsdata) {
        $('#suggestionspane').html(suggestionsdata);
        points = jsondata.score;
        $("#currentscore").html(points);
        updateHiscoreDisplay(jsondata.highscore);
        currenttrackid = jsondata.trackid;
        currentplaylistid = jsondata.playlistid;
        audio.src = "/blindtest/play/" + jsondata.trackid + ".mp3";
        audio.currentTime = Math.floor(jsondata.offset / 1000)
        audio
          .play()
          .then(() => {
            //Hide the answer field
            $("#answer").addClass("invisible");
            //Allow interraction with sending the answer
            $("#PlayTimer").removeClass("invisible");
            StartCountDown(30 - Math.floor(jsondata.offset / 1000));
            StartAnserTimer();
          })
          .catch((error) => {
            // alert("Please allow your browser to autoplay music");
            $("#MainPage").addClass("invisible");
            $("#BrowserError").removeClass("invisible");
            // console.log(jsondata);
            $("#ErrorMusicInfo").html("TrackID is : " + jsondata.trackid);
            $("#ErrorMusicInfo").attr("trackid", jsondata.trackid);
            $.post("/errors/player", jsondata);
          });
      });

    } else {
      //Last song of the game reached. Do an action
      $("#Finished").removeClass("invisible");
      $("#interactionpane").addClass("invisible");
      fireworks($("#fireworksplace")[0], false);
    }
  });
};

/**
 * Handle the Stop or play of a song
 */
var playpause = function () {
  if (!audio.paused) {
    audio.pause();
    $("#audiocontroller_mute").addClass("invisible");
    $("#audiocontroller_unmute").removeClass("invisible");
  } else {
    audio.play();
    $("#audiocontroller_mute").removeClass("invisible");
    $("#audiocontroller_unmute").addClass("invisible");
  }
};

/**
 * Move an object on another one on the page
 * @param {Object} sourceObject 
 * @param {Object} targetObject 
 * @param {integer} speedInSeconds 
 */
var moveObject = function (sourceObject, targetObject, speedInSeconds) {
  sourceObject.removeClass("hidden");
  sourceObject.css("transition", "left 1s ease-out, top " + speedInSeconds + "s ease-out");
  sourceObject.css("z-index", "999999");
  console.log(sourceObject);
  console.log(targetObject);
  //var target = $("#coinscore");
  var xTarget = targetObject.offset().left;
  var yTarget = targetObject.offset().top;
  // console.log(xTarget + " " + yTarget);


  var xSource = sourceObject.offset().left;
  var ySource = sourceObject.offset().top;
  // console.log("Source coordinates : " + xSource + " " + ySource);
  // set the elements position to their position for a smooth animation

  sourceObject.offset({ top: ySource, left: xSource });
  // set their position to the target position
  // the animation is a simple css transition
  sourceObject.offset({ top: yTarget, left: xTarget });
  setTimeout(function () {
    //Reset the object ot their original location
    sourceObject.offset({ top: ySource, left: xSource });
    //and hide them
    sourceObject.addClass("hidden");
  }, speedInSeconds * 1000);
}

/**
 * Skip the current song in case of error
 * @param {int} trackid 
 */
var skipCurrentSong = function (trackid) {
  trackid = $("#ErrorMusicInfo").attr("trackid");
  $.post("/blindtest/game/" + gamesid + "/skipsong.json", {
    trackid: trackid
  }).done(function (jsondata) {
    // console.log("Track " + trackid + " skipped, play the next");
    $("#MainPage").removeClass("invisible");
    $("#BrowserError").addClass("invisible");
    playtitle();
  });
}

/**
 * Post the answer passed and get the response from server
 * @param {string} guessentered
 */
var postcheckanswer = function (guessentered) {
  //Stop chrono
  clearInterval(timer);
  //Stop CountDown.
  clearInterval(countdown);

  answergiven = true;
  userid = $("#totalscore").attr('userid');
  // console.log("Post check answer : " + guessentered);
  $("#MainPage").removeClass("invisible");
  $("#BrowserError").addClass("invisible");


  guessentered = removeAccentsAndSpecialChars(guessentered);
  //Hide the Play field
  //$("#Play").addClass("invisible");
  //show the answer field
  time = $('#answerTimer').attr('time-in-ms');

  $.post("/blindtest/game/" + gamesid + "/check.json", {
    guess: guessentered,
    trackid: currenttrackid,
    timer: time
  })
    .done(function (jsondata) {
      updateHiscoreDisplay(jsondata.highscore);

      $('#PlayerTimer').addClass('invisible');

      check = jsondata.check;
      score = jsondata.score;
      totalscore = jsondata.totalscore;
      var countdownProgressBeforeNextSong = null;
      $('.submitanswer').each(function (index) {
        $(this).removeClass('card-light');
        //Correct answer in the list
        if ($(this).attr('trackid') == jsondata.trackid) {
          $(this).addClass('card-success');
          $(this).find('.image-container').removeClass('invisible');
          $(this).find('.track_link').attr("href", jsondata.track_link);
          $(this).find('.trackimage').attr("src", jsondata.picture);
          $(this).find('.progress').removeClass('invisible');
          countdownProgressBeforeNextSong = $(this).find('.progress');
        }
        //Wrong answer
        if ($(this).attr('trackid') == jsondata.guess && !check) {
          $(this).addClass('card-danger');
        }
        //Correct answer -- Do the animation for gold coins
        if ($(this).attr('trackid') == jsondata.guess && check) {
          // $(this).find('.points1').removeClass("invisible");
          // $(this).find('.points1').removeClass("invisible");
          //moveObject($(this).find('.points1'), $("#coinscore_" + userid), 1);
          // moveObject($(this).find('.points2'), $("#cointotalscore"), 1);
        }

      });


      console.log("USER ID : " + userid);
      $("#currentscore_" + userid).html(score);
      console.log("Points: " + score);
      console.log($("#currentscore_" + totalscore));
      $("#totalscore").html(totalscore);


      waitfor(4, countdownProgressBeforeNextSong);
    }, "json")
    .fail(function () {
      alert("ERROR");
    });
};

var Catalog = (function () {
  /**
   * Initialise the audio stream as document is ready
   */
  var load_playlist = function () {
    points = 0;

    gamesid = $("#MainPage").attr("gamesid");
    // console.log(gamesid);

    if (typeof audio === 'undefined') {
      // console.log("Creating new Audio Stream");
      audio = new Audio();
    }
    $("#startbutton").prop("disabled", false);
  };

  /**
   * Handler on the form if an answer has been entered
   */
  var HandlerCheckAnswer = function () {
    $('body').on('click', '.submitanswer', function (event) {


      if (!$(this).hasClass('boder')) {
        console.log($(this));
        $(this).addClass('boder border-primary border-5');
        guessentered = $(this).attr('trackid');
        console.log("SEND GUESS");
        postcheckanswer(guessentered);
      }
    });
  };

  var HandlerisWriting = function () {
    $('body').on('keyup', '#YourGuess', function () {
      if (!writing) {
        // console.log("Writing");
        $.post('/blindtest/game/' + gamesid + '/writing');
        writing = true;
      }
    });
  };

  var UpdatePlayerData = function (jsondata) {
    console.log("updating Players data");
    $('#userslist').html("");
    userid = jsondata.userid;
    delete jsondata.userid;
    everyoneready = true;
    jQuery.each(jsondata, function (i, val) {
      // console.log(val);
      var icon_writing = "";
      var icon_read = "";
      var icon_online = "";
      if (val.online) {
        everyoneready = everyoneready && val.status;
      } else {
        everyoneready = everyoneready && true;
      }

      //Stop this track, somebody has answered !
      if (val.answered && val.id != userid && !answergiven) {
        guessentered = $("input#YourGuess").first().val().toLowerCase();
        postcheckanswer(guessentered);
      }
      if (val.writing) { icon_writing = '<i class="fas fa-comment-dots"></i>'; }
      if (val.isready) { icon_read = '<i class="fas fa-check"></i>'; }
      if (val.online) { icon_online = '<i class="fas fa-globe green"></i>'; }
      scorevalue = '<img id="coinscore_' + userid + '" src="/img/goldcoin.png" width="20" height="20" alt=""><span id="currentscore_' + userid + '"> ' + val.score + '</span>';
      $('#userslist').append('<li class="list-group-item" userid="' + val.id + '">' + icon_writing + val.nickname + " " + icon_online + " " + scorevalue + " " + icon_read + '</li>');
    });
  };

  var UpdatePlayerstatus = function () {
    setInterval(function () {
      $.get("/blindtest/game/" + gamesid + "/updateplayers.json")
        .done(function (jsondata) {
          UpdatePlayerData(jsondata);

        });
    }, 2500);
  };

  var HandlerCheckPlayerMessages = function () {
    console.log("listening..");
    $.get("/blindtest/game/" + gamesid + "/updateplayers.json")
      .done(function (jsondata) {
        UpdatePlayerData(jsondata);
        HandlerCheckPlayerMessages();
      });
  };



  return {
    init: function () {
      load_playlist();
      HandlerCheckAnswer();
      // UpdatePlayerstatus();
      HandlerisWriting();
      HandlerCheckPlayerMessages();
    },
  };
})();


/**
 * Load the Functions Catalog when the page is ready
 */
$(document).ready(function () {
  Catalog.init();
});
