<?php if ( $video_url ): ?>
    <div class="video-publisher-video video-player">
        <video <?php echo $controls; ?> <?php echo $autoplay; ?> <?php echo $loop; ?> <?php echo $muted; ?>
           poster="<?php echo $poster; ?>"
           preload="auto"
           class="video-js vjs-big-play-centered vjs-theme-isset"
           controls
           x-webkit-airplay="allow"
           data-ad-url="<?php echo empty($ad_url) ? '' : $ad_url; ?>"
           data-chapters='<?php echo json_encode(empty($chapters) ? array() : $chapters); ?>'
        >
            <source src="<?php echo esc_attr($video_url); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                <?php _e( 'Your browser does not support the video tag.', 'isset-video' ); ?>
            </p>
        </video>
    </div>
<?php else: ?>
    <?php _e( 'Video cannot be loaded', 'isset-video' ); ?>
<?php endif; ?>

<script type="application/javascript">
    if (!document.getElementById('isset-video-custom-player-css')) {
        const linkElement = document.createElement('link');
        linkElement.id = 'isset-video-custom-player-css';
        linkElement.rel = 'stylesheet';
        linkElement.href = '<?php echo $css_url; ?>';

        document.head.appendChild(linkElement);
    }
</script>
