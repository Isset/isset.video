<?php if ( $video_url ): ?>
    <div class="video-publisher-video video-player">
        <video <?php echo $controls; ?> <?php echo $autoplay; ?> <?php echo $loop; ?> <?php echo $muted; ?>
               poster="<?php echo $poster; ?>"
               preload="auto"
               class="video-js vjs-big-play-centered vjs-theme-isset"
               controls
               x-webkit-airplay="allow"
               data-ad-url="<?php echo empty($ad_url) ? '' : $ad_url; ?>"
        >
            <source src="<?php echo esc_attr($video_url); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                <?php _e( 'Your browser does not support the video tag.', 'isset-video' ); ?>
            </p>

            <?php foreach($subtitles as $index => $subtitle): ?>
                <track kind="captions" src="<?php echo $subtitle['url']; ?>" srclang="<?php echo $subtitle['language']; ?>" label="<?php echo $subtitle['label']; ?>" <?php echo $index === 0 ? 'default' : ''; ?>>
            <?php endforeach; ?>

            <?php foreach($chapters as $chapter): ?>
                <track kind="chapters" src="<?php echo $chapter['url']; ?>" srclang="<?php echo $chapter['language']; ?>">
            <?php endforeach; ?>
        </video>
    </div>
<?php else: ?>
    <?php _e( 'Video cannot be loaded', 'isset-video' ); ?>
<?php endif; ?>

<script type="application/javascript">
    const linkElementId = 'isset-video-custom-player-css';
    const existingLink = document.getElementById(linkElementId);

    if (!existingLink) {
        const linkElement = document.createElement('link');
        linkElement.id = linkElementId;
        linkElement.rel = 'stylesheet';
        linkElement.href = '<?php echo $css_url; ?>';

        document.head.appendChild(linkElement);
    }
</script>
