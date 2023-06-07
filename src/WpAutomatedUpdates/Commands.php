<?php
namespace ElevenMiles\WpAutomatedUpdates;

use WP_CLI;

class Commands {
    function __construct() {
        $this->registerCustomCommands();
    }

    function registerCustomCommands () {
        WP_CLI::add_command( 'update',  'ElevenMiles\WpAutomatedUpdates\Update');
    }
}