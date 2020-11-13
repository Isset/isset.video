<?php

function calc_percentage( $amount, $total ) {
	$percentage = $amount * 100 / $total;

	return $percentage > 100 ? 100 : $percentage;
}
