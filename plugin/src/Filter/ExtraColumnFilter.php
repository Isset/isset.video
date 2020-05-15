<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


class ExtraColumnFilter extends BaseFilter {
	public function addColumn( $columns ) {
		$newArray = [];
		// Checkbox always first
		if ( isset( $columns["cb"] ) ) {
			$newArray["cb"] = $columns["cb"];
			unset( $columns["cb"] );
		}

		$newArray["video-publisher-thumbnail"] = _('Thumbnail');
		$newArray["video-publisher-duration"] = _('Duration');
		$newArray["video-publisher-max-resolution"] = _('Max resolution');

		return array_merge( $newArray, $columns );
	}

	public function getFilters(): FilterCollection {
		return ( new FilterCollection() )
			->add( "manage_edit-video-publisher_columns", [ $this, "addColumn" ] );
	}
}