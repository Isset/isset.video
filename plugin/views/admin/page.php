<div class="wrap video-publisher-admin">
    <?php if ( $logged_in ): ?>
        <div class="card">
            <h2><?php echo sprintf ( __('Hi, %s', 'isset-video-publisher'), $user['username'] ); ?></h2>
            <div>You are now connected to my.isset.video. Click the button below to unlink your account and logout.</div>
            <a class="btn btn-danger mt-20" href="<?php echo $logout_url; ?>"><?php _e("Logout", 'isset-video-publisher'); ?></a>
            <a class="btn mt-20" href="<?php echo $video_url; ?>"><?php _e("Go to videos", 'isset-video-publisher'); ?></a>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Connect to my.isset.video</h2>
            <div>Please connect your my.isset.video account by clicking the button below. We will redirect you to my.isset.video to ask for your permission.</div>
            <a class="btn mt-20" href="<?php echo $login_url; ?>"><?php _e("Connect to my.isset.video", 'isset-video-publisher'); ?></a>
        </div>
    <?php endif; ?>
</div>
