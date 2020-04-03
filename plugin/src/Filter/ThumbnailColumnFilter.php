<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


class ThumbnailColumnFilter extends BaseFilter {
	public function addColumn( $columns ) {
		$newArray = [];
		// Checkbox always first
		if ( isset( $columns["cb"] ) ) {
			$newArray["cb"] = $columns["cb"];
			unset( $columns["cb"] );
		}

		$newArray["video-publisher-thumbnail"] = "";

		return array_merge( $newArray, $columns );
	}

	public function getFilters(): FilterCollection {
		return ( new FilterCollection() )
			->add( "manage_edit-video-publisher_columns", [ $this, "addColumn" ] );
	}
}