import React from 'react';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';

class AdminCopyText extends React.Component {
    static propTypes = {
        text: PropTypes.string.isRequired,
        onCopied: PropTypes.func,
    };

    constructor(props) {
        super(props);

        this.state = {
            copied: false,
        };
    }

    copy = text => {
        let tempInput = document.createElement('input');

        document.querySelector('body').appendChild(tempInput);
        tempInput.setAttribute('value', text);
        tempInput.select();
        document.execCommand('copy');
        document.querySelector('body').removeChild(tempInput);

        if (this.props.onCopied) {
            this.props.onCopied();
        } else {
            this.setState({copied: true}, () => setTimeout(this.reset, 5000));
        }
    }

    reset = () => this.setState({copied: false});

    render() {
        const {text} = this.props;
        const {copied} = this.state;

        return <div>
            <p className="isset-video-copytext-container">
                <span className="dashicons dashicons-admin-page" onClick={() => this.copy(text)} />
                <code onClick={() => this.copy(text)}>{text}</code>
            </p>

            {copied && <p className="iv-t-orange">{__('Copied shortcode', 'isset-video')}!</p>}
        </div>;
    }

}

export default AdminCopyText;