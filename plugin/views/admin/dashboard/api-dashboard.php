<?php if ( $isLoggedIn ) : ?>
	<div class="video-publisher-flex" style="flex-direction: row; justify-content: space-between">
		<div class="video-publisher-flex-1 video-publisher-flex isset-video-stats-container" style="">
            <a target="_blank" href="<?php echo $isset_video_url; ?>" class="mb-20">
                <img src="<?php echo ISSET_VIDEO_PUBLISHER_URL . '/assets/isset.video.png'; ?>" class="isset-video-logo" />
            </a>
			<?php if ( $stats ) : ?>
				<div class="video-publisher-flex-1">
					<div class="video-publisher-mt-2">
						<span class="video-publisher-bold"><?php _e( 'Storage used', 'isset-video' ); ?>: </span>
                        <?php if ($subscription_limit['storage_limit']): ?>
						    <span><?php echo round( $usage['storage'] / 1e+9, 3 ); ?> GB / <?php echo round( $subscription_limit['storage_limit'] / 1e+9, 3 ); ?> GB</span>
                            <div class="isset-video-progressbar">
                                <div class="isset-video-progressbar-progress" style="width: <?php echo calc_percentage( $usage['storage'], $subscription_limit['storage_limit'] ); ?>%;"></div>
                            </div>
                        <?php else: ?>
                            <span><?php echo round( $usage['storage'] / 1e+9, 3 ); ?> GB</span>
                        <?php endif; ?>
					</div>
					<div>
						<span class="video-publisher-bold"><?php _e( 'Data streamed', 'isset-video' ); ?>: </span>
						<span><?php echo round( $usage['streaming'] / 1e+9, 3 ); ?> GB <?php echo $subscription_limit['streaming_limit'] ? '/ ' . round( $subscription_limit['streaming_limit'] / 1e+9, 3 ) . ' GB' : ''; ?></span>
						<?php if ( $subscription_limit['streaming_limit'] ) : ?>
							<div class="isset-video-progressbar">
								<div class="isset-video-progressbar-progress" style="width: <?php echo calc_percentage( $usage['streaming'], $subscription_limit['streaming_limit'] ); ?>%;"></div>
							</div>
						<?php endif; ?>
					</div>
					<div class="video-publisher-mt-2">
						<span class="video-publisher-bold"><?php _e( 'Logged in as', 'isset-video' ); ?>: </span>
						<span><?php echo $user['username']; ?>
							<a class="isset-video-a-icon" href="<?php echo $logout_url; ?>" title="<?php _e( 'Logout', 'isset-video' ); ?>">
								<span class="dashicons dashicons-migrate"></span>
							</a>
						</span>
					</div>
					<div>
						<span class="video-publisher-bold"><?php _e( 'Subscription type', 'isset-video' ); ?>: </span>
						<span>
							<a target="_blank" href="<?php echo $isset_video_url; ?>subscriptions">
								<?php echo ucwords( $subscription_limit['name'] ); ?>
				            </a>
						</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $stats ) : ?>
			<div class="video-publisher-flex-3">
				<div class="">
					<div class="video-publisher-mb-2 video-publisher-flex video-publisher-flex-end">
						<a href="#" id="videoPublisherChartToggleViews" class="isset-video-btn btn-primary"><?php _e( 'Views', 'isset-video' ); ?></a>
						<a href="#" id="videoPublisherChartToggleData" class="isset-video-btn btn-inactive video-publisher-ml-2"><?php _e( 'Data', 'isset-video' ); ?></a>
					</div>
					<div style="position: relative; height: 200px; overflow: hidden">
						<canvas style="position: relative; height: 200px; width: 100%;" id="videoPublisherViewsChart"></canvas>
						<canvas style="position: relative; height: 200px; width: 100%;" id="videoPublisherDataChart"></canvas>
					</div>
				</div>
				<div id="videoPublisherStreamingViews">
                    <?php if ( isset( $stats['views'] ) ): ?>
                        <?php foreach ( $stats['views'] as $view_row ) : ?>
                            <span data-key="<?php echo $view_row['date']; ?>" data-value="<?php echo $view_row['views']; ?>"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
				</div>
				<div id="videoPublisherStreamingData">
                    <?php if ( isset( $stats['data'] ) ): ?>
                        <?php foreach ( $stats['data'] as $view_row ) : ?>
                            <span data-key="<?php echo $view_row['date']; ?>" data-value="<?php echo $view_row['bytes']; ?>"></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php else : ?>
	<a class="isset-video-btn mt-20" href="<?php echo $login_url; ?>"><?php _e( 'Login to isset.video', 'isset-video' ); ?></a>
<?php endif; ?>
