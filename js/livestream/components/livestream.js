import ReactDOM from 'react-dom';
import React from 'react';
import {publisherAjax} from '../../ajax';
import LivestreamDetails from './livestreamDetails';
import {__} from '../../labels';
import {createLiveStream, fetchActiveLivestreams, fetchLiveStreamDetails} from '../api/api';

class IssetVideoLivestream extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            livestream: {},
        };
    }

    componentDidMount() {
        this.findActiveLivestream();
    }

    componentWillUnmount() {
        this.eventSource.close();
    }

    findActiveLivestream = async () => {
        const filtered = await fetchActiveLivestreams();
        if (filtered.length > 0) {
            this.findActiveLivestreamDetails(filtered[0].uuid);
        }
    };

    findActiveLivestreamDetails = async uuid => {
        const livestream = await fetchLiveStreamDetails(uuid);

        if (livestream && livestream.uuid) {
            this.setState({livestream});
        }
    }

    createLivestream = async () => {
        const livestream = await createLiveStream();

        if (livestream && livestream.uuid) {
            this.findActiveLivestreamDetails(livestream.uuid);
        }
    };

    updateLivestream = update => {
        const {livestream: {uuid}} = this.state;

        publisherAjax(`api/livestreams/${uuid}`, {}, 'PATCH', update).then(result => {
            const {uuid} = result;

            if (uuid) {
                this.getLivestreamDetails(uuid);
            }
        });
    }

    getLivestreamDetails = uuid => {
        publisherAjax(`api/livestreams/${uuid}`, {}, 'GET').then(result => {
            this.setState({livestream: result});
        });
    }

    render() {
        const {livestream} = this.state;

        return <div>
            <h1>{livestream.name ? livestream.name : __('Livestream', 'isset-video')}</h1>
            <LivestreamDetails livestream={livestream} updateLivestream={this.updateLivestream} createLivestream={this.createLivestream} getLivestreamDetails={this.getLivestreamDetails} />
        </div>;
    }

}

window.addEventListener('load', () => {
    if (typeof adminpage !== 'undefined' && adminpage === 'videos_page_isset-video-livestream') {
        const {loggedIn} = window.IssetVideoPublisherAjax;

        if (loggedIn) {
            const livestreamContainer = document.getElementById('isset-video-livestream');
            ReactDOM.render(<IssetVideoLivestream />, livestreamContainer);
        }
    }
});