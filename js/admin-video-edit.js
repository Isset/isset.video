jQuery(($) => {


  if (adminpage === "edit-php") {
    let syncButton = $('<a class="page-title-action" />').text("Sync videos");
    $('.wp-header-end').before(syncButton);

    syncButton.one('click', () => {
      $.post(IssetVideoPublisherAjax.ajaxUrl, {
        _ajax_nonce: IssetVideoPublisherAjax.nonce,
        post_id: IssetVideoPublisherAjax.postId,
        url: $(this).data('url'),
        action: 'isset-video-sync'
      }).then(() => {
        location.reload()
      });

      let i = 0;
      setInterval(() => {
        i = (i + 1) % 3;
        syncButton.text('Syncing' + '.'.repeat(i + 1))
      }, 500)
    });
  }


  $('.thumbnail-item button').click(function () {
    // noinspection JSUnresolvedVariable
    $.post(IssetVideoPublisherAjax.ajaxUrl, {
      _ajax_nonce: IssetVideoPublisherAjax.nonce,
      post_id: IssetVideoPublisherAjax.postId,
      url: $(this).data('url'),
      action: 'isset-video-set-image'
    }).then(() => {
      location.reload()
    })
  });

  let timeout = null;
  let originalSrc = null;

  $('.video-publisher-thumbnail-stills').hover(function () {
    let count = 0;
    let img = $(this);
    let stills = $(this).siblings('div.video-publisher-hidden.video-publisher-possible-stills').children();

    originalSrc = img.attr('src');

    if (stills) {
      img.attr('src', stills[count].innerHTML);
      count++;

      timeout = setInterval(function () {
        img.attr('src', stills[count].innerHTML);
        count = stills.length - 1 === count ? 0 : count + 1;
      }, 800)
    }
  }, function () {
    if (timeout !== null) {
      clearInterval(timeout);
      $(this).attr('src', originalSrc);

      timeout = null;
      originalSrc = null
    }
  });
});