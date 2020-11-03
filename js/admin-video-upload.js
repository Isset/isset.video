import "../scss/upload.scss"
import {updateFaviconProgress} from "./progress-favicon";
import Flow from '@flowjs/flow.js';
import {__} from '@wordpress/i18n';

jQuery(($) => {
    if (adminpage && adminpage !== 'videos_page_isset-video-upload') {
        return;
    }

    // noinspection JSUnresolvedVariable
    const {nonce, ajaxUrl} = IssetVideoPublisherAjax;

    let fileSelect = $('.phase-select input[type="file"]');
    let uploaderUrl = '';
    let archiveUrl = '';
    let archiveToken = '';
    let uploadPercentages = {};

    fileSelect.change(function () {
        let fileDisplay = $("#phase-select-file");
        let files = [...this.files];
        if (files) {
            fileDisplay.html(__('Selected files', 'isset-video-publisher') + ': ' + files.map(file => file.name).join(', '));
        } else {
            fileDisplay.html('');
        }
    });

    $(".phase-select button").click(async () => {
        await wpAjax('isset-video-fetch-archive-url').then(res => {
            archiveUrl = res.url
        });

        await wpAjax('isset-video-fetch-archive-token').then(res => {
            archiveToken = res.token;
        });

        await wpAjax('isset-video-fetch-uploader-url').then(res => {
            uploaderUrl = res.url
        });

        $(window).bind('beforeunload', function () {
            return __('Are you sure you want to leave?', 'isset-video-publisher');
        });

        if (fileSelect.val() === "") {
            return;
        }

        let fileList = [...fileSelect[0].files];

        $(".phase-select").hide();
        $(".phase-upload").show();

        for (const file of fileList) {
            let i = fileList.indexOf(file);
            $('#btnCancelUpload').before(`<div class="video-publisher-mb-2" id="videoPublisherFile${i}">
                    ${__('Uploading', 'isset-video-publisher')}:
                    <div id="indicator${i}" class="indicator">0%</div>
                    <div class="progress">
                        <div id="progressBar${i}" class="progress-bar" role="progressbar" style="width: 0;"></div>
                    </div>
                    <div id="uploadingText${i}" class="uploading-text">${__('Uploading', 'isset-video-publisher')}: ${fileSelect[0].files[i].name}</div>
                </div><hr>`);
        }

        let promises =  [];
        for (const file of fileList) {
            promises.push(uploadFileToUploader(fileList.indexOf(file)));
        }
        await Promise.all(promises);

        $(window).off('beforeunload');

        $('#btnCancelUpload').hide();
        $('.phase-done')
            .html("")
            .append(__('Uploaded', 'isset-video-publisher') + ' ', $('<span>').text(fileList.map(file => file.name).join(', ')))
            .append($('<p class="isset-video-upload-success">').text(__('Your files will be queued for transcoding', 'isset-video-publisher')))
            .show();
        $('.card-footer').show();

        window.onbeforeunload = null;
    });

    $('#btnCancelUpload').click(function () {
        location.reload();
    });

    async function uploadFileToUploader(fileIndex) {
        let flow = new Flow({
            target: uploaderUrl + '/upload'
        });

        let file = fileSelect[0].files[fileIndex];
        if (file) {
            flow.addFile(file);
            flow.upload();
        }

        return new Promise((resolve, reject) => {
            flow.on('progress', (e) => {
                let percent = convertProgressToPercentage(flow.progress());
                uploadPercentages[fileIndex] = percent;

                $(`#progressBar${fileIndex}`).width(`${percent}%`);
                $(`#indicator${fileIndex}`).text(`${percent}%`);

                let overallPercentage = calculateOverallPercentage(uploadPercentages);

                updateFaviconProgress(overallPercentage);
                $(document).prop('title', `${overallPercentage}% - ${file.name}`);
            });

            flow.on('fileSuccess', async (file) => {
                let form = new FormData();
                let filename = file.file.name;

                form.set('filename', filename);
                form.set('url', downloadUrl(file.uniqueIdentifier, filename));

                let data = {
                    filename: filename,
                    url: downloadUrl(file.uniqueIdentifier, filename),
                }

                $(`#uploadingText${fileIndex}`).text(sprintf(__(`Adding %s to archive`, 'isset-video-publisher'), filename));

                await wpAjax('isset-video-create-archive-file', data).then(async response => {
                    if (response.uuid) {
                        $(`#uploadingText${fileIndex}`).text(sprintf(__('Successfully added %s to archive', 'isset-video-publisher'), filename));
                    }
                    resolve();
                }).catch(error => {
                    $(`#uploadingText${fileIndex}`).text(sprintf(__('Failed adding %s to archive', 'isset-video-publisher'), filename) );
                    reject();
                });
            });
        });
    }

    async function wpAjax(action, post = undefined) {
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
    }

    function convertProgressToPercentage(progress) {
        return Math.round(progress * 100);
    }

    function downloadUrl(identifier, filename) {
        return `${uploaderUrl}download/${identifier}/${encodeURI(filename)}`;
    }

    function calculateOverallPercentage(percentages) {
        const numberOfUploads = Object.keys(percentages).length;
        let total = 0;

        if (numberOfUploads === 0) {
            return 0;
        }

        for (const index of Object.keys(percentages)) {
            total += percentages[index];
        }

        return Math.round(total / (numberOfUploads * 100) * 100);
    }
});