import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

class Message extends React.Component {
    static propTypes = {
        message: PropTypes.string.isRequired,
        level: PropTypes.string.isRequired,
        duration: PropTypes.number.isRequired,
        container: PropTypes.object.isRequired,
    };

    close = () => ReactDOM.unmountComponentAtNode(this.props.container);

    componentDidMount() {
        const {duration} = this.props;

        setTimeout(this.close, duration);
    }

    componentWillUnmount() {
        document.body.removeChild(this.props.container);
    }

    render() {
        const {message, level} = this.props;

        return (
            <div className={`isset-video-toast ${level}`}>
                {message}
            </div>
        );
    }
}

export default Message;