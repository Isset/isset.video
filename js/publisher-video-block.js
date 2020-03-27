import React from "react";

const blocks = window.wp.blocks;

let suggestions = [];

fetch("/?rest_route=/isset-publisher/v1/publishes").then(res => {
    res.json().then(json => {
        if (json.length > 9) {
            suggestions = json.slice(0, 5)
        } else {
            suggestions = json;
        }
    }).catch(err => console.log(err))
}).catch(err => console.log(err));

blocks.registerBlockType('isset-video-publisher/video-block', {
    title: 'Isset Video Publisher video',
    icon: 'video-alt2',
    category: 'embed',
    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'h4',
        },
        contentParsed: {
            type: 'array',
            source: 'children',
            selector: 'span',
        },
        showSuggestions: {
            type: 'boolean',
            source: 'children',
            selector: 'p',
        }
    },
    example: {
        attributes: {
            content: 'Hello World',
            showSuggestions: true
        },
    },
    edit: (props) => {
        const {attributes: {content, showSuggestions}, setAttributes} = props;
        const setValue = newValue => setAttributes({content: newValue, contentParsed: `[publish uuid=${newValue}]`});
        const toggleSuggestions = () => setAttributes({showSuggestions: typeof showSuggestions === "boolean" ? !showSuggestions : true});

        return (
            <div className="video-block-container">
                <div className="video-block-title-wrapper">
                    Publisher video
                </div>
                <span className="video-block-text">Enter a video uuid</span>
                <form className="video-block-form">
                    <input className="video-block-input" placeholder="Video uuid" onChange={e => setValue(e.target.value)} value={content}/>
                </form>
                <hr/>
                <a href="#" onClick={toggleSuggestions} className="video-block-text-clickable">Toggle suggestions</a>
                <div style={{display: typeof showSuggestions === "boolean" && showSuggestions === true ? "flex" : "none"}}>
                    <div className="video-block-suggestions-container">
                        <hr />
                        {suggestions.map(suggestion => {
                            return (
                                <div className="video-block-suggestions-wrapper">
                                    { suggestion.post_thumbnail !== null &&
                                        <img alt="thumbnail" className="video-block-suggestions-image" src={suggestion.post_thumbnail} />
                                    }
                                    <span className="video-block-text">{suggestion.post_title}</span>
                                    <a href="#" className="video-block-text-clickable" onClick={() => setValue(suggestion.post_name)}>Use video</a>
                                </div>
                            )
                        })}
                    </div>
                </div>
            </div>
        );
    },
    save: (props) => {
        const { attributes } = props;

        return (
            <div>
                <span>{attributes.contentParsed}</span>
                <h4 style={{display: "none"}}>{attributes.content}</h4>
            </div>
        )
    },
});