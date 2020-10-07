var Catalog = function () {
  

    var load_playlists = function(){
        $.get('/blindtest/playlists.json', function (jsondata) {
  
            jsondata.forEach(element => {
                $.get('/deezer/playlist/'+element+'/cover.html', function (htmldata) {

                $('#playlists').append(htmldata);
                });
                
            });
        });
        
    }
    return {
        init: function () {
            load_playlists();
        }
    };
}();


$(document).ready(function () {
    Catalog.init();
});
