import videojs from 'video.js';
import {setLocaleData} from '@wordpress/i18n';
import 'videojs-ima/dist/videojs.ima.css';

require('jquery');
require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs, {preloadWebComponents: true});
require('@silvermine/videojs-airplay')(videojs);
require('videojs-contrib-ads');
require('videojs-ima');

require('./functions');
require('./overview/components/admin-video-overview');
require('./livestream/components/livestream');
require('./advertisement/components/advertisementSettings');
require('./playerSettings/components/playerSettings');
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
            if (video.dataset.chapters) {
                const chapters = JSON.parse(video.dataset.chapters);

                Array.from(chapters).forEach(chapter => {
                    const {url, language} = chapter;
                    const track = this.addRemoteTextTrack({kind: 'chapters', src: url, language}, true);

                    track.addEventListener('load', () => {
                        const activeSubtitle = findSubtitleTrackWithStatus(this.textTracks().tracks_, 'showing');
                        if (activeSubtitle) {
                            loadChapters(this, activeSubtitle.language);
                        }
                    });
                });
            }

            this.on('loadedmetadata', function() {
                loadChapters(this);

                const activeSubtitle = findSubtitleTrackWithStatus(this.textTracks().tracks_, 'showing');
                if (activeSubtitle) {
                    loadChapters(this, activeSubtitle.language);
                } else {
                    loadChapters(this, null);
                }
            });
        });

        player.chromecast();
        player.airPlay();

        const {adUrl} = video.dataset;

        let imaOptions = {
            adTagUrl: adUrl,
        };

        player.ima(imaOptions);
    }

    setLocaleData(window.issetVideoTranslations, 'isset-video');
});

export function loadChapters(player, language) {
    let duration = player.duration();
    if (duration === 0) {
        return;
    }

    // Disable all chapter text tracks. If we don't do this, the hidden tracks will still fire events
    // which results in errors in the console, because no player is attached.
    disableChapterTracks(player);
    let track = findChapterTrack(player, language);

    if(track === null || track.cues === null){
        return;
    }

    let cues = track.cues;
    let controlBar = player.controlBar.contentEl();
    let element = document.createElement('div');

    element.className = 'vjs-chapter-bar';
    element.id = 'vjs-chapter-bar';

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

/**
 * Disable all chapter textTracks
 */
function disableChapterTracks(player) {
    player.textTracks().tracks_.map(track => {
        if (track.kind === 'chapters') {
            track.mode = 'disabled';
        }
    });
}

function findSubtitleTrackWithStatus(tracks, status = 'showing') {
    return tracks.filter(track => track.kind === 'subtitles' && track.mode === status).pop();
}

