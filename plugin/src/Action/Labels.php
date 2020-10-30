<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


class Labels extends BaseAction
{

    function execute($arguments)
    {
        load_plugin_textdomain( 'rad-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }


    function getAction()
    {
        return 'plugins_loaded';
    }
}