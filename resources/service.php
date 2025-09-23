<?php

declare(strict_types=1);

return [
    'baseUrl' => 'http://ws.audioscrobbler.com/2.0/',
    'operations' => [
        // ===========================
        // ALBUM METHODS
        // ===========================
        'addAlbumTags' => [
            'httpMethod' => 'POST',
            'method' => 'album.addTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'album' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'getAlbumInfo' => [
            'httpMethod' => 'GET',
            'method' => 'album.getInfo',
            'parameters' => [
                'artist' => ['required' => false],
                'album' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
                'lang' => ['required' => false],
            ],
        ],
        'getAlbumTags' => [
            'httpMethod' => 'GET',
            'method' => 'album.getTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => false],
                'album' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => true],
            ],
        ],
        'getAlbumTopTags' => [
            'httpMethod' => 'GET',
            'method' => 'album.getTopTags',
            'parameters' => [
                'artist' => ['required' => false],
                'album' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'removeAlbumTag' => [
            'httpMethod' => 'POST',
            'method' => 'album.removeTag',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'album' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],
        'searchAlbums' => [
            'httpMethod' => 'GET',
            'method' => 'album.search',
            'parameters' => [
                'album' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // ARTIST METHODS
        // ===========================
        'addArtistTags' => [
            'httpMethod' => 'POST',
            'method' => 'artist.addTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'getArtistCorrection' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getCorrection',
            'parameters' => [
                'artist' => ['required' => true],
            ],
        ],
        'getArtistInfo' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getInfo',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'lang' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
            ],
        ],
        'getSimilarArtists' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getSimilar',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getArtistTags' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => true],
            ],
        ],
        'getArtistTopAlbums' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getTopAlbums',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getArtistTopTags' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getTopTags',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'getArtistTopTracks' => [
            'httpMethod' => 'GET',
            'method' => 'artist.getTopTracks',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'removeArtistTag' => [
            'httpMethod' => 'POST',
            'method' => 'artist.removeTag',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],
        'searchArtists' => [
            'httpMethod' => 'GET',
            'method' => 'artist.search',
            'parameters' => [
                'artist' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // CHART METHODS
        // ===========================
        'getTopArtistsChart' => [
            'httpMethod' => 'GET',
            'method' => 'chart.getTopArtists',
            'parameters' => [
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTopTagsChart' => [
            'httpMethod' => 'GET',
            'method' => 'chart.getTopTags',
            'parameters' => [
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTopTracksChart' => [
            'httpMethod' => 'GET',
            'method' => 'chart.getTopTracks',
            'parameters' => [
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],

        // ===========================
        // GEO METHODS
        // ===========================
        'getTopArtistsByCountry' => [
            'httpMethod' => 'GET',
            'method' => 'geo.getTopArtists',
            'parameters' => [
                'country' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTopTracksByCountry' => [
            'httpMethod' => 'GET',
            'method' => 'geo.getTopTracks',
            'parameters' => [
                'country' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],

        // ===========================
        // LIBRARY METHODS
        // ===========================
        'getLibraryArtists' => [
            'httpMethod' => 'GET',
            'method' => 'library.getArtists',
            'requiresAuth' => true,
            'parameters' => [
                'user' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],

        // ===========================
        // TAG METHODS
        // ===========================
        'getTagInfo' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getInfo',
            'parameters' => [
                'tag' => ['required' => true],
                'lang' => ['required' => false],
            ],
        ],
        'getSimilarTags' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getSimilar',
            'parameters' => [
                'tag' => ['required' => true],
            ],
        ],
        'getTagTopAlbums' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getTopAlbums',
            'parameters' => [
                'tag' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTagTopArtists' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getTopArtists',
            'parameters' => [
                'tag' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTopTags' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getTopTags',
            'parameters' => [],
        ],
        'getTagTopTracks' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getTopTracks',
            'parameters' => [
                'tag' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTagWeeklyChartList' => [
            'httpMethod' => 'GET',
            'method' => 'tag.getWeeklyChartList',
            'parameters' => [
                'tag' => ['required' => true],
            ],
        ],

        // ===========================
        // TRACK METHODS
        // ===========================
        'addTrackTags' => [
            'httpMethod' => 'POST',
            'method' => 'track.addTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'getTrackCorrection' => [
            'httpMethod' => 'GET',
            'method' => 'track.getCorrection',
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
            ],
        ],
        'getTrackInfo' => [
            'httpMethod' => 'GET',
            'method' => 'track.getInfo',
            'parameters' => [
                'artist' => ['required' => false],
                'track' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
            ],
        ],
        'getSimilarTracks' => [
            'httpMethod' => 'GET',
            'method' => 'track.getSimilar',
            'parameters' => [
                'artist' => ['required' => false],
                'track' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getTrackTags' => [
            'httpMethod' => 'GET',
            'method' => 'track.getTags',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => false],
                'track' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => true],
            ],
        ],
        'getTrackTopTags' => [
            'httpMethod' => 'GET',
            'method' => 'track.getTopTags',
            'parameters' => [
                'artist' => ['required' => false],
                'track' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'loveTrack' => [
            'httpMethod' => 'POST',
            'method' => 'track.love',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
            ],
        ],
        'removeTrackTag' => [
            'httpMethod' => 'POST',
            'method' => 'track.removeTag',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],
        'scrobbleTrack' => [
            'httpMethod' => 'POST',
            'method' => 'track.scrobble',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'timestamp' => ['required' => true],
                'album' => ['required' => false],
                'albumArtist' => ['required' => false],
                'duration' => ['required' => false],
                'streamId' => ['required' => false],
                'chosenByUser' => ['required' => false],
                'trackNumber' => ['required' => false],
                'mbid' => ['required' => false],
            ],
        ],
        'searchTracks' => [
            'httpMethod' => 'GET',
            'method' => 'track.search',
            'parameters' => [
                'track' => ['required' => true],
                'artist' => ['required' => false],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'unloveTrack' => [
            'httpMethod' => 'POST',
            'method' => 'track.unlove',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
            ],
        ],
        'updateNowPlaying' => [
            'httpMethod' => 'POST',
            'method' => 'track.updateNowPlaying',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'album' => ['required' => false],
                'albumArtist' => ['required' => false],
                'duration' => ['required' => false],
                'trackNumber' => ['required' => false],
                'mbid' => ['required' => false],
            ],
        ],

        // ===========================
        // USER METHODS
        // ===========================
        'getUserFriends' => [
            'httpMethod' => 'GET',
            'method' => 'user.getFriends',
            'parameters' => [
                'user' => ['required' => true],
                'recenttracks' => ['required' => false],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getUserInfo' => [
            'httpMethod' => 'GET',
            'method' => 'user.getInfo',
            'parameters' => [
                'user' => ['required' => false],
            ],
        ],
        'getUserLovedTracks' => [
            'httpMethod' => 'GET',
            'method' => 'user.getLovedTracks',
            'parameters' => [
                'user' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getUserPersonalTags' => [
            'httpMethod' => 'GET',
            'method' => 'user.getPersonalTags',
            'requiresAuth' => true,
            'parameters' => [
                'user' => ['required' => true],
                'tag' => ['required' => true],
                'taggingtype' => ['required' => true],
                'page' => ['required' => false],
                'limit' => ['required' => false],
            ],
        ],
        'getUserRecentTracks' => [
            'httpMethod' => 'GET',
            'method' => 'user.getRecentTracks',
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
                'from' => ['required' => false],
                'to' => ['required' => false],
                'extended' => ['required' => false],
            ],
        ],
        'getUserTopAlbums' => [
            'httpMethod' => 'GET',
            'method' => 'user.getTopAlbums',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getUserTopArtists' => [
            'httpMethod' => 'GET',
            'method' => 'user.getTopArtists',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getUserTopTags' => [
            'httpMethod' => 'GET',
            'method' => 'user.getTopTags',
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
            ],
        ],
        'getUserTopTracks' => [
            'httpMethod' => 'GET',
            'method' => 'user.getTopTracks',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getUserWeeklyAlbumChart' => [
            'httpMethod' => 'GET',
            'method' => 'user.getWeeklyAlbumChart',
            'parameters' => [
                'user' => ['required' => true],
                'from' => ['required' => false],
                'to' => ['required' => false],
            ],
        ],
        'getUserWeeklyArtistChart' => [
            'httpMethod' => 'GET',
            'method' => 'user.getWeeklyArtistChart',
            'parameters' => [
                'user' => ['required' => true],
                'from' => ['required' => false],
                'to' => ['required' => false],
            ],
        ],
        'getUserWeeklyChartList' => [
            'httpMethod' => 'GET',
            'method' => 'user.getWeeklyChartList',
            'parameters' => [
                'user' => ['required' => true],
            ],
        ],
        'getUserWeeklyTrackChart' => [
            'httpMethod' => 'GET',
            'method' => 'user.getWeeklyTrackChart',
            'parameters' => [
                'user' => ['required' => true],
                'from' => ['required' => false],
                'to' => ['required' => false],
            ],
        ],
    ],
    'client' => [
        'class' => 'GuzzleHttp\Client',
        'options' => [
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'LastFmClient/2.0.0 +https://github.com/calliostro/lastfm-client',
                'Accept' => 'application/json',
            ],
        ],
    ],
];
