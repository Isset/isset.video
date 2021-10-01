import React from 'react';
import PlayerPreview from './playerPreview';
import ColorPicker from './colorPicker';
import {objectToRgbaString} from '../helpers/color';
import {publisherAjax} from '../../ajax';
import {showMessage} from '../../toast/helper/toast';
import {__} from '@wordpress/i18n';

class Editor extends React.Component {

    constructor(props) {
        super(props);

        this.defaultState = {
            enabled: false,
            style: {
                border: {
                    borderColor: 'orange',
                    borderWidth: 0,
                    borderRadius: 0,
                    borderStyle: 'solid',
                },
                playButton: {
                    backgroundColor: 'orange',
                },
                playButtonHover: {
                    backgroundColor: 'orange',
                },
                timeline: {
                    height: 5,
                    backgroundColor: 'rgba(115,133,159,.5)',
                },
                timelineLoaded: {
                    backgroundColor: 'rgba(115,133,159,.75)',
                },
                timelinePlayed: {
                    backgroundColor: 'rgb(255,255,255)',
                },
                controlBar: {
                    backgroundColor: 'rgba(30, 30, 30, 0.6)',
                },
                controlBarButtons: {
                    color: 'orange',
                    backgroundColor: '#fff',
                },
            }
        };

        this.state = {...this.defaultState};
    }

    componentDidMount() {
        publisherAjax('api/settings/player', 'get').then(response => {
            if (response) {
                this.setState({style: response.settings, enabled: response.enabled});
            }
        });
    }

    changeButtonColor = color => this.updateStyle('playButton', {backgroundColor: color.hex});
    changeButtonHoverColor = color => this.updateStyle('playButtonHover', {backgroundColor: color.hex});

    changeBorderColor = color => this.updateStyle('border', {borderColor: color.hex});
    changeBorderWidth = event => this.updateStyle('border', {borderWidth: parseInt(event.target.value)});
    changeBorderRadius = event => this.updateStyle('border', {borderRadius: parseInt(event.target.value)});

    changeTimelineBackgroundColor = color => this.updateStyle('timeline', {backgroundColor: objectToRgbaString(color.rgb)});
    changeLoadedBackgroundColor = color => this.updateStyle('timelineLoaded', {backgroundColor: objectToRgbaString(color.rgb)});
    changeProgressBackgroundColor = color => this.updateStyle('timelinePlayed', {backgroundColor: objectToRgbaString(color.rgb)});
    changeProgressHeight = event => this.updateStyle('timeline', {height: parseInt(event.target.value)});

    changeControlsBackgroundColor = color => this.updateStyle('controlBar', {backgroundColor: objectToRgbaString(color.rgb)});
    changeControlButtonsColor = color => this.updateStyle('controlBarButtons', {backgroundColor: objectToRgbaString(color.rgb)});
    changeControlButtonsAccentColor = color => this.updateStyle('controlBarButtons', {color: objectToRgbaString(color.rgb)});

    updateStyle = (key, styles) => {
        const style = {...this.state.style};
        const oldStyle = style[key];
        style[key] = {...oldStyle, ...styles};

        this.setState({style});
    };

    updateEnabled = event => {
        const {target: {checked}} = event;

        this.setState({enabled: checked});
    };

    reset = () => {
        this.setState({...this.defaultState});
    };

    saveSettings = () => {
        const {enabled, style} = this.state;

        const data = {
            enabled,
            settings: style,
        };

        publisherAjax('api/settings/player', {}, 'post', data).then(response => {
            if (response) {
                showMessage('Settings saved', 'success');
            }
        });
    };

    render() {
        const {style, enabled} = this.state;
        const {
            playButton,
            playButtonHover,
            border,
            timeline,
            timelineLoaded,
            timelinePlayed,
            controlBar,
            controlBarButtons,
        } = style;

        return <div className="player-customizer video-publisher-flex iv-row iv-wrap-reverse">
            <div className="iv-col-12 iv-col-md-5">
                <div className="card">
                    <div className="card-header">
                        <h3>{__('Play Button', 'isset-video')}</h3>
                    </div>
                    <div className="card-body">
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Color', 'isset-video')}:</span>
                            <ColorPicker color={playButton.backgroundColor} onChange={this.changeButtonColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Color on Mouseover', 'isset-video')}:</span>
                            <ColorPicker color={playButtonHover.backgroundColor} onChange={this.changeButtonHoverColor} />
                        </div>
                    </div>
                </div>

                <div className="card">
                    <div className="card-header">
                        <h3>{__('Borders', 'isset-video')}</h3>
                    </div>
                    <div className="card-body">
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Color', 'isset-video')}:</span>
                            <ColorPicker color={border.borderColor} onChange={this.changeBorderColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Width', 'isset-video')}:</span>
                            <input type="range" min={0} max={50} value={border.borderWidth} onChange={this.changeBorderWidth} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Rounded Corners', 'isset-video')}:</span>
                            <input type="range" min={0} max={50} value={border.borderRadius} onChange={this.changeBorderRadius} />
                        </div>
                    </div>
                </div>

                <div className="card">
                    <div className="card-header">
                        <h3>{__('Progress Bar', 'isset-video')}</h3>
                    </div>
                    <div className="card-body">
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Background Color', 'isset-video')}:</span>
                            <ColorPicker color={timeline.backgroundColor} onChange={this.changeTimelineBackgroundColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Loaded Color', 'isset-video')}:</span>
                            <ColorPicker color={timelineLoaded.backgroundColor} onChange={this.changeLoadedBackgroundColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Played', 'isset-video')}:</span>
                            <ColorPicker color={timelinePlayed.backgroundColor} onChange={this.changeProgressBackgroundColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Height', 'isset-video')}:</span>
                            <input type="range" min={5} max={16} value={timeline.height} onChange={this.changeProgressHeight} />
                        </div>
                    </div>
                </div>

                <div className="card">
                    <div className="card-header">
                        <h3>{__('Controls', 'isset-video')}</h3>
                    </div>
                    <div className="card-body">
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Background Color', 'isset-video')}:</span>
                            <ColorPicker color={controlBar.backgroundColor} onChange={this.changeControlsBackgroundColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Buttons Color', 'isset-video')}:</span>
                            <ColorPicker color={controlBarButtons.backgroundColor} onChange={this.changeControlButtonsColor} />
                        </div>
                        <div className="video-publisher-flex video-publisher-flex-between mb-2">
                            <span className="text-muted text-uppercase font-weight-bold">{__('Buttons Accent Color', 'isset-video')}:</span>
                            <ColorPicker color={controlBarButtons.color} onChange={this.changeControlButtonsAccentColor} />
                        </div>
                    </div>
                </div>
            </div>
            <div className="iv-col-12 iv-col-md-7 iv-player-preview-column">
                <PlayerPreview style={style}  />

                <div className="card">
                    <div className="card-body iv-pt-10">
                        <div className="form-check">
                            <input type="checkbox" checked={enabled} className="form-check-input" id="enable" onChange={this.updateEnabled} />
                            <label className="form-check-label" htmlFor="enable">
                                {__('Enable Styles', 'isset-video')}
                            </label>
                        </div>
                    </div>
                </div>

                <div className="row mt-20">
                    <div className="col-12">
                        <button className="isset-video-btn isset-video-upload-btn video-publisher-mr-2" onClick={this.saveSettings}>
                            {__('Save', 'isset-video')}
                        </button>
                        <button className="isset-video-btn btn btn-danger ml-2" onClick={this.reset}>
                            {__('Reset', 'isset-video')}
                        </button>
                    </div>
                </div>
            </div>
        </div>;
    }
}

export default Editor;