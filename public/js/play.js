var currenttrackid;
var audio;
var points;
var gamesid;

var playtitle = function () {
  $("#Start").addClass("invisible");
  

  try {
    audio.pause();
  } catch (e) {
    console.log("Can't stop the music");
  }
  $("input#YourGuess").first().val("");

  $.get("/blindtest/game/" + gamesid + "/currenttrack.json", function (jsondata) {
    audio = new Audio();
    audio.src = "/blindtest/play/" + jsondata.trackid + ".mp3";

    audio
      .play()
      .then(() => {
        //Hide the answer field
        $("#answer").addClass("invisible");
        //Allow interraction with sending the answer
        $("#Play").removeClass("invisible");
        
      })
      .catch((error) => {
        // alert("Please allow your browser to autoplay music");
        $("#MainPage").addClass("invisible");
        $("#BrowserError").removeClass("invisible");
      });
  });
};

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

function waitfor(seconds) {
  $("#waitbeforenext").html(seconds);
  var i = seconds;
  $("#btnsubmitanswer").prop("disabled", true);
  var countdown = setInterval(function () {
    i--;
    $("#waitbeforenext").html(i);
    if (i <= 0) {
      clearInterval(countdown);
      $("#btnsubmitanswer").prop("disabled", false);
      playtitle();
    }
  }, 1000);
}

var postcheckanswer = function (guessentered) {
  $.post("/blindtest/game/" + gamesid + "/check.json", {
    guess: guessentered,
  })
    .done(function (jsondata) {
      //Hide the answer field
      $("#Play").addClass("invisible");
      console.log(jsondata);
      console.log("Should check the answer");
      $("#trackimage").attr("src", jsondata.picture);
      $("#artistname").html(jsondata.artist);
      $("#titlename").html(jsondata.title);
      $("#artist").removeClass();
      $("#title").removeClass();
      checkartist = jsondata.checkartist;
      checktitle = jsondata.checktitle;
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
    }, "json")
    .fail(function () {
      alert("ERROR");
    });
};

var Catalog = (function () {
  var load_playlist = function () {
    points = 0;

    gamesid = $("#MainPage").attr("gamesid");
    console.log(gamesid);

    audio = new Audio();
    $("#startbutton").prop("disabled", false);
  };

  var handler_CheckAnswer = function () {
    $("form#FormGuess").submit(function (event) {
      guessentered = $("input#YourGuess").first().val().toLowerCase();
      console.log("Guess is " + guessentered);
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

$(document).ready(function () {
  Catalog.init();
});
