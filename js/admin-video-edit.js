jQuery(($) => {
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