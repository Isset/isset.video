import React from "react";

(function (blocks, element) {
    let suggestions = [];

    fetch("/?rest_route=/isset-publisher/v1/publishes").then(res => {
        res.json().then(res => {
            if (res.length > 9) {
                suggestions = res.slice(0, 5)
            }
            else {
                suggestions = res;
            }
        });

    }).catch(err => {
        console.log(err);
    });

    const style = {
        container: {
            minHeight: "200px",
            padding: "25px",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            flexDirection: "column",
            backgroundColor: "#ccc"
        },
        input: {

        },
        text: {
            fontSize: "1rem",
        },
        textClick: {
            fontSize: "0.75rem",
            textAlign: "center",
            color: "#6666aa"
        }
    };

    let showSuggestionWrapper = false;

    blocks.registerBlockType('publisher/video', {
        title: 'Publisher video',
        icon: 'f234',
        category: 'embed',
        attributes: {
            content: {
                type: 'string',
                source: 'children',
                selector: 'p',
            },
        },
        example: {
            attributes: {
                content: 'Hello World',
            },
        },
        edit: (props) => {
            console.log(props);
            const {attributes: {content}, setAttributes} = props;
            const onChangeContent = newContent => {
                setAttributes({content: newContent.target.value});
            };
            const setValue = newValue => {
                setAttributes({content: newValue});
            }

            return (
                <div style={style.container}>
                    <span style={style.text}>Enter a video uuid</span>
                    <input style={style.input} onChange={onChangeContent} value={content} />
                    <hr />
                    <span onClick={() => {
                        showSuggestionWrapper = !showSuggestionWrapper;
                        setValue('')
                    }} style={style.textClick}>Toggle suggestions</span>
                    <div style={{display: showSuggestionWrapper ? "flex" : "none"}}>
                        <div style={{display: "flex", flexDirection: "column"}}>
                            {suggestions.map(suggestion => {
                                return (
                                    <div style={{display: "flex", flexDirection: "column", marginBottom: "15px"}}>
                                        <iframe src={suggestion.view.embed_url}/>
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
            return <div>[publish uuid={props.attributes.content}]</div>
        },
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.editor
));