<div class="wrap video-publisher-admin">
    <?php if ( $logged_in ): ?>
        <div class="card">
            <?php /*translators: %s is the user's username */ ?>
            <h2><?php echo sprintf ( __( 'Hi, %s', 'isset-video' ), $user['username'] ); ?></h2>
            <div><?php _e( 'You are now connected to my.isset.video. Click the button below to unlink your account and logout.', 'isset-video' ); ?></div>
            <a class="isset-video-btn btn-danger mt-20" href="<?php echo $logout_url; ?>"><?php _e('Logout', 'isset-video' ); ?></a>
            <a class="isset-video-btn mt-20" href="<?php echo $video_url; ?>"><?php _e( 'Go to videos', 'isset-video' ); ?></a>
        </div>
    <?php else: ?>
        <div class="card">
            <h2><?php _e( 'Connect to my.isset.video', 'isset-video' ); ?></h2>
            <div><?php _e( 'Please connect your my.isset.video account by clicking the button below. We will redirect you to my.isset.video to ask for your permission.', 'isset-video' ); ?></div>
            <a class="isset-video-btn mt-20" href="<?php echo $login_url; ?>"><?php _e( 'Connect to my.isset.video', 'isset-video' ); ?></a>
        </div>
    <?php endif; ?>
</div>