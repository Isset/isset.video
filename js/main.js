const videojs = require('video.js');

require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@leochen1216/videojs-chromecast');

require('./functions');

require('../scss/isset-video-publisher.scss');

if (document.querySelector('#isset-video-default')) {
    let video = document.querySelector('#isset-video-default');

    let player = videojs.default(video, {
        fill: true,
        textTrackSettings: false,
        plugins: {
            httpSourceSelector: {
                default: 'auto'
            },
        },
        chromecast:{
            appId:'E0FB432F',
        }
    });

    player.httpSourceSelector();
}