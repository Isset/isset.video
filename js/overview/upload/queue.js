import {updateFaviconProgress} from '../../progress-favicon';
import Flow from '@flowjs/flow.js';
import {
    UPLOAD_ADDED,
    UPLOAD_ARCHIVE_FAILED,
    UPLOAD_ARCHIVED,
    UPLOAD_ARCHIVING,
    UPLOAD_IN_PROGRESS
} from './uploadStatuses';

export default class Queue {
    constructor(files, onProgress, onFinished) {
        const {uploaderUrl} = window.IssetVideoArchiveAjax;

        this.files = files;
        this.queue = [];
        this.uploadPercentages = {};
        this.onProgress = onProgress;
        this.onFinished = onFinished;
        this.uploaderUrl = uploaderUrl;
    }

    uploadNext = () => {
        for (const index in this.files) {
            if (this.files[index].status === UPLOAD_ADDED) {
                this.files[index].status = UPLOAD_IN_PROGRESS;

                return this.uploadFileToUploader(index).then(this.uploadNext);
            }
        }

        const unfinished = this.files.filter(file => [UPLOAD_ADDED, UPLOAD_IN_PROGRESS].includes(file.status));
        if (unfinished.length === 0) {
            this.onFinished();
        }
    }

    start(amount = 3) {
        const uploadsToStart = amount < this.files.length ? amount : this.files.length;

        for (let i = 0; i < uploadsToStart; i++) {
            this.uploadFileToUploader(i).then(this.uploadNext);
        }
    }

    cancelUploads() {
        this.queue.forEach(flow => flow.cancel());
    }

    onUploadProgress = async (flow, fileIndex) => {
        let percent = this.convertProgressToPercentage(flow.progress());
        this.uploadPercentages[fileIndex] = percent;
        this.files[fileIndex].progress = percent;
    };

    onUploadComplete = async (file, fileIndex) => {
        let filename = file.file.name;
        let data = {
            filename: filename,
            url: this.downloadUrl(file.uniqueIdentifier, filename),
        }

        this.files[fileIndex].status = UPLOAD_ARCHIVING;
        this.returnProgress();

        await this.wpAjax('isset-video-create-archive-file', data).then(response => {
            if (response.uuid) {
                this.files[fileIndex].status = UPLOAD_ARCHIVED;
                this.returnProgress();
            }
        }).catch(error => {
            this.files[fileIndex].status = UPLOAD_ARCHIVE_FAILED;
            this.returnProgress();
        });
    }

    uploadFileToUploader = async (fileIndex) => {
        let flow = new Flow({
            target: this.uploaderUrl + 'upload',
            chunkSize: 1048576 * 4, //4mb
            simultaneousUploads: 3,
        });

        let file = this.files[fileIndex];
        if (file) {
            flow.addFile(file);
            flow.upload();

            this.queue.push(flow);
        }

        return new Promise((resolve, reject) => {
            flow.on('progress', (e) => {
                this.onUploadProgress(flow, fileIndex);
                this.returnProgress();
            });

            flow.on('fileSuccess', async (file) => {
                this.onUploadComplete(file, fileIndex).then(() => resolve()).catch(() => reject());
            });
        });
    };

    returnProgress = () => {
        let overallPercentage = this.calculateOverallPercentage(this.uploadPercentages);
        this.onProgress(this.files, overallPercentage);
    }

    downloadUrl = (identifier, filename) => `${this.uploaderUrl}download/${identifier}/${encodeURI(filename)}`;
    convertProgressToPercentage = progress => Math.round(progress * 100);
    calculateOverallPercentage = percentages => {
        const numberOfUploads = Object.keys(percentages).length;
        let total = 0;

        if (numberOfUploads === 0) {
            return 0;
        }

        for (const index of Object.keys(percentages)) {
            total += percentages[index];
        }

        return Math.round(total / (numberOfUploads * 100) * 100);
    };

    wpAjax = async (action, post = undefined) => {
        const {nonce, ajaxUrl} = window.IssetVideoPublisherAjax;
        let form = new FormData();

        form.set('_ajax_nonce', nonce);
        form.set('action', action);

        if (post) {
            for (const key of Object.keys(post)) {
                form.set(key, post[key]);
            }
        }

        let archiveUrlPromise = await fetch(ajaxUrl, {
            method: 'POST',
            body: form
        });

        return new Promise((resolve, reject) => {
            archiveUrlPromise.json().then(json => {
                resolve(json);
            }).catch(err => {
                reject(err);
            })
        });
    };
};

