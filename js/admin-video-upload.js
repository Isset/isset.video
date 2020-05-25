import "../scss/upload.scss"
import {updateFaviconProgress} from "./progress-favicon";

jQuery(($) => {
  // noinspection JSUnresolvedVariable
  const {nonce, ajaxUrl} = IssetVideoPublisherAjax;

  let fileSelect = $('.phase-select input[type="file"]');

  fileSelect.change(function () {
    let fileDisplay = $("#phase-select-file");
    let file = this.files[0];

    if (file) {
      fileDisplay.html('Selected files: <ul>' + [...this.files].map(file => '<li>' + file.name + '</li>').join('') + '</ul>')
    } else {
      fileDisplay.html('')
    }
  });

  $(".phase-select button").click(async () => {
    $(window).bind('beforeunload', function(){
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
                    <div class="indicator">0%</div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0;"></div>
                    </div>
                    <div class="uploading-text">Uploading: ${fileSelect[0].files[i].name}</div>
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
      .append("Succesfully uploaded ", $('<span>').text(response.filename), ", Please wait while we get it ready for you")
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
    form.set("file", fileSelect.prop("files")[fileIndex]);

    let uploadXhr = new XMLHttpRequest();

    uploadXhr.upload.addEventListener("progress", (e) => {
      const progressContainer = $(`#videoPublisherFile${fileIndex}`);
      const percent = Math.ceil((e.loaded / e.total) * 100);
      progressContainer.find('.progress-bar').width(`${percent}%`);
      progressContainer.find('.indicator').text(`${percent}%`);

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

    return {
      response,
      registerResponse
    };
  }
});
