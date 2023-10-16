<?php

declare(strict_types=1);

/*
 * This file is part of Contao TinyMCE Plugin Builder Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-tinymce-plugin-builder-bundle
 */

namespace Markocupic\ContaoTinymcePluginBuilderBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

/**
 * Add plugins to tinymce rte.
 */
class ParseTemplateListener
{
    protected const REGEX_MATCH = '/script\>window.tinymce(.*)window.tinymce(.*)tinymce.init(.*)([\s,\{])%s([\s]*):([\s]*)(["\']{1})(.*)\7(.*)<\/script>/sU';
    protected const REGEX_REPLACE = 'script>window.tinymce\1window.tinymce\2tinymce.init\3\4%s:\7%s\7\9</script>';
    protected string $strBuffer;

    /**
     * Modify template for tinyMCE 5.
     */
    #[AsHook('parseBackendTemplate', priority: 100)]
    #[AsHook('parseFrontendTemplate', priority: 100)]
    public function __invoke(string $strBuffer, string $strTemplateName): string
    {
        if (!str_contains($strTemplateName, 'be_tinyMCE')) {
            return $strBuffer;
        }

        $this->strBuffer = $strBuffer;

        $arrKeys = ['plugins', 'toolbar', 'content_css','extended_valid_elements'];

        foreach ($arrKeys as $key) {
            $regexMatch = sprintf(self::REGEX_MATCH, $key);

            if (isset($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)])) {
                if (\is_array($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)])) {
                    // Add key with empty value if it does not exist
                    if (!preg_match($regexMatch, $this->strBuffer, $matches)) {
                        // Add empty key
                        $this->addRow($key, "''");
                        // Retest
                        preg_match($regexMatch, $this->strBuffer, $matches);
                    }

                    if (isset($matches[8])) {
                        $oldValue = $matches[8];

                        $newValue = '';

                        // Plugins
                        if ('plugins' === $key) {
                            // Plugins are separated with whitespaces
                            $aPlugins = preg_split('/[\\s]+/', $oldValue);

                            foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $plugin) {
                                $aPlugins[] = $plugin;
                            }

                            $aPlugins = array_unique($aPlugins);
                            $newValue = trim(implode(' ', $aPlugins));
                        }

                        // Toolbar buttons
                        if ('toolbar' === $key) {
                            $aButtons = explode('|', $oldValue);
                            $aButtons = array_map(
                                static function ($item) {
                                    // Remove whitespaces
                                    return trim($item);
                                },
                                $aButtons
                            );

                            foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $button) {
                                $aButtons[] = $button;
                            }

                            $aButtons = array_unique($aButtons);
                            $newValue = trim(implode(' | ', $aButtons));
                        }

                        // content_css
                        //if ('content_css' === $key) {
                        // content_css extended_valid_elements  komma separated
                        if ('content_css' === $key || 'extended_valid_elements' === $key) {
                            // Plugins are separated by commas
                            $aPlugins = preg_split('/\\s*,\\s*/', $oldValue);

                            foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $plugin) {
                                $aPlugins[] = trim($plugin);
                            }

                            $aPlugins = array_unique($aPlugins);
                            $newValue = trim(implode(',', $aPlugins));
                        }
                        $regexReplace = sprintf(self::REGEX_REPLACE, $key, $newValue);

                        $this->strBuffer = preg_replace($regexMatch, $regexReplace, $this->strBuffer);
                    }
                }
            }
        }

        // Add new config rows
        if (isset($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'])) {
            if (\is_array($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'])) {
                foreach ($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'] as $key => $row) {
                    $this->addRow($key, $row);
                }
            }
        }

        return $this->strBuffer;
    }

    /**
     * Add a new config row to tinymce.init({}) V5.
     */
    private function addRow(string $key, string $value): void
    {
        $tinyMceRowPattern = '/script\>window.tinymce(.*)window.tinymce(.*)tinymce.init(.*)\({(.*)<\/script>/sU';

        if (preg_match($tinyMceRowPattern, $this->strBuffer, $matches)) {
            if (isset($matches[4])) {
                $strRow = "\n\t".$key.': '.$value.',';
                $this->strBuffer = str_replace($matches[4], $strRow.$matches[4], $this->strBuffer);
            }
        }
    }
}
