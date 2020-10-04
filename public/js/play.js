

var Catalog = function () {
  

    var load_playlist = function(){
       playlistid=$('#main_page').attr('playlistid');
       console.log(playlistid);
                $.get('/deezer/playlist/'+playlistid+'/info.json', function (jsondata) {
                    console.log(jsondata.tracks[0]);
                     var audio = new Audio('/deezer/blindtest/play/'+jsondata.tracks[0]+'.mp3');
                     audio.play();
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
