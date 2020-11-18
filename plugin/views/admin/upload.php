<?php require_once( ABSPATH . 'wp-admin/admin-header.php' ); ?>

<div class="card isset-video-upload-card">
    <div class="card-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-cloud-upload"></span> <?php _e( 'Upload new video', 'isset-video-publisher' ); ?>
        </h1>
    </div>
    <div class="card-body">
        <?php if ( $uploading_allowed ): ?>
            <div class="upload-container">
                <div class="phase-select">
                    <div class="phase-select-dropzone">
                        <input multiple accept="video/mp4,video/x-m4v,video/x-flv,video/*,.mkv,.ts" type="file">
                        <div class="selected-files-container video-publisher-p-2" id="phase-select-file">
                            <div class="text-wrapper">
                                <span class="dashicons dashicons-download download-icon"></span>
                            </div>
                            <div class="text-wrapper">
                                <p><?php _e( 'Drop one or more files here (4.3GB max) or browse your computer.', 'isset-video-publisher' ); ?></p>
                            </div>
                        </div>
                    </div>
                    <button class="isset-video-btn"><?php _e( 'Upload', 'isset-video-publisher' ); ?></button>
                </div>
                <div class="phase-upload">
                    <button id="btnCancelUpload" class="isset-video-btn btn-danger"><?php _e( 'Cancel upload', 'isset-video-publisher' ); ?></button>
                </div>
                <div class="phase-done">

                </div>
            </div>
        <?php else: ?>
            <div class="text-wrapper">
                <p>
                    <?php _e( "Storage limit is already reached, please remove video's to upload again or upgrade your subscription", 'isset-video-publisher' ); ?>
                    <a href="https://my.isset.video/subscriptions">https://my.isset.video/subscriptions</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer" style="display: none;">
        <a id="videoPublisherSyncVideosAfterUpload" class="isset-video-btn" href="<?php echo $video_url; ?>"><?php _e( 'Go to videos', 'isset-video-publisher' ); ?></a>
    </div>
</div>
<canvas width=32 height=32 style="display: none;"></canvas>