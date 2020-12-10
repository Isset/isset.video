import React from 'react';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';
import {dateTimeToHumanlyReadable} from '../../overview/helpers/time';
import AdminCopyText from '../../overview/components/admin-copy-text';

class LivestreamDetails extends React.Component {
    static propTypes = {
        livestream: PropTypes.object.isRequired,
        updateLivestream: PropTypes.func.isRequired,
        createLivestream: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {
            name: '',
            description: '',
            status: '',
        }
    }

    componentDidMount() {
        this.updateLivestreamState();
    }

    componentDidUpdate(prevProps, prevState) {
        const {livestream} = this.props;

        if (livestream !== prevProps.livestream) {
            this.updateLivestreamState();
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

        if (livestream.date_ended) {
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

    render() {
        const {uuid, stream_key, rtmp_url, date_created, date_started, date_ended, embed_url, share_url} = this.props.livestream;
        const {name, description, status} = this.state;

        return <div className="isset-video-container video-publisher-flex video-publisher-flex-between">
            <div className="iv-w-50 video-publisher-p-2">
                <div className="isset-video-livestream-container video-publisher-flex video-publisher-justify-center video-publisher-align-center">
                    {status === 'new' && <div>
                        <h2>{__('No livestream created yet', 'isset-video')}</h2>
                        <button className="isset-video-btn-orange" type="button" onClick={this.props.createLivestream}>
                            {__('Create Livestream', 'isset-video')}
                        </button>
                    </div>}

                    {status === 'waiting' && <div>
                        <h2>{__('Livestream has not yet started', 'isset-video')}</h2>
                        <p>
                            {sprintf(__('Livestream is waiting for input at: %s', 'isset-video'), `${rtmp_url}${stream_key}`)}
                        </p>
                    </div>}

                    {status === 'active' && <iframe allowFullScreen={true} className="embed-responsive-item bg-black" src={embed_url}/>}

                    {status === 'ended' && <div>
                        <h2>{__('Livestream has ended', 'isset-video')}</h2>
                    </div>}
                </div>
            </div>
            <div className="iv-w-50 video-publisher-p-2">
                {uuid && <>
                    <table className="iv-w-100 isset-video-details-table isset-video-livestream-table">
                        <tbody>
                            <tr>
                                <td>{__('Stream Url', 'isset-video')}:</td>
                                <td>{rtmp_url}</td>
                            </tr>
                            <tr>
                                <td>{__('Stream Key', 'isset-video')}:</td>
                                <td>{stream_key}</td>
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
                </>}

                {!uuid && <div className="isset-video-livestream-info video-publisher-p-2 video-publisher-pl-4">
                    <h2><span className="dashicons dashicons-video-alt" /> {__('Start streaming', 'isset-video')}</h2>
                    <p>
                        {__('Streaming info', 'isset-video')}
                    </p>
                </div>}
            </div>
        </div>;
    }
}

export default LivestreamDetails;