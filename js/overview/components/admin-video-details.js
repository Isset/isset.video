import React from 'react';
import ReactDOM from 'react-dom';
import {archiveAjax, publisherAjax} from '../../ajax';
import {secondsToHours} from '../helpers/duration';
import moment from 'moment';
import filesize from '../helpers/filesize';
import AdminCopyText from './admin-copy-text';
import PropTypes from 'prop-types';

class AdminVideoDetails extends React.Component {
    static propTypes = {
        uuid: PropTypes.string.isRequired,
        onClose: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {
            playout: '',
            uuid: '',
            uuidParsed: '',
            stills: '',
            videoName: '',
            videoSize: '',
            autoplay: '',
            file: {},
            publish: {},
            assets: [],
            defaultImage: null,
            playerUrl: '',
        }
    }

    componentDidMount() {
        this.loadVideoDetails();
    }

    componentDidUpdate(prevProps, prevState) {
        const {uuid} = this.state;

        if (prevState.uuid !== uuid) {
            this.loadPublish();
        }
    }

    loadVideoDetails = () => {
        const {uuid} = this.props;

        archiveAjax(`api/files/${uuid}/details`).then(json => {
            const {publish: {publish_uuid}, file, publish} = json;

            this.setState({
                uuid: publish_uuid,
                uuidParsed: `[publish uuid=${publish_uuid}]`,
                stills: file.stills,
                videoName: file.filename,
                videoSize: file.size,
                autoplay: '',
                publish,
                file,
            });
        }).catch(err => console.log(err));
    };

    loadPublish = () => {
        const {uuid} = this.state;

        publisherAjax(`api/publishes/${uuid}`).then(json => {
            const {playout: {playout_url, player_url}, assets} = json;

            this.setState({
                playout: playout_url,
                playerUrl: player_url,
                assets,
                defaultImage: this.findDefaultImage(assets),
            });
        }).catch(err => console.log(err));
    };

    findDefaultImage(assets) {
        const filtered = assets.filter(asset => asset.is_default === true);

        return filtered.length > 0 ? filtered[0].id : null;
    }

    setDefaultImage = id => {
        const {uuid} = this.state;

        publisherAjax(`api/publishes/${uuid}/stills/${id}/set-default`, {}, 'POST').then(json => {
            this.setState({defaultImage: id}, this.loadPublish)
        }).catch(err => console.log(err));
    };

    renderDetails = () => {
        const {videoName, uuidParsed, assets, playerUrl, defaultImage, publish: {presets}} = this.state;
        const {file: {
            date_created,
            duration,
            height,
            size,
            width,
        }} = this.state;

        if (!playerUrl) {
            return null;
        }

        return <div className="isset-video-overlay">
            <div className="isset-video-details video-publisher-flex video-publisher-flex-between ">
                <div className="iv-w-50">
                    <span className="isset-video-close dashicons dashicons-no" onClick={this.props.onClose} />
                    <h1>{videoName}</h1>

                    <iframe
                        allowFullScreen={true}
                        className="embed-responsive-item bg-black"
                        src={playerUrl}
                    />

                    <p>Use the following code to embed this video:</p>

                    <AdminCopyText text={uuidParsed} />

                    <div className="isset-video-stills-container">
                        {assets.map(asset => <img className={defaultImage === asset.id ? 'isset-video-default-image' : undefined} src={`${asset.url}?width=150&height=80`} key={`asset-${asset.id}`} onClick={() => this.setDefaultImage(asset.id)} />)}
                    </div>
                </div>

                <div className="iv-w-50">
                    <table className="isset-video-details-table">
                        <tbody>
                            <tr>
                                <td>Filename:</td>
                                <td>{videoName}</td>
                            </tr>
                            <tr>
                                <td>Created:</td>
                                <td>{moment(date_created).format('MM-DD-YYYY HH:mm')}</td>
                            </tr>
                            <tr>
                                <td>Filesize:</td>
                                <td>{filesize(size)}</td>
                            </tr>
                            <tr>
                                <td>Length:</td>
                                <td>{secondsToHours(duration)}</td>
                            </tr>
                            <tr>
                                <td>Resolution:</td>
                                <td>{`${width}x${height}`}</td>
                            </tr>
                            <tr>
                                <td>Presets:</td>
                                <td className="isset-video-preset-container">{presets.map(preset => <span className="isset-video-preset" key={`preset-${preset}`}>{preset}</span>)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>;
    };

    render() {
        const detailsContainer = document.getElementById('issetVideoOverlay');

        return ReactDOM.createPortal(this.renderDetails(), detailsContainer);
    }
}

export default AdminVideoDetails;