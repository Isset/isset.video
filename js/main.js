let videojs = require('video.js');

require('./functions');

require('../scss/isset-video-publisher.scss');

if (document.querySelector('#isset-video-default')) {
    let video = document.querySelector('#isset-video-default');

    // Hide cc in safari
    if (video.textTracks.length > 0) {
        video.textTracks[0].mode = "hidden";
    }

    videojs.default(video, {
        fill: true,
        chromecast: {
            appId: 'E0FB432F'
        }
    });
}