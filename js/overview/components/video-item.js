import React from 'react';
import moment from 'moment';
import {secondsToHours} from '../helpers/duration';

class VideoItem extends React.Component {

    toggleChecked = event => {
        const {uuid} = this.props;
        const {checked} = event.target;

        this.props.onCheck(uuid, checked);
    };

    showDetails = (event, uuid) => {
        event.preventDefault();
        this.props.onSelect(uuid);
    };

    renderStill = (preview, stills) => {
        if (preview) {
            return <video className="video-publisher-preview-video">
                <source src={preview} />
            </video>;
        }

        if (stills.length > 0) {
            return <img src={`${stills[0]}?width=300&height=168`} alt="" />
        }

        return <div className="isset-video-thumb-placeholder">
            <div className="isset-video-icon-container">
                <span className="dashicons dashicons-backup" />
                <div>Processing</div>
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
                {this.renderStill(preview, stills)}
            </td>
            <td>
                {secondsToHours(duration)}
            </td>
            <td>
                {size}
            </td>
            <td>
                <a href="#" onClick={event => this.showDetails(event, uuid)}>{filename}</a>
            </td>
            <td>
                {moment(created).format('MM-DD-YYYY HH:mm')}
            </td>
        </tr>;
    }

}

export default VideoItem;