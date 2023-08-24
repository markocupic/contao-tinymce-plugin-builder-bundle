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

namespace Markocupic\ContaoTinymcePluginBuilderBundle;

use Contao\System;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Provide methods to add plugins to tinymce rte.
 */
class TinymcePluginBuilder
{
    protected const REGEX_MATCH = '/script\>window.tinymce(.*)setTimeout(.*)window.tinymce(.*)tinymce.init(.*)([\s,\{])%s([\s]*):([\s]*)(["\']{1})(.*)\8(.*)<\/script>/sU';
    protected const REGEX_MATCH_49 = '/script\>window.tinymce(.*)window.tinymce(.*)tinymce.init(.*)([\s,\{])%s([\s]*):([\s]*)(["\']{1})(.*)\7(.*)<\/script>/sU';
    protected const REGEX_REPLACE = 'script>window.tinymce\1setTimeout\2window.tinymce\3tinymce.init\4\5%s\6:\7\8%s\8\10</script>';
    protected const REGEX_REPLACE_49 = 'script>window.tinymce\1window.tinymce\2tinymce.init\3\4%s:\7%s\7\9</script>';

    protected string $strBuffer;
    protected ?LoggerInterface $customLogger = null;

    public function __construct()
    {
        $container = System::getContainer();

        if ($container->getParameter('kernel.debug')) {
            // With debug mode enabled
            $logPath = $container->getParameter('kernel.project_dir').'/var/logs/TinymcePluginBuilder.log';
            $this->customLogger = $container->get('monolog.logger.contao');
            $streamHandler = new StreamHandler($logPath, Logger::DEBUG);
            $this->customLogger->pushHandler($streamHandler);
        }
    }

    public function outputTemplate(string $strBuffer, string $strTemplate): string
    {
        $this->strBuffer = $strBuffer;

        if (false === strpos($this->strBuffer, 'tinymce.init')) {
            return $this->strBuffer;
        }

        // Extend lines in "tinymce.init({})" with some new content
        $arrKeys = ['plugins', 'toolbar'];

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

                    if (\count($matches) > 0) {
                        // $matches[9]: string between single/double quotes (value)
                        if (isset($matches[9])) {
                            $oldValue = $matches[9];
                            $newValue = '';
                            // Plugins
                            /** @noinspection DuplicatedCode */
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

                            $regexReplace = sprintf(self::REGEX_REPLACE, $key, $newValue);
                            $this->strBuffer = preg_replace($regexMatch, $regexReplace, $this->strBuffer);
                        }
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
     * Modify template for tinyMCE 5.
     */
    public function myParseTemplate(string $strTemplate, string $strTemplateName): string
    {
        if (false === strpos($strTemplateName, 'be_tinyMCE')) {
            return $strTemplate;
        }

        $this->debugMe('PBD Template Name '.$strTemplateName);
        $this->strBuffer = $strTemplate;

        $arrKeys = ['plugins', 'toolbar', 'content_css'];

        foreach ($arrKeys as $key) {
            $regexMatch = sprintf(self::REGEX_MATCH_49, $key);
            $this->debugMe('PBD plugin tiny Builder key: '.$key);

            if (isset($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)])) {
                if (\is_array($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)])) {
                    // Add key with empty value if it does not exist
                    if (!preg_match($regexMatch, $this->strBuffer, $matches)) {
                        // Add empty key
                        $this->addRow49($key, "''");
                        // Retest
                        preg_match($regexMatch, $this->strBuffer, $matches);
                    }

                    if (isset($matches[8])) {
                        $oldValue = $matches[8];
                        $newValue = '';
                        $this->debugMe('PBD plugin tiny Builder: [8] oldValue: '.$oldValue);

                        // Plugins
                        /** @noinspection DuplicatedCode */
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
                        if ('content_css' === $key) {
                            // Plugins are separated by commas
                            $aPlugins = preg_split('/\\s*,\\s*/', $oldValue);

                            foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $plugin) {
                                $this->debugMe("PBD plugin add content_css key: $key plugin: $plugin");
                                $aPlugins[] = trim($plugin);
                            }

                            $aPlugins = array_unique($aPlugins);
                            $newValue = trim(implode(',', $aPlugins));
                        }
                        $this->debugMe("PBD plugin tiny Builder key: $key oldValue: $oldValue newValue $newValue");
                        $regexReplace = sprintf(self::REGEX_REPLACE_49, $key, $newValue);

                        $this->strBuffer = preg_replace($regexMatch, $regexReplace, $this->strBuffer);
                    } else {
                        $this->debugMe('PBD plugin tiny Builder: matches[8] nicht vorhanden');
                    }
                }
            }
        }

        // Add new config rows
        if (isset($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'])) {
            if (\is_array($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'])) {
                foreach ($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'] as $key => $row) {
                    $this->addRow49($key, $row);
                }
            }
        }
        $this->debugMe('PBD plugin tiny Builder: return: '.$this->strBuffer);

        return $this->strBuffer;
    }

    /**
     * Add a new config row to tinymce.init({}).
     */
    private function addRow(string $key, string $value): void
    {
        $tinyMceRowPattern = '/script\>window.tinymce(.*)setTimeout(.*)window.tinymce(.*)tinymce.init(.*)\({(.*)<\/script>/sU';

        if (preg_match($tinyMceRowPattern, $this->strBuffer, $matches)) {
            if (isset($matches[5])) {
                $strRow = "\n\t".$key.': '.$value.',';
                $this->strBuffer = str_replace($matches[5], $strRow.$matches[5], $this->strBuffer);
            }
        }
    }

    /**
     * Add a new config row to tinymce.init({}) V5.
     */
    private function addRow49(string $key, string $value): void
    {
        $this->debugMe('PBD plugin tiny Builder addRow2 key: '.$key.' val '.$value);
        $tinyMceRowPattern49 = '/script\>window.tinymce(.*)window.tinymce(.*)tinymce.init(.*)\({(.*)<\/script>/sU';

        if (preg_match($tinyMceRowPattern49, $this->strBuffer, $matches)) {
            if (isset($matches[4])) {
                $strRow = "\n\t".$key.': '.$value.',';
                $this->strBuffer = str_replace($matches[4], $strRow.$matches[4], $this->strBuffer);
            }
        }
    }

    /**
     * Write debug line if debug mode is enabled.
     */
    private function debugMe($strDebug): void
    {
        if ($this->customLogger) {
            $this->customLogger->debug($strDebug);
        }
    }
}
