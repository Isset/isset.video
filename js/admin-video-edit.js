jQuery(($) => {


  if (adminpage === "edit-php" && typenow === "video-publisher") {
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
});