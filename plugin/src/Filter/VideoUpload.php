<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
use Timber\Timber;

class VideoUpload extends BaseFilter {
	/**
	 * @param bool $_
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	public function shouldReplaceEditor( $_, $post ) {
		$service = new VideoPublisherService($this->plugin);

		if ( $post->post_type !== VideoPublisher::getTypeName() ) {
			return $_;
		}

		if ( $post->post_status === 'auto-draft' ) {
			$context           = Timber::context();
			$context['screen'] = get_current_screen();
			$context['uploading_allowed'] = $service->uploadingAllowed();
            $context['video_url'] = admin_url( 'edit.php?post_type=' . urlencode( VideoPublisher::getTypeName() ) );

			Timber::render( __DIR__ . '/../../views/admin/upload.html.twig', $context );

			return true;
		}

		return false;
	}

	/**
	 * @param bool $_
	 * @param \WP_Screen $screen
	 *
	 * @return bool
	 */
	public function hideScreenOptions( $_, $screen ) {
	    if ( $screen->post_type !== VideoPublisher::getTypeName() ) {
		    return $_;
		}

		$this->plugin->enqueueScript( 'js/admin-video-upload.js' );
		$this->plugin->enqueueStyle( 'css/admin-video-upload.css' );

		// Only hide screen options for new post page
		if ( $screen->parent_base === 'edit' && $screen->action !== 'add' ) {
			return $_;
		}

		return false;
	}

	public function getFilters(): FilterCollection {
		return FilterCollection::new()
		                       ->add( "replace_editor", [ $this, 'shouldReplaceEditor' ], 10, 2 )
		                       ->add( 'screen_options_show_screen', [ $this, 'hideScreenOptions' ], 10, 2 );
	}
}