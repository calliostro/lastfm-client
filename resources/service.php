<?php

return [
    'baseUrl' => 'https://ws.audioscrobbler.com/2.0/',
    'operations' => [
        // ===========================
        // ALBUM METHODS
        // ===========================
        'album.getInfo' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'album' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
                'lang' => ['required' => false],
            ],
        ],
        'album.getTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => true],
                'album' => ['required' => true],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => false],
            ],
        ],
        'album.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'album' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'album.search' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'album' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'album.addTags' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'album' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'album.removeTag' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'album' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],

        // ===========================
        // ARTIST METHODS
        // ===========================
        'artist.getInfo' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
                'lang' => ['required' => false],
            ],
        ],
        'artist.getSimilar' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'limit' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'artist.getTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => false],
            ],
        ],
        'artist.getTopAlbums' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'artist.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'artist.getTopTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'artist.search' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'artist.getCorrection' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => true],
            ],
        ],
        'artist.addTags' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'artist.removeTag' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],

        // ===========================
        // AUTH METHODS
        // ===========================
        'auth.getMobileSession' => [
            'httpMethod' => 'POST',
            'requiresSignature' => true,
            'parameters' => [
                'username' => ['required' => true],
                'password' => ['required' => true],
            ],
        ],
        'auth.getSession' => [
            'httpMethod' => 'GET',
            'requiresSignature' => true,
            'parameters' => [
                'token' => ['required' => true],
            ],
        ],
        'auth.getToken' => [
            'httpMethod' => 'GET',
            'requiresSignature' => true,
            'parameters' => [],
        ],

        // ===========================
        // CHART METHODS
        // ===========================
        'chart.getTopArtists' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'chart.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'chart.getTopTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // GEO METHODS
        // ===========================
        'geo.getTopArtists' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'country' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'geo.getTopTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'country' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
                'location' => ['required' => false],
            ],
        ],

        // ===========================
        // LIBRARY METHODS
        // ===========================
        'library.getArtists' => [
            'httpMethod' => 'GET',
            'requiresAuth' => true,
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // TAG METHODS
        // ===========================
        'tag.getInfo' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
                'lang' => ['required' => false],
            ],
        ],
        'tag.getSimilar' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
            ],
        ],
        'tag.getTopAlbums' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'tag.getTopArtists' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'tag.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'tag.getTopTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'tag.getWeeklyChartList' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'tag' => ['required' => true],
            ],
        ],

        // ===========================
        // TRACK METHODS
        // ===========================
        'track.addTags' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'tags' => ['required' => true],
            ],
        ],
        'track.getCorrection' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
            ],
        ],
        'track.getInfo' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'track' => ['required' => false],
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'username' => ['required' => false],
            ],
        ],
        'track.getSimilar' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'track' => ['required' => false],
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'limit' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'track.getTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'track' => ['required' => false],
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
                'user' => ['required' => false],
            ],
        ],
        'track.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'track' => ['required' => false],
                'artist' => ['required' => false],
                'mbid' => ['required' => false],
                'autocorrect' => ['required' => false],
            ],
        ],
        'track.love' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'track' => ['required' => true],
                'artist' => ['required' => true],
            ],
        ],
        'track.removeTag' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'tag' => ['required' => true],
            ],
        ],
        'track.scrobble' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'timestamp' => ['required' => true],
                'album' => ['required' => false],
                'albumArtist' => ['required' => false],
                'trackNumber' => ['required' => false],
                'mbid' => ['required' => false],
                'duration' => ['required' => false],
                'streamId' => ['required' => false],
                'chosenByUser' => ['required' => false],
                'context' => ['required' => false],
            ],
        ],
        'track.search' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'track' => ['required' => true],
                'artist' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'track.unlove' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'track' => ['required' => true],
                'artist' => ['required' => true],
            ],
        ],
        'track.updateNowPlaying' => [
            'httpMethod' => 'POST',
            'requiresAuth' => true,
            'parameters' => [
                'artist' => ['required' => true],
                'track' => ['required' => true],
                'album' => ['required' => false],
                'albumArtist' => ['required' => false],
                'trackNumber' => ['required' => false],
                'mbid' => ['required' => false],
                'duration' => ['required' => false],
                'context' => ['required' => false],
            ],
        ],

        // ===========================
        // USER METHODS
        // ===========================
        'user.getFriends' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'recenttracks' => ['required' => false],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getInfo' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
            ],
        ],
        'user.getLovedTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getPersonalTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'tag' => ['required' => true],
                'taggingtype' => ['required' => true], // artist, album, track
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getRecentTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
                'page' => ['required' => false],
                'from' => ['required' => false],
                'to' => ['required' => false],
                'extended' => ['required' => false],
            ],
        ],
        'user.getTopAlbums' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false], // overall, 7day, 1month, 3month, 6month, 12month
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getTopArtists' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false], // overall, 7day, 1month, 3month, 6month, 12month
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getTopTags' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'limit' => ['required' => false],
            ],
        ],
        'user.getTopTracks' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'period' => ['required' => false], // overall, 7day, 1month, 3month, 6month, 12month
                'limit' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.getWeeklyAlbumChart' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'from' => ['required' => false],
                'to' => ['required' => false],
            ],
        ],
        'user.getWeeklyArtistChart' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
                'from' => ['required' => false],
                'to' => ['required' => false],
            ],
        ],
        'user.getWeeklyChartList' => [
            'httpMethod' => 'GET',
            'parameters' => [
                'user' => ['required' => true],
            ],
        ],
        'user.getWeeklyTrackChart' => [
            'httpMethod' => 'GET',
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
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'LastFmClient/1.0 (+https://github.com/calliostro/lastfm-client)',
                'Accept' => 'application/json',
            ],
        ],
    ],
];
