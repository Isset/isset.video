<?php if ( $type === 'text' ): ?>
    <input type="text" id="<?php echo $name; ?>" name="isset-video-publisher-options[<?php echo $name; ?>]" value="<?php echo $value; ?>" class="large-text"/>
<?php elseif ( $type == 'checkbox' ): ?>
    <input type="checkbox" id="<?php echo $name; ?>" name="isset-video-publisher-options[<?php echo $name; ?>]" <?php echo isset($extra['checked']) && $extra['checked'] ? "checked" : ''; ?> value="<?php echo $value; ?>"/> <?php echo isset($extra['postfix']) ? $extra['postfix'] : ''; ?>
<?php else: ?>
    <?php _e( 'Unsupported type', 'isset-video' ); ?>
<?php endif; ?>