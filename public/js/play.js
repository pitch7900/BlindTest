
var Catalog = function () {


    var load_playlist = function () {
        playlistid = $('#MainPage').attr('playlistid');
        console.log(playlistid);
        $.get('/deezer/playlist/' + playlistid + '/info.json', function (jsondata) {
            console.log(jsondata.tracks[0]);

            var audio = new Audio('/deezer/blindtest/play/' + jsondata.tracks[0] + '.mp3');
            audio.play().then(() => {
                //Nothing to do
            }).catch((error) => {
               // alert("Please allow your browser to autoplay music");
                $('#MainPage').addClass('invisible');
                $('#BrowserError').removeClass('invisible');
            });

        });


    }
    return {
        init: function () {
            load_playlist();
        }
    };
}();


$(document).ready(function () {
    Catalog.init();
});
