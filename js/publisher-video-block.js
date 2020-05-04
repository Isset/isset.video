import React from "react";

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
    },
    edit: class extends React.Component {
        constructor(props) {
            super(props);
            this.state = {
                lazyStep: 0
            };

            this.getSuggestions = this.getSuggestions.bind(this);
            this.setValue = this.setValue.bind(this);
            this.changeSearchTerm = this.changeSearchTerm.bind(this);
        }

        componentDidMount() {
            this.getSuggestions('', 0);
        }

        getSuggestions(searchTerm, lazyStep = null) {
            let {setAttributes, attributes: {suggestions}} = this.props;

            if (lazyStep === false) {
                suggestions = [];
                lazyStep = null;
            }

            fetch(`/?rest_route=/isset-publisher/v1/publishes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({post_title: searchTerm, page: lazyStep})
            }).then(res => {
                res.json().then(json => {
                    setAttributes({suggestions: [...suggestions, ...json]});
                    this.setState({
                        lazyStep: lazyStep + 1
                    });
                }).catch(err => console.log(err))
            }).catch(err => console.log(err));
        }

        setValue(suggestion) {
            const {setAttributes} = this.props;

            if (suggestion === '') {
                setAttributes({
                    uuid: '',
                    uuidParsed: '',
                    videoThumbnail: '',
                    videoName: '',
                    videoSize: ''
                });
            }
            else {
                setAttributes({
                    uuid: suggestion.post_name,
                    uuidParsed: `[publish uuid=${suggestion.post_name}]`,
                    videoThumbnail: suggestion.post_thumbnail,
                    videoName: suggestion.post_title,
                    videoSize: suggestion.post_size
                });
            }
        }

        changeSearchTerm(newTerm) {
            const {setAttributes} = this.props;
            setAttributes({searchTerm: newTerm});
            this.getSuggestions(newTerm, false)
        }

        render() {
            const {attributes: {uuid, suggestions, videoThumbnail, videoName, videoSize, searchTerm}} = this.props;
            let {lazyStep} = this.state;

            if ((typeof Array.isArray(uuid) && uuid.length === 1) || (typeof uuid === 'string' && uuid !== '')) {
                return (
                    <div className="video-block-selected-wrapper">
                        {
                            <BlockControls>
                                <InspectorControls>
                                    <PanelBody title="Video info">
                                        <PanelRow>
                                            <span><b>Video name: </b> {videoName}</span>
                                        </PanelRow>
                                        <PanelRow>
                                            <span><b>Video size: </b> {(parseInt(videoSize) / 1e+9).toFixed(3)} GB</span>
                                        </PanelRow>
                                        <PanelRow>
                                            <span><b>Uuid: </b> {uuid}</span> <br/>
                                        </PanelRow>
                                    </PanelBody>
                                </InspectorControls>
                            </BlockControls>
                        }
                        <div className="video-block-relative">
                            <div dangerouslySetInnerHTML={{__html: videoThumbnail + "<span class='dashicon dashicons-controls-play video-block-play-button'/>"}}/>
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
                        isset.video Video
                    </div>
                    <form className="video-block-form" onSubmit={e => e.preventDefault()}>
                        <input className="video-block-input" placeholder="Search videos"
                               onBlur={e => this.changeSearchTerm(e.target.value)}/>
                        <button className="video-block-button" type="submit">Search</button>
                    </form>
                    <hr/>
                    <div className="video-block-suggestions-container">
                        {suggestions.length === 0 ?
                            <span className="video-block-text">No publishes found</span>
                            :
                            suggestions.map(suggestion => {
                                return suggestion.type !== 'div' &&
                                    <div className="video-block-suggestions-wrapper"
                                         onClick={() => this.setValue(suggestion)}>
                                        {suggestion.post_thumbnail !== null &&
                                        <div dangerouslySetInnerHTML={{__html: suggestion.post_thumbnail}}/>
                                        }
                                        <span className="video-block-text">{suggestion.post_title}</span>
                                    </div>
                            })
                        }
                    </div>
                    <div>
                        <button onClick={() => this.getSuggestions(searchTerm, lazyStep)}
                                className="video-block-button">More results
                        </button>
                    </div>
                </div>
            );
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
            </div>
        )
    },
});