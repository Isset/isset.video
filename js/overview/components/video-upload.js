import React from 'react';
import ReactDOM from 'react-dom';
import {__} from '@wordpress/i18n';
import Queue from '../upload/queue';
import {UPLOAD_ADDED, UPLOAD_ARCHIVE_FAILED, UPLOAD_ARCHIVED, UPLOAD_ARCHIVING} from '../upload/uploadStatuses';
import PropTypes from 'prop-types';
import {updateFaviconProgress} from '../../progress-favicon';
import {wpAjax} from '../../ajax';
import "../../../scss/upload.scss"
import {showMessage} from '../../toast/helper/toast';
import filesize from '../helpers/filesize';

const initialUploadState = {
    files: [],
    uploadStatus: '',
    totalProgress: 0,
    uploadsAllowed: false,
    filesizeLimit: 0,
};

class VideoUpload extends React.Component {
    static propTypes = {
        show: PropTypes.bool.isRequired,
        toggleShow: PropTypes.func.isRequired,
        refresh: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {...initialUploadState};

        this.getUploadsAllowed();
        this.getFilesizeLimit();
    }

    resetUploads = () => {
        this.queue = null;

        this.setState({...initialUploadState});
        this.getUploadsAllowed();
        this.getFilesizeLimit();
    };

    filesAdded = (event) => {
        const {target: {files}} = event;
        const selectedFiles = [];
        const {filesizeLimit} = this.state;

        const filtered = Array.from(files).filter(file => {
            if (file.size > filesizeLimit) {
                showMessage(`File ${file.name} is too large.`, 'error');
                return false;
            }

            return true;
        });

        for (let i = 0; i < filtered.length; i++) {
            let file = filtered[i];

            file.progress = 0;
            file.status = UPLOAD_ADDED;
            selectedFiles.push(file);
        }

        this.setState({files: [...selectedFiles]});
    };

    doUpload = async () => {
        const {files} = this.state;

        if (files.length === 0) {
            return;
        }

        this.setState({uploadStatus: 'uploading'});
        this.queue = new Queue(files, this.uploadProgress, this.uploadsFinished);
        this.queue.start(3);

        window.onbeforeunload = () => "";
    }

    getUploadsAllowed = () => {
        wpAjax('isset-video-fetch-uploading-allowed').then(response => {
            this.setState({uploadsAllowed: response.allowed ? response.allowed : false});
        });
    };

    getFilesizeLimit = () => {
        wpAjax('isset-video-fetch-subscription-limits').then(response => {
            this.setState({filesizeLimit: typeof response[0].storage_limit !== undefined ? response[0].storage_limit : 1000000000});
        });
    };

    cancelUploads = () => {
        this.queue && this.queue.cancelUploads();
        this.setState({uploadStatus: 'cancelled'});
        window.onbeforeunload = null;
    }
    uploadProgress = (files, totalProgress) => {
        updateFaviconProgress(totalProgress);
        document.title = `${totalProgress}%`;

        this.setState({files: [...files], totalProgress});
    }

    uploadsFinished = () => {
        this.setState({uploadStatus: 'finished'}, this.props.refresh());
        window.onbeforeunload = null;
    }

    renderProgressBars = files => {
        return <div className="isset-video-progressbar-container">
            {files.map((file, index) => {
                return <div key={`progressbar-${index}`}>
                    <div className="video-publisher-mb-2" id={`videoPublisherFile${index}`}>
                        {__('Uploading', 'isset-video')}: <div id={`indicator${index}`} className="indicator">{file.progress}%</div>
                        <div className="progress">
                            <div id={`progressBar${index}`} className="progress-bar" role="progressbar" style={{width: `${file.progress}%`}} />
                        </div>
                        <div id={`uploadingText${index}`} className="uploading-text">
                            {this.renderUploadText(file.status, file.name)}
                        </div>
                    </div>
                    <hr />
                </div>
            })}
        </div>;
    }

    renderUploadText = (status, filename) => {
        switch(status) {
            case UPLOAD_ARCHIVING:
                return sprintf(__(`Adding %s to archive`, 'isset-video'), filename);
            case UPLOAD_ARCHIVED:
                return sprintf(__('Successfully added %s to archive', 'isset-video'), filename);
            case UPLOAD_ARCHIVE_FAILED:
                return sprintf(__('Failed adding %s to archive', 'isset-video'), filename);
            default:
                return `${__('Uploading', 'isset-video')}: ${filename}`;
        }
    }

    renderUploadContainerText = () => {
        const {filesizeLimit} = this.state;

        if (filesizeLimit > 5000000000 || filesizeLimit === null) {
            return __('Drop one or more files here or browse your computer.', 'isset-video')
        } else {
            return __('Drop one or more files here or browse your computer.', 'isset-video') + ' ' + sprintf(__('(Max %s)', 'isset-video'), `${filesize(filesizeLimit)}`);
        }
    };

    renderFileList = files => {
        if (files.length > 0) {
            return __('Selected files', 'isset-video') + ': ' + files.map(file => file.name).join(', ');
        }

        return <>
            <div className="text-wrapper">
                <span className="dashicons dashicons-download download-icon" />
            </div>
            <div className="text-wrapper">
                <p>{this.renderUploadContainerText()}</p>
            </div>
        </>;
    };

    renderUploaderContent = () => {
        const {files, uploadStatus, uploadsAllowed} = this.state;

        if (!uploadsAllowed) {
            return <p>
                {__('Storage limit is already reached, please remove videos to upload again or upgrade your subscription', 'isset-video')}
                : <a href="https://my.isset.video/subscriptions">https://my.isset.video/subscriptions</a>
            </p>;
        }

        return <>
            {uploadStatus === '' && <div className="phase-select">
                <div className="phase-select-dropzone">
                    <input multiple accept="video/mp4,video/x-m4v,video/x-flv,video/*,.mkv,.ts" type="file" onChange={this.filesAdded} />
                    <div className="selected-files-container video-publisher-p-2" id="phase-select-file">
                        {this.renderFileList(files)}
                    </div>
                </div>
                {files.length > 0 ? <button className="isset-video-btn" onClick={this.doUpload}>{__('Upload', 'isset-video')}</button> : ''}
            </div>}
            {(uploadStatus === 'uploading' || uploadStatus === 'finished') && <div className="phase-upload">
                {this.renderProgressBars(files)}
            </div>}
            {uploadStatus === 'uploading' && <button id="btnCancelUpload" className="isset-video-btn btn-danger" onClick={this.cancelUploads} >
                {__('Cancel upload', 'isset-video')}
            </button>}
            {uploadStatus === 'finished' && <div className="phase-done">
                <div className="isset-video-upload-success">
                    {__('Your files will be queued for transcoding', 'isset-video')}
                </div>
            </div>}
            {uploadStatus === 'cancelled' && <div className="phase-upload">
                <p>{__('Uploads Cancelled', 'isset-video')}</p>
            </div>}
        </>;
    }

    renderUpload = () => {
        const {files, uploadStatus, totalProgress} = this.state;
        const {show, toggleShow} = this.props;

        if (!show) {
            return <div className="isset-video-upload-widget" onClick={() => toggleShow(true)}>
                {files.length > 0 && <div className="isset-video-upload-progressbar-container">
                    <div className="isset-video-progress-label">
                        {sprintf(__(`Uploading %s files`, 'isset-video'), files.length)};
                    </div>
                    <div className="isset-video-progressbar-striped">
                        <div className="isset-video-progressbar-colored" style={{width: `${totalProgress}%`}} />
                    </div>
                </div>}
                <span className="dashicons dashicons-upload" />
            </div>;
        }

        return <div className="isset-video-overlay">
            <div className="isset-video-upload-container video-publisher-flex video-publisher-flex-between ">
                <span className="isset-video-close dashicons dashicons-no" onClick={() => toggleShow(false)} />
                <div className="card isset-video-upload-card">
                    <div className="card-header">
                        <h1 className="wp-heading-inline">
                            <span className="dashicons dashicons-cloud-upload" /> {__('Upload new video', 'isset-video')}
                        </h1>
                    </div>
                    <div className="card-body">
                        <div className="upload-container">
                            {this.renderUploaderContent()}
                        </div>
                    </div>
                    {(uploadStatus === 'finished' || uploadStatus === 'cancelled') && <div className="card-footer">
                        <button className="isset-video-btn isset-video-upload-btn" onClick={() => this.resetUploads()}>
                            <span className="dashicons dashicons-image-rotate" /> {__('Upload More', 'isset-video')}
                        </button>
                    </div>}
                </div>
            </div>
        </div>;
    };

    render() {
        const uploaderContainer = document.getElementById('issetVideoOverlay');

        return ReactDOM.createPortal(this.renderUpload(), uploaderContainer);
    }
}

export default VideoUpload;