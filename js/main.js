const videojs = require('video.js');
require('videojs-contrib-quality-levels')
require('videojs-http-source-selector');

require('./functions');

require('../scss/isset-video-publisher.scss');

if (document.querySelector('#isset-video-default')) {
    let video = document.querySelector('#isset-video-default');

    // Hide cc in safari
    if (video.textTracks.length > 0) {
        video.textTracks[0].mode = "hidden";
    }

    let player = videojs.default(video, {
        fill: true,
        plugins: {
            httpSourceSelector:
                {
                    default: 'auto'
                }
        }
    });
    player.httpSourceSelector();
}