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

  let stillCycleInterval = null;
  let originalSrc = null;

  $(document).on({
    mouseleave: function () {
      $(this).get(0).pause();
    },
    mouseenter: function () {
      $(this).get(0).play();
    }
  }, '.video-publisher-preview-video').on({
    mouseenter: function(){
      let count = 0;
      let img = $(this);

      if (img.data('preview') === '') {
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
      }
      else {
        img.after(`<video class="video-publisher-preview-video" style="display: none" autoplay muted loop><source src="${img.data('preview')}"></video>`);
        stillCycleInterval = setTimeout(function () {
          $('.video-publisher-preview-video').show();
          img.remove();
        }, 500)
      }
    },
    mouseleave: function(){
      let img = $(this);

      if (img.data('preview') === '') {
        if (stillCycleInterval !== null) {
          clearInterval(stillCycleInterval);
          $(this).attr('src', originalSrc);

          stillCycleInterval = null;
          originalSrc = null;
        }
      }
      else {
        clearTimeout(stillCycleInterval);
        $('.video-publisher-preview-video').remove();
      }
    }
  }, '.video-publisher-thumbnail-stills')
});