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
      fileDisplay.html('Selected file: ' + file.name)
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

    $(".phase-select").hide();
    $(".phase-upload").show();

    let uploadReqForm = new URLSearchParams();

    uploadReqForm.set('_ajax_nonce', nonce);
    uploadReqForm.set('action', 'isset-video-fetch-upload-url');

    let uploadUrlResp = await fetch(ajaxUrl, {
      method: 'POST',
      body: uploadReqForm
    });

    let uploadUrlObj = await uploadUrlResp.json();

    let filename = fileSelect[0].files[0].name;
    $("#uploadingTitle")[0].innerHTML = filename;

    let form = new FormData();
    form.set("file", fileSelect.prop("files")[0], fileSelect.val().split("/").pop());

    let uploadXhr = new XMLHttpRequest();

    uploadXhr.upload.addEventListener("progress", (e) => {
      const progressContainer = $('.phase-upload');
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

    $('.phase-upload').hide();
    $('.phase-done')
      .html("")
      .append("Succesfully uploaded ", $('<span>').text(response.filename), ", Please wait while we get it ready for you")
      .show();


    let registerResponse = await fetch(ajaxUrl, {
      method: 'POST',
      body: new URLSearchParams([
        ["action", "isset-video-register-upload"],
        ["_ajax_nonce", nonce],
        ["id", response.id],
      ])
    });

    $(window).unbind(function(){
      return true;
    });

    let registerObj = await registerResponse.json();
    location.href = registerObj.url;
  });

  $('#btnCancelUpload').click(function () {
    location.reload();
  });
});
