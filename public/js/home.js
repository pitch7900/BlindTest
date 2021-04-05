

var AddCustomPlaylist = function () {
    url = $("#CustomPlaylistLink").val();
    console.log(url);
    $.post("/deezer/playlist/add", {
        url: url
    }).done(function (jsondata) {
        // console.log(jsondata);
        document.location.reload();
    });

};



var Catalog = (function () {
    /**
     * Listen to the update playlist action button
     */
    var HandlerUpdatePlaylist = function () {
        $('body').on('click', '.updatetracksaction', function () {
            playlistid = $(this).attr('playlist');

            var currentobject = $(this);
            currentobject.parents("#carddata").addClass("invisible");
            currentobject.parents("#carddata").siblings("#loading").removeClass("invisible");

            $.post("/deezer/playlist/" + playlistid + "/updatetracks").done(function (jsondata) {
                currentobject.parents("#carddata").removeClass("invisible");
                currentobject.parents("#carddata").siblings("#loading").addClass("invisible");
                currentobject.siblings(".tracksnumber").html(jsondata.tracks);
            });

        });
    };
    /**
     * 
     */
    var HandlerFilterPlaylists = function () {
        $('body').on('keyup', '#FilterSongs', function () {
            var searched = $(this).val().toLowerCase();
            if (searched.length >= 3) {
                console.log("Searching for " + searched);
                $(".playlistname").each(function () {
                    title=($(this).html()).toLowerCase().trim();
                    console.log(" - "+title);
                    if (title.includes(searched)) {
                        console.log(title);
                        $(this).parents(".card").removeClass("invisible");
                    } else {
                        $(this).parents(".card").addClass("invisible");
                    }
                });
            } else {
                $(".playlistname").each(function () {
                    $(this).parents(".card").removeClass("invisible");
                });
            }
        });
    };
    var ClickOnPlayList = function (){
        $('body').on('click', '.playlistlink', function () {
            
            var WaitingModal = new bootstrap.Modal(document.getElementById('WaitingModal'), {
                keyboard: false
              });
              WaitingModal.show();
            //   $('#pulselocation').addClass("dot-pulse");
              
        });
    }
    return {
        init: function () {
            HandlerUpdatePlaylist();
            HandlerFilterPlaylists();
            ClickOnPlayList();
        }
    };
})();


/**
 * Load the Functions Catalog when the page is ready
 */
$(document).ready(function () {
    Catalog.init();
    var CreatingGameModal = new bootstrap.Modal(document.getElementById('CreatingGameModal'), {
        keyboard: false
      });
      CreatingGameModal.hide();
});
