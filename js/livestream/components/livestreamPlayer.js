import React, {createRef} from 'react';
import ReactDOM from 'react-dom';

import videojs from 'video.js';

import httpSourceSelector from 'videojs-http-source-selector';

import {__} from '@wordpress/i18n';

require('videojs-contrib-quality-levels');
require('videojs-http-source-selector');
require('@silvermine/videojs-chromecast')(videojs);
require('@silvermine/videojs-airplay')(videojs);

class LivestreamPlayer extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            play: props.started && !props.ended,
            ended: props.ended,
            started: props.started,
        };
        this.videoNode = createRef();
        this.player = null;
    }

    componentDidMount() {
        let options = {
            controls: true,
            autoplay: true,
            muted: true,
            techOrder: ['chromecast', 'html5'],
            plugins: {
                httpSourceSelector: {
                    default: 'auto'
                },
                chromecast: {},
                airPlay: {}
            },
            html5: {
                nativeTextTracks: false
            }
        };

        videojs.registerPlugin('httpSourceSelector', httpSourceSelector);

        let player = videojs(this.videoNode, options, function () {
            this.on('loadedmetadata', function () {
                player.muted(false);
            });
            this.on('volumechange', function (e) {
                //Cookies.set('volume', player.volume());
            });
        });

        player.httpSourceSelector();
        player.chromecast();
        player.airPlay();

        this.player = player;
        this.initDetailsListener();
    }

    componentWillUnmount() {
        this.eventSource.close();
    }

    initDetailsListener = () => {
        const {socket} = this.props;

        this.eventSource = new EventSource(socket);
        this.eventSource.onmessage = (e) => {
            const eventData = JSON.parse(e.data);
            //timeout 20 seconds
            if (eventData.event === 'start') {
                // wait 20 seconds
                // fetch new data and update status
                setTimeout(() => this.showPlayer(), 20000);
            }
            if (eventData.event === 'end') {
                // wait 20 seconds
                // fetch new data and update status
                setTimeout(() => this.showEnded(), 20000);
            }
        };
    };

    showPlayer = () => {
        const {url} = this.props;
        
        this.player.src(url);
        this.player.play();

        this.setState({play: true});
    };

    showEnded = () => {
        this.player.dispose();
        this.setState({play: false, ended: true});
    };

    renderEndedOverlay = () => {
        return <div className="video-js vjs-theme-isset vjs-16-9 vjs-overlay-abs isset-video-livestream-ended">
            <div className="vjs-tech">
                <div className="text-container">
                    <h1>
                        {__('Livestream ended', 'isset-video')}
                    </h1>
                    <p>
                        {__('The VOD will be ready on this page at a later time.', 'isset-video')}
                    </p>
                </div>
            </div>
        </div>;
    };

    renderWaitingOverlay = () => {
        return <div className="video-js vjs-theme-isset vjs-16-9 vjs-overlay-abs">
            <div className="vjs-tech">
                <div className="text-container">
                    <h1>
                        {__('Livestream waiting to start', 'isset-video')}
                    </h1>
                    <p>
                        {__('This player will update automatically when the livestream goes live.', 'isset-video')}
                    </p>
                </div>
            </div>
        </div>;
    };

    render() {
        const {play, ended, started} = this.state;
        const {uuid, url, socket} = this.props;

        return <div className="isset-video-livestream-video vjs-big-play-centered vjs-16-9">
            <video
                id={`isset-video-livestream-${uuid}`}
                className="video-js vjs-theme-isset vjs-16-9"
                data-event-source-url={socket}
                data-player-url={url}
                data-start={play}
                controls
                data-setup={"{}"}
                ref={node => this.videoNode = node}
                muted={true}
            >
                <p className="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
            {ended && this.renderEndedOverlay()}
            {!ended && !started && this.renderWaitingOverlay()}
        </div>;
    }
}

export default LivestreamPlayer;

window.addEventListener('load', () => {
    const players = document.getElementsByClassName('isset-video-livestream-player');

    for (let i = 0; i < players.length; i++) {
        let player = players[i];
        const {uuid, socket, url, started, ended} = player.dataset;

        ReactDOM.render(<LivestreamPlayer key={uuid} uuid={uuid} socket={socket} url={url} started={started} ended={ended} />, player);
    }
});