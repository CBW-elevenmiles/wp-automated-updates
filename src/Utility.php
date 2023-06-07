<?php

namespace Trethowans\CLI;

use WP_CLI;
use WP_CLI\Utils;
use Composer\Semver\Comparator;

class Utility
{
    public $ticket;
    public $date;
    public $progressBar;

    public function getLatestWordpressVersion()
    {
        wp_version_check();
        $from_api = get_site_transient('update_core');
        if (!$from_api) {
            return [];
        }

        $compare_version = str_replace('-src', '', $GLOBALS['wp_version']);

        $updates = [
            'major' => false,
            'minor' => false,
        ];

        foreach ($from_api->updates as $offer) {

            $update_type = Utils\get_named_sem_ver($offer->version, $compare_version);
            if (!$update_type) continue;

            // WordPress follow its own versioning which is roughly equivalent to semver
            if ('minor' === $update_type) {
                $update_type = 'major';
            } elseif ('patch' === $update_type) {
                $update_type = 'minor';
            }

            if (!empty($updates[$update_type]) && !Comparator::greaterThan($offer->version, $updates[$update_type]['version'])) continue;

            $updates[$update_type] = ['version' => $offer->version];
        }

        foreach ($updates as $type => $value) if (empty($value)) unset($updates[$type]);

        $updates = array_reverse(array_values($updates));

        return isset($updates[0]['version']) ? $updates[0]['version'] : false;
    }

    public function getPlugins()
    {
        $options = [
            'return'     => true,
            'parse'      => 'json',
            'launch'     => false,
            'exit_error' => true,
        ];

        return WP_CLI::runcommand('plugin list --format=json', $options);
    }

    public function getPluginsToUpdate()
    {
        return [...array_filter(self::getPlugins(), fn ($plugin) => $plugin['update'] == 'available')];
    }

    public function getPluginVersion($name) {
        $plugins = array_filter(self::getPlugins(), fn($plugin) => $plugin['name'] === $name);

        if (count($plugins) === 1) return $plugins[0]['version'];

        return false;
    }

    public function getCountOfThingsToUpdate()
    {
        global $wp_version;
        $wordpressNeedsUpdate = $wp_version !== $this->getLatestWordpressVersion() ? 1 : 0;
        $pluginsToUpdate = count($this->getPluginsToUpdate());

        return $wordpressNeedsUpdate + $pluginsToUpdate;
    }

    public function updateWordpress($assoc_args = [])
    {
        $args = join(' ', array_reduce(array_keys($assoc_args), function ($output, $key) use ($assoc_args) {
            array_push($output, "--{$key}={$assoc_args[$key]}");

            return $output;
        }, []));

        WP_CLI::runcommand(sprintf('core update %s', $args));

        $wp_details = self::get_wp_details();

        self::commitToGit('Wordpress', $assoc_args['version'], $wp_details['wp_version']);
        if ($this->progressBar) $this->progressBar->tick();
    }

    public function updatePlugin($name, $currentVersion)
    {
        WP_CLI::runcommand("plugin update {$name}");
        $newVersion = self::getPluginVersion($name);
        self::commitToGit($name, $currentVersion, $newVersion);
        if ($this->progressBar) $this->progressBar->tick();
    }

    public function commitToGit($name, $version, $newVersion)
    {
        $emoji = false;

        switch (true) {
            case $version === null:
                $emoji = "heavy_plus_sign";
                break;
            case $newVersion === null:
                $emoji = "heavy_minus_sign";
                break;
            case $version === $newVersion:
                $emoji = "arrow_up";
                break;
            case $version > $newVersion:
                $emoji = "arrow_up";
                break;
            case $version < $newVersion:
                $emoji = "arrow_down";
                break;
        }

        if ($emoji) ':' . $emoji . ':';

        shell_exec("git commit -am '{$this->ticket}: :package: {$emoji} {$name} ({$this->date})'");
    }

    public static function get_wp_details($abspath = ABSPATH)
    {
        $versions_path = $abspath . 'wp-includes/version.php';

        if (!is_readable($versions_path)) {
            WP_CLI::error(
                "This does not seem to be a WordPress installation.\n" .
                    'Pass --path=`path/to/wordpress` or run `wp core download`.'
            );
        }

        $version_content = file_get_contents($versions_path, null, null, 6, 2048);

        $vars   = ['wp_version', 'wp_db_version', 'tinymce_version', 'wp_local_package'];
        $result = [];

        foreach ($vars as $var_name) {
            $result[$var_name] = self::find_var($var_name, $version_content);
        }

        return $result;
    }

    public static function find_var($var_name, $code)
    {
        $start = strpos($code, '$' . $var_name . ' = ');

        if (!$start) {
            return null;
        }

        $start = $start + strlen($var_name) + 3;
        $end   = strpos($code, ';', $start);

        $value = substr($code, $start, $end - $start);

        return trim($value, " '");
    }
}
