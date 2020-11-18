import React from 'react';
import moment from 'moment';
import {secondsToHours} from '../helpers/duration';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';

class VideoItem extends React.Component {
    static propTypes = {
        uuid: PropTypes.string.isRequired,
        filename: PropTypes.string.isRequired,
        created: PropTypes.string.isRequired,
        duration: PropTypes.number,
        preview: PropTypes.string,
        size: PropTypes.string,
        stills: PropTypes.array,
        onSelect: PropTypes.func.isRequired,
        checked: PropTypes.bool.isRequired,
        onCheck: PropTypes.func.isRequired,
    };

    toggleChecked = event => {
        const {uuid} = this.props;
        const {checked} = event.target;

        this.props.onCheck(uuid, checked);
    };

    showDetails = (event, uuid) => {
        event.preventDefault();
        this.props.onSelect(uuid);
    };

    renderStill = (preview, stills = []) => {
        if (stills.length > 0) {
            return <img src={`${stills[0]}?width=300&height=168`} alt="" />
        }

        return <div className="isset-video-thumb-placeholder">
            <div className="isset-video-icon-container">
                <span className="dashicons dashicons-backup" />
                <div>{__('Processing', 'isset-video-publisher')}</div>
            </div>
        </div>;
    }

    render() {
        const {uuid, filename, created, duration, preview, size, stills, checked} = this.props;

        return <tr className="iv-v-align-top">
            <td>
                <input type="checkbox" checked={checked} onChange={this.toggleChecked} />
            </td>
            <td onClick={event => this.showDetails(event, uuid)}>
                {this.renderStill(preview, stills || [])}
            </td>
            <td>
                {secondsToHours(duration)}
            </td>
            <td>
                {size}
            </td>
            <td className="isset-video-filename">
                {this.renderFilename(filename, uuid)}
            </td>
            <td>
                {moment(created).format('MM-DD-YYYY HH:mm')}
            </td>
        </tr>;
    }

    renderFilename(filename, uuid) {
        if (uuid) {
            return <a href="#" onClick={event => this.showDetails(event, uuid)}>{filename}</a>;
        }

        return <strong>{filename}</strong>;
    }
}

export default VideoItem;