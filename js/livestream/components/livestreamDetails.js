import React from 'react';
import PropTypes from 'prop-types';
import {__} from '../../labels';
import {dateTimeToHumanlyReadable} from '../../overview/helpers/time';
import AdminCopyText from '../../overview/components/admin-copy-text';
import {cancelLiveStream} from '../api/api';
import {showMessage} from '../../toast/helper/toast';

class LivestreamDetails extends React.Component {
    static propTypes = {
        livestream: PropTypes.object.isRequired,
        updateLivestream: PropTypes.func.isRequired,
        createLivestream: PropTypes.func.isRequired,
        getLivestreamDetails: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {
            name: '',
            description: '',
            status: '',
            creatingLivestream: false,
        }

        this.eventSource = null;
    }

    componentDidMount() {
        this.updateLivestreamState();
        this.initEventSourceListener();
    }

    componentDidUpdate(prevProps, prevState) {
        const {livestream} = this.props;

        if (livestream !== prevProps.livestream) {
            this.updateLivestreamState();
            this.initEventSourceListener();
        }
    }

    componentWillUnmount() {
        this.stopEventSourceListener();
    }

    initEventSourceListener = () => {
        const {livestream: {uuid}} = this.props;
        const {mercureUrl} = window.IssetVideoPublisherAjax;

        if (uuid) {
            this.stopEventSourceListener();
            this.eventSource = new EventSource(mercureUrl + '?topic=' + encodeURIComponent(`https://isset.video/livestreams/${uuid}`));
            this.eventSource.onmessage = (e) => {
                const eventData = JSON.parse(e.data);
                //timeout 20 seconds
                if (eventData.event === 'start') {
                    // wait 20 seconds
                    // fetch new data and update status
                    setTimeout(() => this.props.getLivestreamDetails(uuid), 20000);
                }
                if (eventData.event === 'end') {
                    // wait 20 seconds
                    // fetch new data and update status
                    setTimeout(() => this.props.getLivestreamDetails(uuid), 20000);
                }
            };
        }
    }

    stopEventSourceListener = () => {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }

    updateLivestreamState = () => {
        const {name, description} = this.props.livestream;

        this.setState({
            name: name || '',
            description: description || '',
            status: this.getLivestreamStatus(this.props.livestream),
        });
    };

    getLivestreamStatus = livestream => {
        if (Object.keys(livestream).length === 0) {
            return 'new';
        }

        if (livestream.date_ended && livestream.archive_uuid === null) {
            return 'cancelled';
        } else if (livestream.date_ended) {
            return 'ended';
        } else if (livestream.date_started) {
            return 'active';
        } else {
            return 'waiting';
        }
    };

    nameChange = event => {
        const {value} = event.target;

        this.setState({name: value});
    };

    descriptionChange = event => {
        const {value} = event.target;

        this.setState({description: value});
    };

    updateName = () => {
        const {name} = this.state;

        this.props.updateLivestream({name});
    };

    updateDescription = () => {
        const {description} = this.state;

        this.props.updateLivestream({description});
    };

    createLivestream = () => {
        this.setState({creatingLivestream: true}, () => this.props.createLivestream());
    };

    cancelLivestream = streamKey => {
        const {livestream: {uuid}} = this.props;
        if (confirm(__('Are you sure you want to cancel the current livestream? No VOD will be created', 'isset-video'))) {
            cancelLiveStream(streamKey).then(() => {
                this.setState({creatingLivestream: false}, () => this.props.getLivestreamDetails(uuid));
            });
        }
    };

    render() {
        const {uuid, stream_key, rtmp_url, date_created, date_started, date_ended, embed_url, share_url} = this.props.livestream;
        const {name, description, status, creatingLivestream} = this.state;

        return <div className="isset-video-container">
            <div className="video-publisher-flex video-publisher-flex-between">
                <div className="iv-w-50 video-publisher-p-2">
                    <div className="isset-video-livestream-container video-publisher-flex video-publisher-justify-center video-publisher-align-center">
                        {status === 'cancelled' && <div>
                            <h2>{__('Livestream cancelled', 'isset-video')}</h2>
                            <p>
                                {sprintf(__('This livestream has been cancelled by the user', 'isset-video'), `${rtmp_url}${stream_key}`)}
                            </p>
                            <p>
                                <button disabled={creatingLivestream} className="isset-video-btn-orange isset-video-pointer" type="button" onClick={this.createLivestream}>
                                    {__('Create New Livestream', 'isset-video')}
                                </button>
                            </p>
                        </div>}

                        {status === 'new' && <div>
                            <h2>{__('No livestream created yet', 'isset-video')}</h2>
                            <button disabled={creatingLivestream} className="isset-video-btn-orange isset-video-pointer" type="button" onClick={this.createLivestream}>
                                {__('Create Livestream', 'isset-video')}
                            </button>
                        </div>}

                        {status === 'waiting' && <div>
                            <h2>{__('Livestream has not yet started', 'isset-video')}</h2>
                            <p>
                                {sprintf(__('Livestream is waiting for input at: %s', 'isset-video'), `${rtmp_url}${stream_key}`)}
                            </p>
                        </div>}

                        {status === 'active' && <div className="isset-video-livestream-player-container">
                            <iframe allowFullScreen={true} className="embed-responsive-item bg-black" src={embed_url}/>
                        </div>}

                        {status === 'ended' && <div>
                            <h2>{__('Livestream has ended', 'isset-video')}</h2>
                        </div>}
                    </div>
                    {status === 'waiting' && <div className="video-publisher-p-2 video-publisher-pl-0">
                        <button className="isset-video-btn isset-video-btn-orange" onClick={() => this.cancelLivestream(stream_key)}>
                            <span className="dashicons dashicons-remove video-publisher-mr-2" />
                            <span>{__('Cancel Livestream', 'isset-video')}</span>
                        </button>
                    </div>}
                </div>
                <div className="iv-w-50 video-publisher-p-2">
                    <div className="livestream-logo-container">
                        <a target="_blank" href="<?php echo $isset_video_url; ?>" className="mb-20">
                            <img src={`/wp-content/plugins/isset-video/assets/isset.video.png`} className="isset-video-logo"/>
                        </a>
                    </div>
                    {uuid && <div className="iv-bg-black video-publisher-p-2">
                        <table className="iv-w-100 isset-video-details-table isset-video-livestream-table">
                            <tbody>
                            <tr>
                                <td>{__('Stream Url', 'isset-video')}:</td>
                                <td>
                                    <AdminCopyText text={rtmp_url} onCopied={() => showMessage(__('Copied stream url', 'isset-video'))} />
                                </td>
                            </tr>
                            <tr>
                                <td>{__('Stream Key', 'isset-video')}:</td>
                                <td>
                                    <AdminCopyText text={stream_key} onCopied={() => showMessage(__('Copied stream key', 'isset-video'))} />
                                </td>
                            </tr>
                            <tr>
                                <td>{__('Created', 'isset-video')}:</td>
                                <td>{dateTimeToHumanlyReadable(date_created)}</td>
                            </tr>
                            {date_started && <tr>
                                <td>{__('Started', 'isset-video')}:</td>
                                <td>{dateTimeToHumanlyReadable(date_started)}</td>
                            </tr>}
                            {date_ended && <tr>
                                <td>{__('Ended', 'isset-video')}:</td>
                                <td>{dateTimeToHumanlyReadable(date_ended)}</td>
                            </tr>}
                            <tr>
                                <td>{__('Share Url', 'isset-video')}:</td>
                                <td>
                                    <a href={share_url} target="_blank">
                                        {share_url}
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <input className="iv-w-100 video-publisher-mt-2" type="text" placeholder={__('Name', 'isset-video')} value={name} onChange={this.nameChange} onBlur={this.updateName} />

                        <textarea className="iv-w-100 video-publisher-mt-2" rows={5} placeholder={__('Description', 'isset-video')} value={description} onChange={this.descriptionChange} onBlur={this.updateDescription} />

                        <AdminCopyText text={`[isset-livestream uuid=${uuid}]`} />
                    </div>}

                    {!uuid && <div className="isset-video-livestream-info video-publisher-p-2 video-publisher-pl-4">
                        <h2><span className="dashicons dashicons-video-alt" /> {__('Start streaming', 'isset-video')}</h2>
                        <p>
                            {__('Streaming info', 'isset-video')}
                        </p>
                        <h4 className="iv-t-orange">
                            {__('Rtmp info header', 'isset-video')}
                        </h4>
                        <p>
                            {__('Rtmp info text', 'isset-video')}
                        </p>
                        <h4 className="iv-t-orange">
                            {__('Streamkey info header', 'isset-video')}
                        </h4>
                        <p>
                            {__('Streamkey info text', 'isset-video')}
                        </p>
                    </div>}
                </div>
            </div>
        </div>;
    }
}

export default LivestreamDetails;