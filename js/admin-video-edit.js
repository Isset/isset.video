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

  let stillCycleInterval = null;
  let originalSrc = null;

  $('.video-publisher-thumbnail-stills').hover(function () {
    let count = 0;
    let img = $(this);
    let stills = img.data('stills');

    originalSrc = img.attr('src');

    if (stills) {
      img.attr('src', stills[count].url);
      count++;

      stillCycleInterval = setInterval(function () {
        img.attr('src', stills[count].url);
        count = (count + 1) % stills.length;
      }, 800)
    }
  }, function () {
    if (stillCycleInterval !== null) {
      clearInterval(stillCycleInterval);
      $(this).attr('src', originalSrc);

      stillCycleInterval = null;
      originalSrc = null
    }
  });
});