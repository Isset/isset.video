import React from 'react';
import PropTypes from 'prop-types';

class AdminCopyText extends React.Component {
    static propTypes = {
        text: PropTypes.string.isRequired,
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

        this.setState({copied: true}, () => setTimeout(this.reset, 5000));
    }

    reset = () => this.setState({copied: false});

    render() {
        const {text} = this.props;
        const {copied} = this.state;

        return <div>
            <p>
                <code onClick={() => this.copy(text)}>{text}</code>
            </p>

            {copied && <p>Copied shortcode!</p>}
        </div>;
    }

}

export default AdminCopyText;