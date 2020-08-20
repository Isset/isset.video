import "../scss/upload.scss"
import {updateFaviconProgress} from "./progress-favicon";

jQuery(($) => {
    // noinspection JSUnresolvedVariable
    const {nonce, ajaxUrl} = IssetVideoPublisherAjax;

    let fileSelect = $('.phase-select input[type="file"]');
    let rootFolder = '';
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
        if ($('#addToArchive').get(0).checked) {
            await wpAjax('isset-video-fetch-archive-url').then(res => {
                archiveUrl = res.url
            });

            await wpAjax('isset-video-fetch-archive-token').then(res => {
                archiveToken = res.token;
            });

            await getArchiveRootFolder().then(res => {
                rootFolder = res.root_folder
            });
        }

        $(window).bind('beforeunload', function () {
            return 'Are you sure you want to leave?';
        });

        if (fileSelect.val() === "") {
            return;
        }

        let res;
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

        for (const file of fileList) {
            res = await uploadFile(fileList.indexOf(file));
        }

        let {response, registerResponse} = res;

        $(window).off('beforeunload');

        $('.phase-upload').hide();
        $('.phase-done')
            .html("")
            .append("Succesfully uploaded ", $('<span>').text(fileList.map(file => file.name).join(', ')), ", Please wait while we get it ready for you")
            .show();

        let registerObj = await registerResponse.json();
        window.onbeforeunload = null;
        location.href = registerObj.url;
    });

    $('#btnCancelUpload').click(function () {
        location.reload();
    });

    async function uploadFile(fileIndex) {
        let uploadReqForm = new URLSearchParams();

        uploadReqForm.set('_ajax_nonce', nonce);
        uploadReqForm.set('action', 'isset-video-fetch-upload-url');

        let uploadUrlResp = await fetch(ajaxUrl, {
            method: 'POST',
            body: uploadReqForm
        });

        let uploadUrlObj = await uploadUrlResp.json();
        let filename = fileSelect[0].files[fileIndex].name;
        let form = new FormData();
        let uploadXhr = new XMLHttpRequest();

        form.set("file", fileSelect.prop("files")[fileIndex], filename);

        uploadXhr.upload.addEventListener("progress", (e) => {
            const percent = Math.ceil((e.loaded / e.total) * 100);
            $(`#progressBar${fileIndex}`).width(`${percent}%`);
            $(`#indicator${fileIndex}`).text(`${percent}%`);

            updateFaviconProgress(percent);
            $(document).prop('title', `${percent}% - ${filename}`);
        });

        uploadXhr.open("POST", uploadUrlObj.url);
        uploadXhr.send(form);
        let uploadPromise = new Promise((res, err) => {
            uploadXhr.addEventListener('readystatechange', (e) => {
                if (uploadXhr.readyState === uploadXhr.DONE) {
                    res(uploadXhr.responseText);
                }
            });

            uploadXhr.addEventListener('error', (e) => {
                err("Request failed");
            })
        });

        let response = JSON.parse(await uploadPromise);

        let registerResponse = await fetch(ajaxUrl, {
            method: 'POST',
            body: new URLSearchParams([
                ["action", "isset-video-register-upload"],
                ["_ajax_nonce", nonce],
                ["id", response.id],
            ])
        });

        if ($('#addToArchive').get(0).checked) {
            form.set('folder', rootFolder);

            let archiveXHR = new XMLHttpRequest();
            archiveXHR.open("POST", 'https://test.archive.isset.video/api/files/upload');
            archiveXHR.setRequestHeader('x-token-auth', archiveToken);
            archiveXHR.send(form);

            $(`#uploadingText${fileIndex}`).text(`Adding ${filename} to archive`)

            await new Promise((res, err) => {
                archiveXHR.addEventListener('readystatechange', (e) => {
                    if (archiveXHR.readyState === archiveXHR.DONE) {
                        $(`#uploadingText${fileIndex}`).text(`Successfully added ${filename} to archive`)
                        res(archiveXHR.responseText);
                    }
                });

                archiveXHR.addEventListener('error', (e) => {
                    $(`#uploadingText${fileIndex}`).text(`Failed adding ${filename} to archive`)
                    err("Request failed");
                })
            });
        }

        return {
            response,
            registerResponse
        };
    }

    async function getArchiveRootFolder() {
        const response = await fetch(`${archiveUrl}api/root`, {
            headers: {
                'x-token-auth': archiveToken
            }
        });
        return response.json();
    }

    async function wpAjax(action) {
        let form = new URLSearchParams();

        form.set('_ajax_nonce', nonce);
        form.set('action', action);

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
        })
    }
});
