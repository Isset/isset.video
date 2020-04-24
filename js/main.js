const videoJs = require('video.js');

// require('videojs-airplay');
// require('videojs-chromecast');

// require('videojs-airplay/dist/videojs.airplay.css');
// require('videojs-chromecast/dist/videojs-chromecast.css');

require('../scss/isset-video-publisher.scss');
require('./functions');

window.VideoJS = videoJs.default('isset-video-default', {
    fill: true,
    chromecast: {
        appId: 'E0FB432F'
    },
});