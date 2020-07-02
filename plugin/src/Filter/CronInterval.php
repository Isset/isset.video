<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


class CronInterval extends BaseFilter {
	/**
	 * @param \Twig\Environment $twig
	 *
	 * @return \Twig\Environment
	 */
	public function registerFunctions( $schedules ) {
		$schedules['five_minutes'] = array(
			'interval' => 300,
			'display'  => esc_html__( 'Every Five minutes' ), );

		return $schedules;
	}

	public function getFilters(): FilterCollection {
		return FilterCollection::new()
		                       ->add( 'cron_schedules', [ $this, 'cronScedules' ] );
	}
}