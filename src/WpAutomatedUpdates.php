<?php
namespace Em11\WpAutomatedUpdates;

use WP_CLI;

class Commands {
    function __construct() {
        $this->registerCustomCommands();
    }

    function registerCustomCommands () {
        WP_CLI::add_command( 'update',  'Trethowans\CLI\Update');
    }
}