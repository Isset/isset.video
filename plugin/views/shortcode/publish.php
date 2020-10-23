<?php if ( $video_url ): ?>
    <div class="video-publisher-video video-player">
        <video <?php echo $controls; ?> <?php echo $autoplay; ?> <?php echo $loop; ?> <?php echo $muted; ?>
               poster="<?php echo $poster; ?>" preload="auto" class="video-js vjs-big-play-centered vjs-default-skin" controls x-webkit-airplay="allow">
            <source src="<?php echo esc_attr($video_url); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                <?php _e( 'Your browser does not support the video tag.', 'isset-video-publisher'); ?>
            </p>
        </video>
    </div>
<?php else: ?>
    <?php _e( 'Video cannot be loaded', 'isset-video-publisher' ); ?>
<?php endif; ?>