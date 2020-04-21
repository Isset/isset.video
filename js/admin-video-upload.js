import "../scss/upload.scss"

jQuery(($) => {
  // noinspection JSUnresolvedVariable
  const {nonce, ajaxUrl} = IssetVideoPublisherAjax;

  $(".phase-select button").click(async () => {
    let fileSelect = $('.phase-select input[type="file"]');

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

    let form = new FormData();
    form.set("file", fileSelect.prop("files")[0], fileSelect.val().split("/").pop());

    let uploadXhr = new XMLHttpRequest();

    uploadXhr.upload.addEventListener("progress", (e) => {
      const progressBar = $('.phase-upload .progress');
      const percent = (e.loaded / e.total) * 100;
      progressBar.find('.bar').width(`${percent}%`);
      progressBar.find('.indicator').text(`${Math.ceil(percent)}%`);
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

    let registerObj = await registerResponse.json();
    location.href = registerObj.url;
  })
});