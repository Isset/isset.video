import videojs from 'video.js';
import {setLocaleData} from '@wordpress/i18n';

require('jquery');
require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs, {preloadWebComponents: true});
require('@silvermine/videojs-airplay')(videojs);

require('./functions');
require('./overview/components/admin-video-overview');
require('./livestream/components/livestream');
require('./advertisement/components/advertisementSettings');

require('../scss/isset-video-publisher.scss');

require('./livestream/components/livestreamPlayer');


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
        }, function() {
            this.on('loadedmetadata', function() {
                loadChapters(this);
            });
        });

        player.chromecast();
        player.airPlay();
    }

    setLocaleData(window.issetVideoTranslations, 'isset-video');
});

export function loadChapters(player) {

    let duration = player.duration();
    if (duration === 0) {
        return;
    }
    let track = findChapterTrack(player);
    if(track === null){
        return;
    }
    let cues = track.cues;
    let controlBar = player.controlBar.contentEl();
    let element = document.createElement('div');

    element.className = 'vjs-chapter-bar';

    for (let i = 0; i < cues.length; i++) {
        let cue = cues[i];

        let marker = document.createElement('div');
        marker.className = 'vjs-chapter-marker';

        let markerText = document.createElement('div');
        let markerContent = document.createElement('span');
        markerText.className = 'vjs-chapter-marker-text';
        markerContent.innerText = cue.text;
        markerText.appendChild(markerContent);

        marker.appendChild(markerText);

        let position = ((cue.startTime / duration) * 1000) / 10;

        let chapterDuration = cue.endTime - cue.startTime;
        let width = ((chapterDuration / duration) * 1000) / 10;

        marker.style.left = `${position}%`;
        marker.style.width = `${width}%`;

        element.appendChild(marker);
    }

    controlBar.querySelector('.vjs-progress-control').append(element);
}

function findChapterTrack(player) {
    let tracks = player.textTracks();
    for (let i = 0; i < tracks.length; i++) {
        let track = tracks[i];

        // find the captions track check language?
        if (track.kind === 'chapters') {
            return track;
        }
    }

    return null;
}
