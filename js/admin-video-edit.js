import Chart from 'chart.js';

jQuery(($) => {


  if (adminpage === "edit-php" && typenow === "video-publisher") {

    // Sync button
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

  $(document).on({
    mouseleave: function () {
      $(this).get(0).pause();
    },
    mouseenter: function () {
      $(this).get(0).play();
    }
  }, '.video-publisher-preview-video').on({
    mouseenter: function(){
      let video = $(this).siblings('.video-publisher-preview-video');
      $(this).remove();
      video.show().get(0).play();
    },
  }, '.video-publisher-thumbnail-stills').one({
    click: function () {
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
        $(this).text('Syncing' + '.'.repeat(i + 1))
      }, 500)
    }
  }, '#videoPublisherSyncVideos')
});
