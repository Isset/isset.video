import "../scss/upload.scss"
import {updateFaviconProgress} from "./progress-favicon";
import Flow from '@flowjs/flow.js';

jQuery(($) => {
    // noinspection JSUnresolvedVariable
    const {nonce, ajaxUrl, postId} = IssetVideoPublisherAjax;

    let fileSelect = $('.phase-select input[type="file"]');
    let uploaderUrl = '';
    let archiveUrl = '';
    let archiveToken = '';

    fileSelect.change(function () {
        let fileDisplay = $("#phase-select-file");
        let files = [...this.files];
        if (files) {
            fileDisplay.html('Selected files: ' + files.map(file => file.name).join(', '))
        } else {
            fileDisplay.html('')
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
            return 'Are you sure you want to leave?';
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
                    Uploading:
                    <div id="indicator${i}" class="indicator">0%</div>
                    <div class="progress">
                        <div id="progressBar${i}" class="progress-bar" role="progressbar" style="width: 0;"></div>
                    </div>
                    <div id="uploadingText${i}" class="uploading-text">Uploading: ${fileSelect[0].files[i].name}</div>
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
            .append("Succesfully uploaded ", $('<span>').text(fileList.map(file => file.name).join(', ')), ", Please wait while we get it ready for you")
            .show();
        $('.card-footer').show();

        window.onbeforeunload = null;
    });

    $('#btnCancelUpload').click(function () {
        location.reload();
    });

    if (adminpage === "post-new-php" && typenow === "video-publisher") {
        const toVideoButton = $('#videoPublisherSyncVideosAfterUpload');

        toVideoButton.one('click', async (e) => {
            e.preventDefault();
            let i = 0;
            setInterval(() => {
                i = (i + 1) % 3;
                toVideoButton.text('Syncing' + '.'.repeat(i + 1))
            }, 500)

            await wpAjax('isset-video-sync', {post_id: postId});

            location = toVideoButton.attr('href');
        });
    }

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
                $(`#progressBar${fileIndex}`).width(`${percent}%`);
                $(`#indicator${fileIndex}`).text(`${percent}%`);

                updateFaviconProgress(percent);
                $(document).prop('title', `${percent}% - ${file.name}`);
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

                $(`#uploadingText${fileIndex}`).text(`Adding ${filename} to archive`);

                await wpAjax('isset-video-create-archive-file', data).then(async response => {
                    if (response.uuid) {
                        $(`#uploadingText${fileIndex}`).text(`Successfully added ${filename} to archive`);
                    }
                    resolve();
                }).catch(error => {
                    $(`#uploadingText${fileIndex}`).text(`Failed adding ${filename} to archive`);
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
        return `${uploaderUrl}/download/${identifier}/${encodeURI(filename)}`;
    }
});
