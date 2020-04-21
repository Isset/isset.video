<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


use Timber\Twig_Function;

class Timber extends BaseFilter {
	/**
	 * @param \Twig\Environment $twig
	 *
	 * @return \Twig\Environment
	 */
	public function registerFunctions( $twig ) {
		$twig->addFunction( new Twig_Function( 'render_admin_header', function () {
			require_once ABSPATH . 'wp-admin/admin-header.php';
		} ) );

		return $twig;
	}

	public function getFilters(): FilterCollection {
		return FilterCollection::new()
		                       ->add( 'timber/twig', [ $this, 'registerFunctions' ] );
	}
}