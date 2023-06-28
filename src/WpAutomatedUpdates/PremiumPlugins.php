<?php

namespace ElevenMiles\WpAutomatedUpdates;

use WP_CLI;

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

        WP_CLI::runcommand("plugin install https://connect.advancedcustomfields.com/index.php?p=pro&a=download&k={$key}&#8221");
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
    public static function GravityForms($currentVersion, $ticket, $date){
        $key = getenv('GF_KEY');
        
        if (!$key) {
            WP_CLI::log('Missing Gravity Forms license key.');
            return;
        }

        WP_CLI::runcommand("gf update --key={$key}");
        Utility::afterUpdatePlugin('gravityforms', $currentVersion, $ticket, $date);
    }
}