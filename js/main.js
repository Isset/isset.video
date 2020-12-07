import videojs from 'video.js';
import {setLocaleData} from '@wordpress/i18n';

require('jquery');
require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs, {preloadWebComponents: true});
require('@silvermine/videojs-airplay')(videojs);

require('./functions');
require('./overview/components/admin-video-overview');

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
                chromecast: {},
                airPlay: {}
            },
            html5: {
                nativeTextTracks: false,
                hls: {
                    handleManifestRedirects: true,
                }
            },
        });

        player.chromecast();
        player.airPlay();
    }

    setLocaleData(window.issetVideoTranslations, 'isset-video');
});