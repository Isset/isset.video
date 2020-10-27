<div class="video-publisher-admin">
    <?php if ( $isLoggedIn ): ?>
        <div class="video-publisher-flex" style="flex-direction: row; justify-content: space-between">
            <div class="video-publisher-flex-1 video-publisher-flex" style="flex-direction: column;">
                <?php if ( $stats ): ?>
                    <div class="video-publisher-flex-1">
                        <h2 class="video-publisher-mb-0"><?php echo sprintf( __( 'Logged in as %s', 'isset-video-publisher' ), $user['username'] ); ?></h2>
                        <p class="video-publisher-m-0">
                            <a target="_blank" href="<?php echo $isset_video_url; ?>"><?php _e('isset.video'); ?></a></p>
                        <div class="video-publisher-mt-2">
                            <span class="video-publisher-bold">Storage used: </span>
                            <span><?php echo round( $usage['storage'] / 1e+9, 3 ); ?> GB / <?php echo round( $subscription_limit['storage_limit'] / 1e+9, 3 ); ?> GB</span>
                        </div>
                        <div>
                            <span class="video-publisher-bold">Data streamed: </span>
                            <span><?php echo round( $usage['streaming'] / 1e+9, 3 ); ?> GB / <?php echo round( $subscription_limit['streaming_limit'] / 1e+9, 3 ) | round(3); ?> GB</span>
                        </div>
                        <div>
                            <span class="video-publisher-bold">Subscription type: </span>
                            <span><?php echo $subscription_limit['name']; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="mt-20">
                    <a class="isset-video-btn btn-danger" href="<?php echo $logout_url; ?>"><?php _e("Logout", 'isset-video-publisher'); ?></a>
                </div>
            </div>
            <?php if ( $stats ): ?>
                <div class="video-publisher-flex-1">
                    <div class="">
                        <div class="video-publisher-mb-2 video-publisher-flex video-publisher-flex-end">
                            <a href="#" id="videoPublisherChartToggleViews" class="isset-video-btn btn-primary">Views</a>
                            <a href="#" id="videoPublisherChartToggleData" class="isset-video-btn btn-inactive video-publisher-ml-2">Data</a>
                        </div>
                        <canvas id="videoPublisherViewsChart"></canvas>
                        <canvas style="display: none;" id="videoPublisherDataChart"></canvas>
                    </div>
                    <div id="videoPublisherStreamingViews">
                        <?php foreach ( $stats['views']['totalByDay'] as $view_row ): ?>
                            <span data-key="<?php echo $view_row['date']; ?>" data-value="<?php echo $view_row['views']; ?>" />
                        <?php endforeach; ?>
                    </div>
                    <div id="videoPublisherStreamingData">
                        <?php foreach ( $stats['data']['totalByDay'] as $view_row ): ?>
                            <span data-key="<?php echo $view_row['date']; ?>" data-value="<?php echo $view_row['bytes']; ?>" />
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <a class="isset-video-btn mt-20" href="<?php echo $login_url; ?>">Login to isset.video</a>
    <?php endif; ?>
</div>