<?php require_once( ABSPATH . 'wp-admin/admin-header.php' ); ?>

<div class="card isset-video-upload-card">
    <div class="card-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-cloud-upload"></span> <?php _e("Upload new video"); ?>
        </h1>
    </div>
    <div class="card-body">
        <?php if ( $uploading_allowed ): ?>
            <div class="upload-container">
                <div class="phase-select">
                    <div class="phase-select-dropzone">
                        <input multiple accept="video/mp4,video/x-m4v,video/*" type="file">
                        <div class="text-wrapper">
                            <span class="dashicons dashicons-download download-icon"></span>
                        </div>
                        <div class="text-wrapper">
                            <p>Drop one or more files here (4.3GB max) or <u><b>browse</b></u> your computer.</p>
                        </div>
                    </div>
                    <button class="btn">Upload</button>
                    <div class="selected-files-container" id="phase-select-file"></div>
                </div>
                <div class="phase-upload">
                    <button id="btnCancelUpload" class="btn btn-danger">Cancel upload</button>
                </div>
                <div class="phase-done">

                </div>
            </div>
        <?php else: ?>
            <div class="text-wrapper">
                <p>
                    Storage limit is already reached, please remove video's to upload again or upgrade your subscription
                    <a href="https://my.isset.video/subscriptions">https://my.isset.video/subscriptions</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer" style="display: none;">
        <a id="videoPublisherSyncVideosAfterUpload" class="btn" href="<?php echo $video_url; ?>"><?php _e("Go to videos", 'isset-video-publisher'); ?></a>
    </div>
</div>
<canvas width=32 height=32 style="display: none;"></canvas>