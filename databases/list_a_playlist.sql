
SELECT playlisttracks.playlisttracks_playlist as playlist,
	playlisttracks_track as trackid,
	t_album.id,
	t_album.album_title,
	t_artist.id as artist_id,
	t_artist.artist_name
FROM playlisttracks
INNER JOIN (SELECT id, track_title,track_artist, track_album FROM track) t_track on t_track.id = playlisttracks.playlisttracks_track
INNER JOIN (SELECT id,artist_name FROM artist) t_artist on t_artist.id = t_track.track_artist
INNER JOIN (SELECT id,album_title FROM album) t_album on t_album.id = t_track.track_album
WHERE playlisttracks_playlist = 7752014202 

