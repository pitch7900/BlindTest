

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
     * Initialise the audio stream as document is ready
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

    return {
        init: function () {
            HandlerUpdatePlaylist();
        }
    };
})();


/**
 * Load the Functions Catalog when the page is ready
 */
$(document).ready(function () {
    Catalog.init();
});
