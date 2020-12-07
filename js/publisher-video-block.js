import React from "react";
import {archiveAjax} from './ajax';
import {__} from '@wordpress/i18n';

const blocks = window.wp.blocks;
const {
    BlockControls,
    InspectorControls,
} = window.wp.editor;
const {
    PanelBody,
    PanelRow,
} = wp.components;

blocks.registerBlockType('isset-video-publisher/video-block', {
    title: 'isset.video video',
    icon: 'video-alt2',
    category: 'embed',
    edit: class extends React.Component {
        constructor(props) {
            super(props);
            this.state = {
                lazyStep: 0
            };

            this.getSuggestions = this.getSuggestions.bind(this);
            this.setValue = this.setValue.bind(this);
            this.changeSearchTerm = this.changeSearchTerm.bind(this);
            this.updatedParsedUuid = this.updatedParsedUuid.bind(this);
            this.setAttr = this.setAttr.bind(this);
            this.renderProcessingPlaceholder = this.renderProcessingPlaceholder.bind(this);
            this.renderError = this.renderError.bind(this);
        }

        componentDidMount() {
            this.getSuggestions('', 0);
        }

        componentDidUpdate(prevProps) {
            const {attributes: {uuid, autoplay}} = this.props;

            if (prevProps.attributes.uuid !== uuid || prevProps.attributes.autoplay !== autoplay) {
                this.updatedParsedUuid();
            }
        }

        renderStill(stills) {
            if (stills && stills.length > 0) {
                return <img src={`${stills[0]}?width=300&height=168`} />
            }

            return <div className="isset-video-thumb-placeholder" />;
        }

        getSuggestions(searchTerm, page = null) {
            let {setAttributes, attributes: {suggestions}} = this.props;
            const {root} = window.IssetVideoArchiveAjax;
            const {lazyStep} = this.state;

            if (page === false) {
                suggestions = [];
            }

            archiveAjax(`api/search`, {
                q: searchTerm,
                offset: lazyStep * 25,
            }).then(json => {
                const {results} = json;

                setAttributes({
                    suggestions: [
                        ...suggestions,
                        ...results.map((result, index) => {
                            result.processed = result.stills && result.stills.length > 0;

                            return result;
                        })
                    ],
                });

                this.setState({
                    lazyStep: lazyStep + 1
                });
            }).catch(err => console.log(err));
        }

        setValue(suggestion) {
            const {setAttributes} = this.props;

            if (suggestion === '') {
                this.setState({lazyStep: 0}, () => {
                    setAttributes({
                        uuid: '',
                        uuidParsed: '',
                        videoThumbnail: '',
                        videoName: '',
                        videoSize: '',
                        autoplay: '',
                        searchTerm: '',
                    });
                    this.getSuggestions('', false);
                });
            }
            else {
                const {uuid} = suggestion;

                archiveAjax(`api/files/${uuid}/details`).then(json => {
                    const {publish: {publish_uuid}, file} = json;

                    setAttributes({
                        uuid: publish_uuid,
                        uuidParsed: `[publish uuid=${publish_uuid}]`,
                        videoThumbnail: file.stills[0],
                        videoName: file.filename,
                        videoSize: file.size,
                        autoplay: ''
                    });
                }).catch(err => console.log(err));
            }
        }

        setAttr(key, value) {
            const {setAttributes} = this.props;

            switch (key) {
                case 'autoplay':
                    setAttributes({
                        autoplay: value === true ? 'autoplay' : ''
                    });
                    break;
            }
        }

        updatedParsedUuid() {
            const {setAttributes, attributes: {uuid, autoplay}} = this.props;

            setAttributes({
                uuidParsed: `[publish uuid="${uuid}" autoplay="${autoplay}"]`,
            });
        }

        changeSearchTerm(newTerm) {
            const {setAttributes} = this.props;
            setAttributes({searchTerm: newTerm});
            this.setState({lazyStep: 0}, () => this.getSuggestions(newTerm, false));
        }

        renderProcessingPlaceholder() {
            return <div className="isset-video-icon-container">
                <span className="dashicons dashicons-backup" />
                <div>{__('Processing', 'isset-video')}</div>
            </div>;
        }

        renderError() {
            return <div className="isset-video-icon-container isset-video-warning">
                <span className="dashicons dashicons-warning" />
                <div>{__('Error', 'isset-video')}</div>
            </div>;
        }

        renderSuggestions(suggestions) {
            return suggestions.map(suggestion => {
                const {processed, filename, stills} = suggestion;

                if (!processed) {
                    return <div className="video-block-suggestions-wrapper">
                        <div className="isset-video-placeholder-container">
                            <div className="isset-video-thumb-placeholder">
                                {this.renderProcessingPlaceholder()}
                            </div>
                        </div>
                        <span className="video-block-text">{filename ? filename : __('File nof found', 'isset-video')}</span>
                    </div>;
                }

                return <div className="video-block-suggestions-wrapper" onClick={() => this.setValue(suggestion)}>
                    <div>
                        {this.renderStill(stills)}
                    </div>
                    <span className="video-block-text">{filename}</span>
                </div>;
            })
        }

        render() {
            const {attributes: {uuid, suggestions, videoThumbnail, videoName, videoSize, searchTerm, autoplay, uuidParsed}} = this.props;
            let {lazyStep} = this.state;

            if ((typeof Array.isArray(uuid) && uuid.length === 1) || (typeof uuid === 'string' && uuid !== '')) {
                return (
                    <div className="video-block-selected-wrapper">
                        {
                            <BlockControls>
                                <InspectorControls>
                                    <PanelBody title={__('Video info', 'isset-video')}>
                                        <PanelRow>
                                            <span><b>{__('Video name', 'isset-video')}: </b> {videoName}</span>
                                        </PanelRow>
                                        <PanelRow>
                                            <span><b>{__('Video size', 'isset-video')}: </b> {(parseInt(videoSize) / 1e+9).toFixed(3)} GB</span>
                                        </PanelRow>
                                        <PanelRow>
                                            <span><b>Uuid: </b> {uuid}</span> <br/>
                                        </PanelRow>
                                    </PanelBody>
                                    <PanelBody title={__('Options', 'isset-video')}>
                                        <PanelRow>
                                            <span><b>{__('Autoplay', 'isset-video')}: </b> <input onChange={e => this.setAttr('autoplay', e.target.checked)} type="checkbox" /></span>
                                        </PanelRow>
                                    </PanelBody>
                                </InspectorControls>
                            </BlockControls>
                        }
                        <div className="video-block-relative">
                            <div>
                                <img src={videoThumbnail} />
                                <span className='dashicon dashicons-controls-play video-block-play-button'/>
                            </div>
                            <div className="video-block-icon-close-wrapper" onClick={() => this.setValue('')}>
                                <span className="video-block-icon-close dashicons dashicons-no"/>
                            </div>
                        </div>
                    </div>
                )
            }

            return (
                <div className="video-block-container">
                    <div className="video-block-title-wrapper">
                        {__('Videos on isset.video', 'isset-video')}
                    </div>
                    <form className="video-block-form" onSubmit={e => e.preventDefault()}>
                        <input className="video-block-input" placeholder={__('Search videos', 'isset-video')}
                               onBlur={e => this.changeSearchTerm(e.target.value)}/>
                        <button className="video-block-button" type="submit">{__('Search', 'isset-video')}</button>
                    </form>
                    <hr/>
                    <div className="video-block-suggestions-container">
                        {suggestions.length === 0 ? <span className="video-block-text">{__('No publishes found', 'isset-video')}</span> : this.renderSuggestions(suggestions)}
                    </div>
                    <div>
                        <button onClick={() => this.getSuggestions(searchTerm, lazyStep)}
                                className="video-block-button">{__('More results', 'isset-video')}
                        </button>
                    </div>
                </div>
            );
        }
    },
    attributes: {
        uuidParsed: {
            type: 'array',
            source: 'children',
            selector: 'video-embed',
        },
        uuid: {
            type: 'array',
            source: 'children',
            selector: 'video-uuid',
        },
        videoName: {
            type: 'array',
            source: 'children',
            selector: 'video-name'
        },
        videoSize: {
            type: 'array',
            source: 'children',
            selector: 'video-size'
        },
        videoThumbnail: {
            type: 'array',
            source: 'children',
            selector: 'video-thumbnail'
        },
        searchTerm: {
            type: 'array',
            source: 'children'
        },
        suggestions: {
            type: 'array',
            source: 'children'
        },
        autoplay: {
            type: 'array',
            source: 'children',
            selector: 'video-autoplay'
        }
    },
    save: (props) => {
        const {attributes} = props;

        return (
            <div>
                <video-uuid style={{display: 'none'}}>{attributes.uuid}</video-uuid>
                <video-embed>{attributes.uuidParsed}</video-embed>
                <video-thumbnail style={{display: 'none'}}>{attributes.videoThumbnail}</video-thumbnail>
                <video-name style={{display: 'none'}}>{attributes.videoName}</video-name>
                <video-size style={{display: 'none'}}>{attributes.videoSize}</video-size>
                <video-autoplay style={{display: 'none'}}>{attributes.autoplay}</video-autoplay>
            </div>
        )
    },
});