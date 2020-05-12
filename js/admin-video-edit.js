import Chart from 'chart.js';

jQuery(($) => {


  if (adminpage === "edit-php" && typenow === "video-publisher") {

    // Dashboard
    $('.wp-header-end').after($('<div id="issetVideoDash" class="video-publisher-mb-2"></div>'));
    $.ajax({
      url: '/?rest_route=/isset-publisher/v1/dashboard',
    }).done(function (res) {
      $('#issetVideoDash').addClass('card').html(res.html);
      let streamingViewsData = $('#videoPublisherStreamingViews').children();

      let chart = new Chart('videoPublisherViewsChart', {
        type: 'line',
        data: {
          labels: streamingViewsData.map(function () {
            return new Date($(this).data('key')).getDate()
          }),
          datasets: [{
            label: 'Views',
            data: streamingViewsData.map(function () {
              return $(this).data('value')
            }),
            backgroundColor: [
                'rgba(46, 112, 157, .2)'
            ],
            borderColor: [
              'rgba(46, 112, 157, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          maintainAspactRatio: false,
          legend: {
            display: false,
          },
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
              }
            }]
          },
        }

      })
    });


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