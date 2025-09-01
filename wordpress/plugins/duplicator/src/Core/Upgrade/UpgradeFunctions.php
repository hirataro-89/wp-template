<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Upgrade;

use DUP_Settings;
use Duplicator\Utils\Email\EmailSummary;

/**
 * Utility class managing actions when the plugin is updated
 */
class UpgradeFunctions
{
    const LAST_VERSION_EMAIL_SUMMARY_WRONG_KEY = '1.5.6.1';
    const FIRST_VERSION_NEW_STORAGE_POSITION   = '1.3.35';
    const FIRST_VERSION_FOLDER_MIGRATION       = '1.5.14';

    /**
    * This function is executed when the plugin is activated and
    * every time the version saved in the wp_options is different from the plugin version both in upgrade and downgrade.
    *
    * @param false|string $currentVersion current Duplicator version, false if is first installation
    * @param string       $newVersion     new Duplicator Version
    *
    * @return void
    */
    public static function performUpgrade($currentVersion, $newVersion): void
    {
        self::updateStoragePostition($currentVersion);
        self::emailSummaryOptKeyUpdate($currentVersion);
        self::migrateStorageFolders($currentVersion, $newVersion);
    }

    /**
     * Update email summary option key seperator from '-' to '_'
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     *
     * @return void
     */
    private static function emailSummaryOptKeyUpdate($currentVersion): void
    {
        if ($currentVersion == false || version_compare($currentVersion, self::LAST_VERSION_EMAIL_SUMMARY_WRONG_KEY, '>')) {
            return;
        }

        if (($data = get_option(EmailSummary::INFO_OPT_OLD_KEY)) !== false) {
            update_option(EmailSummary::INFO_OPT_KEY, $data);
            delete_option(EmailSummary::INFO_OPT_OLD_KEY);
        }
    }

    /**
     * Update storage position option
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     *
     * @return void
     */
    private static function updateStoragePostition($currentVersion): void
    {
        //PRE 1.3.35
        //Do not update to new wp-content storage till after
        if ($currentVersion !== false && version_compare($currentVersion, self::FIRST_VERSION_NEW_STORAGE_POSITION, '<')) {
            DUP_Settings::Set('storage_position', DUP_Settings::STORAGE_POSITION_LEGACY);
        }
    }

    /**
     * Migrate storage folders from legacy to new location
     *
     * @param false|string $currentVersion current Duplicator version, false if first install
     *
     * @return void
     */
    private static function migrateStorageFolders($currentVersion): void
    {
        // Skip on fresh installs or if already past migration version
        if ($currentVersion === false || version_compare($currentVersion, self::FIRST_VERSION_FOLDER_MIGRATION, '>=')) {
            return;
        }

        // If storage position is already set to new, do not migrate
        if (DUP_Settings::Get('storage_position') === DUP_Settings::STORAGE_POSITION_WP_CONTENT) {
            return;
        }

        // Force using wp-content storage position
        DUP_Settings::setStoragePosition(DUP_Settings::STORAGE_POSITION_WP_CONTENT);
        DUP_Settings::Save();
    }
}
