import React from 'react';
import ReactDOM from 'react-dom';
import {archiveAjax, publisherAjax} from '../../ajax';
import {secondsToHours} from '../helpers/time';
import moment from 'moment';
import filesize from '../helpers/filesize';
import AdminCopyText from './admin-copy-text';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';
import {UPLOAD_ADDED} from '../upload/uploadStatuses';
import {uploadCustomStill} from '../upload/api';

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
            settingCustomImage: false,
            files: [],
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

        if (!uuid) {
            this.props.onClose();
            return null;
        }

        publisherAjax(`api/publishes/${uuid}`).then(json => {
            const {playout: {playout_url, player_url}, assets} = json;

            this.setState({
                playout: playout_url,
                playerUrl: player_url,
                assets,
                defaultImage: this.findDefaultImage(assets),
                settingCustomImage: false,
                files: [],
            });
        }).catch(err => console.log(err));
    };

    filesAdded = (event) => {
        const {target: {files}} = event;
        const selectedFiles = [];

        for (let i = 0; i < files.length; i++) {
            let file = files[i];

            file.progress = 0;
            file.status = UPLOAD_ADDED;
            selectedFiles.push(file);
        }

        this.setState({files: [...selectedFiles]});
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

    hasCustomImage = assets => assets.filter(asset => asset.id === 0).length > 0;
    uploadCustomImage = () => {
        this.setState({settingCustomImage: true});
    };

    doUpload = () => {
        const {uuid, files} = this.state;

        if (files.length === 1) {
            const result = uploadCustomStill(uuid, files[0]);

            result.then(() => this.loadPublish());
        }
    };

    closeCustomStillDialog = () => {
        this.setState({settingCustomImage: false, files: []});
    };

    renderFilenames = files => {
        if (files.length === 1) {
            return <div className="text-wrapper">
                Uploading file: {files[0].name}
            </div>;
        }

        return <>
            <div className="text-wrapper">
                <span className="dashicons dashicons-download download-icon" />
            </div>
            <div className="text-wrapper">
                <p>{__('Drop an image file here or browse your computer.', 'isset-video')}</p>
            </div>
        </>;
    };

    renderUploadCustomStill = () => {
        const {files} = this.state;

        return <div className="isset-video-overlay video-publisher-admin">
            <div className="isset-video-custom-still-dialog">
                <h2 className="video-publisher-text-white">Upload Custom Still</h2>
                <div className="phase-select isset-video-image-upload">
                    <div className="phase-select-dropzone">
                        <input accept=".jpg,.jpeg,.png" type="file" onChange={this.filesAdded} />
                        <div className="selected-image-container video-publisher-p-2">
                            {this.renderFilenames(files)}
                        </div>
                    </div>
                    {files.length > 0 ? <button className="isset-video-btn video-publisher-mr-2" onClick={this.doUpload}>{__('Upload', 'isset-video')}</button> : ''}
                    <button className="isset-video-btn btn-danger" onClick={this.closeCustomStillDialog}>{__('Cancel', 'isset-video')}</button>
                </div>
            </div>
        </div>;
    };

    renderDetails = () => {
        const {videoName, uuidParsed, assets, playerUrl, defaultImage, publish: {presets}} = this.state;
        const {file: {
            date_created,
            duration,
            height,
            size,
            width,
            settingCustomImage,
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

                    <p>{__('Use the following code to embed this video', 'isset-video')}:</p>

                    <AdminCopyText text={uuidParsed} />

                    <div className="isset-video-stills-container">
                        {!this.hasCustomImage(assets) && <div className="isset-video-add-default-still" onClick={this.uploadCustomImage}>
                            <span className="dashicons dashicons-plus" />
                        </div>}
                        {assets.map(asset => <div className="video-block-relative video-publisher-inline-block">
                            {asset.id === 0 && <span className="dashicons dashicons-update-alt still-replace" onClick={this.uploadCustomImage} />}
                            <img className={defaultImage === asset.id ? 'isset-video-default-image' : undefined} src={`${asset.url}?width=150&height=80`} key={`asset-${asset.id}`} onClick={() => this.setDefaultImage(asset.id)} />
                        </div>)}
                    </div>
                </div>

                <div className="iv-w-50">
                    <table className="isset-video-details-table">
                        <tbody>
                            <tr>
                                <td>{__('Filename', 'isset-video')}:</td>
                                <td>{videoName}</td>
                            </tr>
                            <tr>
                                <td>{__('Created', 'isset-video')}:</td>
                                <td>{moment(date_created).format('YYYY-MM-DD HH:mm')}</td>
                            </tr>
                            <tr>
                                <td>{__('Filesize', 'isset-video')}:</td>
                                <td>{filesize(size)}</td>
                            </tr>
                            <tr>
                                <td>{__('Length', 'isset-video')}:</td>
                                <td>{secondsToHours(duration)}</td>
                            </tr>
                            <tr>
                                <td>{__('Resolution', 'isset-video')}:</td>
                                <td>{width && height ? `${width}x${height}` : '-'}</td>
                            </tr>
                            <tr>
                                <td>{__('Presets', 'isset-video')}:</td>
                                <td className="isset-video-preset-container">{presets.map(preset => <span className="isset-video-preset" key={`preset-${preset}`}>{preset}</span>)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>;
    };

    render() {
        const {settingCustomImage} = this.state;
        const detailsContainer = document.getElementById('issetVideoOverlay');

        if (settingCustomImage) {
            return ReactDOM.createPortal(this.renderUploadCustomStill(), detailsContainer);
        }

        return ReactDOM.createPortal(this.renderDetails(), detailsContainer);
    }
}

export default AdminVideoDetails;