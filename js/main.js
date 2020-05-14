import videojs from 'video.js';

require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs, {preloadWebComponents: true});
require('@silvermine/videojs-airplay')(videojs);

require('./functions');

require('../scss/isset-video-publisher.scss');

if (document.querySelector('#isset-video-default')) {
  let video = document.querySelector('#isset-video-default');

  let player = videojs(video, {
    fill: true,
    textTrackSettings: false,
    techOrder: ['chromecast', 'html5'],
    plugins: {
      httpSourceSelector: {
        default: 'auto'
      },
      chromecast: {},
      airPlay: {}
    },
    html5: {
      nativeTextTracks: false,
      hls: {
        handleManifestRedirects: true,
      }
    }
  });

  player.httpSourceSelector();
  player.chromecast();
  player.airPlay();
}