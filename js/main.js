import videojs from 'video.js';

require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs, {preloadWebComponents: true});
require('@silvermine/videojs-airplay')(videojs);

require('./functions');

require('../scss/isset-video-publisher.scss');


window.addEventListener('load', () => {
  for (const video of Array.from(document.querySelectorAll('.video-publisher-video video'))) {
    let player = videojs(video, {
      fill: true,
      fluid: true,
      controls: true,
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
    console.log(video, player);

    player.httpSourceSelector();
    player.chromecast();
    player.airPlay();
  }
});