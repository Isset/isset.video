import React from "react";

const blocks = window.wp.blocks;

blocks.registerBlockType('isset-video-publisher/video-block', {
    title: 'Isset Video Publisher video',
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
        }
    },
    edit: class extends React.Component {
        constructor(props) {
            super(props);

            this.getSuggestions = this.getSuggestions.bind(this);
            this.setValue = this.setValue.bind(this);
            this.changeSearchTerm = this.changeSearchTerm.bind(this);
        }

        componentDidMount() {
            this.getSuggestions();
        }

        getSuggestions(searchTerm) {
            const {setAttributes} = this.props;

            fetch(`/?rest_route=/isset-publisher/v1/publishes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({post_title: searchTerm})
            }).then(res => {
                res.json().then(json => {
                    if (json.length > 6) {
                        setAttributes({suggestions: json.slice(0, 6)})
                    } else {
                        setAttributes({suggestions: json})
                    }
                }).catch(err => console.log(err))
            }).catch(err => console.log(err));
        }

        setValue(newValue, newThumbnail = '') {
            const {attributes: {suggestions}, setAttributes} = this.props;

            setAttributes({
                uuid: newValue,
                uuidParsed: `[publish uuid=${newValue}]`,
                videoThumbnail: newThumbnail
            });
        }

        changeSearchTerm(newTerm) {
            const {setAttributes} = this.props;
            setAttributes({searchTerm: newTerm});
            this.getSuggestions(newTerm)
        }

        render() {
            const {attributes: {uuid, suggestions, videoName, videoThumbnail}} = this.props;

            if ((typeof Array.isArray(uuid) && uuid.length === 1) || (typeof uuid === 'string' && uuid !== '')) {
                return (
                    <div className="video-block-selected-wrapper">
                        <div className="video-block-icon-close-wrapper" onClick={() => this.setValue('')}>
                            <span className="video-block-icon-close dashicons dashicons-no"/>
                        </div>
                        <div dangerouslySetInnerHTML={{__html: videoThumbnail}}/>
                    </div>
                )
            }

            return (
                <div className="video-block-container">
                    <div className="video-block-title-wrapper">
                        Publisher video
                    </div>
                    <span className="video-block-text">Search for a video</span>
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
                                return suggestion.type === 'div' ?
                                    <span className="video-block-text">No publishes found</span> :
                                    <div className="video-block-suggestions-wrapper">
                                        {suggestion.post_thumbnail !== null &&
                                        <div dangerouslySetInnerHTML={{__html: suggestion.post_thumbnail}}/>
                                        }
                                        <span className="video-block-text">{suggestion.post_title}</span>
                                        <a href="#" className="video-block-text-clickable"
                                           onClick={() => this.setValue(suggestion.post_name, suggestion.post_thumbnail)}>Use video</a>
                                    </div>
                            })
                        }
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
                <video-name style={{display: 'none'}}>{attributes.videoName}</video-name>
                <video-thumbnail style={{display: 'none'}}>{attributes.videoThumbnail}</video-thumbnail>
            </div>
        )
    },
});