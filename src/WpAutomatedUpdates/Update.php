<?php

namespace ElevenMiles\WpAutomatedUpdates;

use WP_CLI;
use ElevenMiles\WpAutomatedUpdates\Utility;

class Update
{
    public $ticket;
    public $date;
    public $progressBar;

    public function all($args, $assoc_args)
    {
        if (!isset($assoc_args['ticket'])) WP_CLI::error('Please add "--ticket=EMS-1234" to your command');
        if (!isset($assoc_args['date'])) $assoc_args['date'] = date("d-m-Y");

        $this->ticket = $assoc_args['ticket'];
        $this->date = $assoc_args['date'];

        unset($assoc_args['ticket']);
        unset($assoc_args['date']);

        $this->progressBar = WP_CLI\Utils\make_progress_bar('Updates', Utility::getCountOfThingsToUpdate());

        self::wordpress($args, array_merge($assoc_args, ['force' => true]));
        self::plugins($args, array_merge($assoc_args, ['force' => true]));
        // self::themes($args, array_merge($assoc_args, ['force' => true]));
        // self::translations($args, array_merge($assoc_args, ['force' => true]));

        $this->progressBar->finish();
    }

    // Usage: wp update wordpress
    public function wordpress($args, $assoc_args)
    {
        global $wp_version;
        $nextVersion = Utility::getLatestWordpressVersion();

        if (!$nextVersion) {
            WP_CLI::log('No new update for Wordpress at this time');
            return;
        }

        $force = $assoc_args['force'] ?? false;

        WP_CLI::log(sprintf('Update available, Wordpress will be update from %s to %s', $wp_version, $nextVersion));
        if (!$force) WP_CLI::confirm('Ok to continue?', $assoc_args);

        Utility::updateWordpress(array_merge($assoc_args, ['version' => $nextVersion]), $this->ticket, $this->date);
    }

    // Usage: wp example subcommand
    public function plugins($args, $assoc_args)
    {
        $pluginsToUpdate = Utility::getPluginsToUpdate();

        if (count($pluginsToUpdate) === 0) {
            WP_CLI::log('No plugins to update;');
            return;
        }

        $force = $assoc_args['force'] ?? false;

        WP_CLI::log(sprintf('%d plugins to update', (int)count($pluginsToUpdate)));
        if (!$force) WP_CLI::confirm('Ok to continue?', $assoc_args);

        foreach ($pluginsToUpdate as $plugin) Utility::updatePlugin($plugin['name'], $plugin['version'], $this->ticket, $this->date);
    }

    // Usage: wp example subcommand
    public function themes()
    {
        WP_CLI::log(sprintf('You have run a subcommand'));
    }

    // Usage: wp example subcommand
    public function translations()
    {
        WP_CLI::log(sprintf('You have run a subcommand'));
    }
}
