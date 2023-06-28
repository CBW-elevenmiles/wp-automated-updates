<?php

namespace ElevenMiles\WpAutomatedUpdates;

use WP_CLI;

/**
 * PremiumPlugins class.
 * 
 * 
 * @package    ElevenMiles\WpAutomatedUpdates
 * @subpackage ElevenMiles\WpAutomatedUpdates\PremiumPlugins
 * @since      1.0.0
 */
class PremiumPlugins {
    /**
     *
     * Update Advanced Custom Fields Pro
     *
     * 
     * @return string|null
     *
     */
    public static function AdvancedCustomFieldsPro($currentVersion, $ticket, $date){
        $key = getenv('ACFPRO_KEY');
        
        if (!$key) {
            WP_CLI::log('Missing Advanced Custom Fields Pro license key.');
            return;
        }

        Utility::runCommand("plugin install https://connect.advancedcustomfields.com/index.php?p=pro&a=download&k={$key}&#8221 --force");
        Utility::afterUpdatePlugin('advanced-custom-fields-pro', $currentVersion, $ticket, $date);
    }

    /**
     *
     * Update Gravity Forms
     *
     * 
     * @return string|null
     *
     */
    public static function GravityForms($name, $currentVersion, $ticket, $date){
        if (!\is_plugin_active('gravityformscli')) return;

        $key = getenv('GF_KEY');
        
        if (!$key) {
            WP_CLI::log('Missing Gravity Forms license key.');
            return;
        }
        
        $command = "gf update {$name} --key={$key}";

        if ($name === 'gravityforms') $command = "gf update --key={$key}";

        Utility::runCommand($command);
        Utility::afterUpdatePlugin('gravityforms', $currentVersion, $ticket, $date);
    }
}