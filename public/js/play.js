var currenttrackid;
var audio;
var points;
var gamesid;
var countdown;
var currentplaylistid;


/**
 * Countdown for this game
 */
var StartCountDown = function () {
  var seconds = 30;
  var i = seconds;
  $("#countdown").attr("aria-valuenow", 30);
  $("#countdown").attr("style", "width: 100%");

  countdown = setInterval(function () {
    i--;

    $("#countdown").attr("aria-valuenow", i);
    $("#countdown").attr("style", "width: " + Math.floor(i / seconds * 100) + "%");
    if (i <= 0) {
      // clearInterval(countdown);
      guessentered = $("input#YourGuess").first().val().toLowerCase();
      // console.log("Guess is " + guessentered);
      postcheckanswer(guessentered);
    }
  }, 1000);
}


/**
 * CountDown before next song
 * @param {integer} seconds 
 */
var waitfor = function (seconds) {
  $("#waitbeforenext").html(seconds);
  var i = seconds;
  $("#btnsubmitanswer").prop("disabled", true);
  var countdownwaitfor = setInterval(function () {
    i--;
    $("#waitbeforenext").html(i);
    if (i <= 0) {
      clearInterval(countdownwaitfor);
      $("#btnsubmitanswer").prop("disabled", false);
      playtitle();
    }
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
  $("#Start").addClass("invisible");
  //Try to set the JS audio player
  try {
    audio.pause();
  } catch (e) {
    console.log("Can't stop the music");
  }

  $("input#YourGuess").first().val("");
  $("#YourGuess").focus();
  $.get("/blindtest/game/" + gamesid + "/currenttrack.json", function (jsondata) {
    // console.log(audio);
    if (typeof audio === 'undefined') {
      // console.log("Creating new Audio Stream");
      audio = new Audio();
    }
    //We haven't reached the last song of the game. Play
    if (jsondata.trackid != -1) {
      points = jsondata.score;
      $("#currentscore").html(points);
      currenttrackid = jsondata.trackid;
      currentplaylistid = jsondata.playlistid;
      audio.src = "/blindtest/play/" + jsondata.trackid + ".mp3";

      audio
        .play()
        .then(() => {
          //Hide the answer field
          $("#answer").addClass("invisible");
          //Allow interraction with sending the answer
          $("#Play").removeClass("invisible");
          StartCountDown();
        })
        .catch((error) => {
          // alert("Please allow your browser to autoplay music");
          $("#MainPage").addClass("invisible");
          $("#BrowserError").removeClass("invisible");
          $.post("/errors/player", jsondata);
        });
    } else {
      //Last song of the game reached. Do an action
      alert("Last song reached. Final score is : ".jsondata.score);
    }
  });
};

/**
 * Handle the Stop or play of a song
 */
var playpause = function () {
  if (!audio.paused) {
    audio.pause();
    $("#audiocontroller>i.far.fa-pause-circle").addClass("invisible");
    $("#audiocontroller>i.far.fa-play-circle").removeClass("invisible");
  } else {
    audio.play();
    $("#audiocontroller>i.far.fa-pause-circle").removeClass("invisible");
    $("#audiocontroller>i.far.fa-play-circle").addClass("invisible");
  }
};



/**
 * Post the answer passed and get the response from server
 * @param {string} guessentered
 */
var postcheckanswer = function (guessentered) {
  //Stop CountDown.
  clearInterval(countdown);
  guessentered = removeAccentsAndSpecialChars(guessentered);
  $.post("/blindtest/game/" + gamesid + "/check.json", {
    guess: guessentered,
    trackid: currenttrackid,
  })
    .done(function (jsondata) {
      //Hide the answer field
      $("#Play").addClass("invisible");
      // console.log(jsondata);
      // console.log("Should check the answer");
      $("#trackimage").attr("src", jsondata.picture);
      $("#artistname").html(jsondata.artist);
      $("#titlename").html(jsondata.title);
      $("#track_link").attr("href", jsondata.track_link);
      $("#artist").removeClass();
      $("#title").removeClass();
      checkartist = jsondata.checkartist;
      checktitle = jsondata.checktitle;
      points = jsondata.score;
      if (checkartist) {
        $("#artist").addClass("alert alert-success");

      } else {
        $("#artist").addClass("alert alert-danger");
      }
      if (checktitle) {
        $("#title").addClass("alert alert-success");

      } else {
        $("#title").addClass("alert alert-danger");
      }
      $("#currentscore").html(points);
      $("#answer").removeClass("invisible");
      waitfor(4);
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
  var handler_CheckAnswer = function () {
    $("form#FormGuess").submit(function (event) {
      guessentered = $("input#YourGuess").first().val().toLowerCase();
      // console.log("Guess is " + guessentered);
      postcheckanswer(guessentered);

      event.preventDefault();
    });
  };

  return {
    init: function () {
      load_playlist();
      handler_CheckAnswer();
    },
  };
})();


/**
 * Load the Functions Catalog when the page is ready
 */
$(document).ready(function () {
  Catalog.init();
});
