import React from 'react';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';

class PlayerPreview extends React.Component {
    static propTypes = {
        style: PropTypes.object.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {
            showPicker: false,
            playHover: false,
        };
    }

    playMouseOver = () => this.setState({playHover: true});
    playMouseOut = () => this.setState({playHover: false});

    render() {
        const {playHover} = this.state;
        const {playButton, playButtonHover, border, timeline, timelineLoaded, timelinePlayed, controlBar, controlBarButtons} = this.props.style;

        return <div>
            <div className="card">
                <div className="card-header">
                    <h3>{__('Preview', 'isset-video')}</h3>
                </div>
                <div className="card-body">
                    <div className="player-custom-preview">
                        <div className="container-16-9">
                            <div className="iv-player-container video-publisher-flex video-publisher-justify-center video-publisher-align-center" style={border}>
                                <div className="play-button video-publisher-ml-2" style={playHover ? playButtonHover : playButton} onMouseEnter={this.playMouseOver} onMouseLeave={this.playMouseOut} />
                                <div className="progress-bar-container video-publisher-flex video-publisher-flex-between video-publisher-align-center" style={controlBar}>
                                    <span className="video-publisher-ml-2">
                                        <span className="vjs-icon-play" style={controlBarButtons} />
                                    </span>
                                    <div className="progress-bar-preview video-publisher-ml-2 video-publisher-mr-2" style={timeline}>
                                        <div className="progress-bar-loaded" style={timelineLoaded} />
                                        <div className="progress-bar-played" style={timelinePlayed} />
                                    </div>
                                    <span className="video-publisher-mr-2 video-publisher-text-white">10:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>;
    }
}

export default PlayerPreview;