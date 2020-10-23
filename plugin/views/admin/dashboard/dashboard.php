<div class="video-publisher-admin">
    <?php if ( $isLoggedIn ): ?>
        <h2><?php echo sprintf( __( 'Logged in as %s', 'isset-video-publisher' ), $user['username'] ); ?></h2>
        <?php if ( $stats ): ?>
            <div class="video-publisher-mt-2">
                <span class="video-publisher-bold">Storage used: </span>
                <span><?php echo round( $stats['storage'] / 1e+9, 3 ); ?> GB / <?php echo round( $subscription_limit['storage_limit'] / 1e+9, 3 ); ?> GB</span>
            </div>
            <div>
                <span class="video-publisher-bold">Data streamed: </span>
                <span><?php echo round( $stats['streaming'] / 1e+9, 3 ); ?> GB / <?php echo round( $subscription_limit['streaming_limit'] / 1e+9, 3 ) | round(3); ?> GB</span>
            </div>
        <?php endif; ?>
        <a class="btn btn-danger mt-20" href="<?php echo $logout_url; ?>"><?php _e( 'Logout', 'isset-video-publisher' ); ?></a>
        <a class="btn mt-20" href="<?php echo $video_url; ?>"><?php _e('Go to videos', 'isset-video-publisher' ); ?></a>

    <?php else: ?>
        <a class="btn mt-20" href="<?php echo $login_url; ?>">Login to isset.video</a>
    <?php endif; ?>
</div>