import React from "react";
const { RichText } = wp.editor;

(function (blocks, element) {
    let suggestions = [];

    fetch("/?rest_route=/isset-publisher/v1/publishes").then(res => {
        res.json().then(res => {
            if (res.length > 9) {
                suggestions = res.slice(0, 5)
            } else {
                suggestions = res;
            }
        });

    }).catch(err => {
        console.log(err);
    });

    const style = {
        container: {
            padding: "1em 2.5em",
            minHeight: "200px",
            width: "100%",
            textAlign: "center",
            background: "rgba(139,139,150,.1)",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            flexDirection: "column",
        },
        titleWrapper: {
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            fontWeight: "600",
            marginBottom: "0.5em"
        },
        form: {
            display: "flex",
            flexDirection: "row",
            width: "100%"
        },
        input: {
            fontFamily: "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif",
            padding: "6px 8px",
            boxShadow: "0 0 0 transparent",
            transition: "box-shadow .1s linear",
            borderRadius: "4px",
            border: "1px solid #7e8993",
            flex: 1
        },
        text: {
            fontSize: "13px",
            marginBottom: "0.5em"
        },
        textClick: {
            fontSize: "0.75rem",
            textAlign: "center",
            color: "#6666aa"
        },
        hidden: {
            display: "none"
        }
    };

    blocks.registerBlockType('publisher/video', {
        title: 'Publisher video',
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
                <div style={style.container}>
                    <div style={style.titleWrapper}>
                        Publisher video
                    </div>
                    <span style={style.text}>Enter a video uuid</span>
                    <form style={style.form}>
                        <input style={style.input} placeholder="Video uuid" onChange={e => setValue([e.target.value])} value={content}/>
                    </form>
                    <hr/>
                    <span onClick={toggleSuggestions} style={style.textClick}>Toggle suggestions</span>
                    <div style={{display: typeof showSuggestions === "boolean" && showSuggestions === true ? "flex" : "none"}}>
                        <div style={{display: "flex", flexDirection: "column"}}>
                            {suggestions.map(suggestion => {
                                return (
                                    <div style={{display: "flex", flexDirection: "column", marginBottom: "15px"}}>
                                        <span style={style.text}>{suggestion.streamName}</span>
                                        <span style={style.textClick} onClick={() => setValue(suggestion.uuid)}>Use video</span>
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
                    <h4 style={style.hidden}>{attributes.content}</h4>
                </div>
            )
        },
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.editor
));