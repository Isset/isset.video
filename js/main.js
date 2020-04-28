const videoJs = require('video.js');

require('@silvermine/videojs-airplay')(videoJs);

require('@silvermine/videojs-airplay/src/scss/videojs-airplay');

require('./functions');

require('../scss/isset-video-publisher.scss');

if (document.querySelector('#isset-video-default')) {
    let video = document.querySelector('#isset-video-default');

    // Hide cc in safari
    if (video.textTracks.length > 0) {
        video.textTracks[0].mode = "hidden";
    }

    window.VideoJS = videoJs.default('isset-video-default', {
        fill: true,
        plugins: {
            airPlay: {
                addButtonToControlBar: true
            }
        }
    });
    window.VideoJS.airPlay();
}