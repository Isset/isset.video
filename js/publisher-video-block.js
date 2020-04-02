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
        searchTerm: {
            type: 'array',
            source: 'children'
        },
        suggestions: {
            type: 'array',
            source: 'children'
        }
    },
    edit: (props) => {
        const {attributes: {uuid, suggestions}, setAttributes} = props;
        const setValue = newValue => setAttributes({uuid: newValue, uuidParsed: `[publish uuid=${newValue}]`});
        const changeSearchTerm = newTerm => {
            setAttributes({searchTerm: newTerm});
            getSuggestions(newTerm)
        };
        const getSuggestions = searchTerm => {
            fetch(`/?rest_route=/isset-publisher/v1/publishes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({post_title: searchTerm})
            }).then(res => {
                res.json().then(json => {
                    if (json.length > 5) {
                        setAttributes({suggestions: json.slice(0, 5)})
                    } else {
                        setAttributes({suggestions: json})
                    }
                }).catch(err => console.log(err))
            }).catch(err => console.log(err));
        };

        console.log(suggestions);

        return (
            <div className="video-block-container">
                <div className="video-block-title-wrapper">
                    Publisher video
                </div>
                <span className="video-block-text">Enter a video uuid</span>
                <form className="video-block-form" onSubmit={e => e.preventDefault()}>
                    <input className="video-block-input" placeholder="Video uuid"
                           onChange={e => setValue(e.target.value)} value={uuid}/>
                </form>
                <span className="video-block-text">Or</span>
                <span className="video-block-text">Search for a video</span>
                <form className="video-block-form" onSubmit={e => e.preventDefault()}>
                    <input className="video-block-input" placeholder="Search videos"
                           onBlur={e => changeSearchTerm(e.target.value)} />
                </form>
                <hr/>
                <div className="video-block-suggestions-container">
                    <hr/>
                    {suggestions.length === 0 &&
                    <span className="video-block-text">No publishes found</span>
                    }
                    {suggestions.map(suggestion => {
                        if (suggestion.type === 'div') return <div/>;

                        return (
                            <div className="video-block-suggestions-wrapper">
                                {suggestion.post_thumbnail !== null &&
                                    <div dangerouslySetInnerHTML={{__html: suggestion.post_thumbnail}}/>
                                }
                                <span className="video-block-text">{suggestion.post_title}</span>
                                <a href="#" className="video-block-text-clickable"
                                   onClick={() => setValue(suggestion.post_name)}>Use video</a>
                            </div>
                        )
                    })}
                </div>
            </div>
        );
    },
    save: (props) => {
        const {attributes} = props;

        return (
            <div>
                <video-uuid>{attributes.uuid}</video-uuid>
                <video-embed style={{display: "none"}}>{attributes.uuidParsed}</video-embed>
            </div>
        )
    },
});