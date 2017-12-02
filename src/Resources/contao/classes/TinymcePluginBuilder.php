<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Markocupic\ContaoTinymcePluginBuilderBundle;


/**
 * Provide methods to add plugins to tinymce rte
 *
 *
 * @author Marko Cupic <https://github.com/markocupic>
 */
class TinymcePluginBuilder
{

    /**
     * @var
     */
    protected $strBuffer;

    /**
     * @var string
     */
    protected $regexMatch = '/script\>window.tinymce(.*)setTimeout(.*)window.tinymce(.*)tinymce.init(.*)([\s,\{])%s([\s]*):([\s]*)(["\']{1})(.*)\8(.*)<\/script>/sU';

    /**
     * @var string
     */
    protected $regexReplace = 'script>window.tinymce\1setTimeout\2window.tinymce\3tinymce.init\4\5%s\6:\7\8%s\8\10</script>';



    /**
     * @param $strBuffer
     * @param $strTemplate
     * @return mixed
     */
    public function outputTemplate($strBuffer, $strTemplate)
    {

        $this->strBuffer = $strBuffer;


        if (strpos($this->strBuffer, 'tinymce.init') === false)
        {
            return $this->strBuffer;
        }


        // Extend lines in "tinymce.init({})" with some new content
        $arrKeys = array('plugins', 'toolbar');
        foreach ($arrKeys as $key)
        {
            $regexMatch = sprintf($this->regexMatch, $key);
            if (isset($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)]))
            {
                if (is_array($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)]))
                {
                    // Add key with empty value if it does not exist
                    if (!preg_match($regexMatch, $this->strBuffer, $matches))
                    {
                        // Add empty key
                        $this->addRow($key, "''");

                        // Retest
                        preg_match($regexMatch, $this->strBuffer, $matches);
                    }


                    if (count($matches) > 0)
                    {
                        // $matches[9]: string between single/double quotes (value)
                        if (isset($matches[9]))
                        {
                            $oldValue = $matches[9];
                            $newValue = '';


                            // Plugins
                            if ($key == 'plugins')
                            {
                                // Plugins are separated with whitespaces
                                $aPlugins = preg_split("/[\s]+/", $oldValue);
                                foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $plugin)
                                {
                                    $aPlugins[] = $plugin;
                                }

                                $aPlugins = array_unique($aPlugins);
                                $newValue = trim(implode(' ', $aPlugins));
                            }


                            // Toolbar buttons
                            if ($key == 'toolbar')
                            {
                                $aButtons = explode("|", $oldValue);
                                $aButtons = array_map(function ($item)
                                {
                                    // Remove whitespaces
                                    return trim($item);
                                }, $aButtons);

                                foreach ($GLOBALS['TINYMCE']['SETTINGS'][strtoupper($key)] as $button)
                                {
                                    $aButtons[] = $button;
                                }

                                $aButtons = array_unique($aButtons);
                                $newValue = trim(implode(' | ', $aButtons));
                            }


                            $regexReplace = sprintf($this->regexReplace, $key, $newValue);
                            $this->strBuffer = preg_replace($regexMatch, $regexReplace, $this->strBuffer);
                        }
                    }
                }
            }
        }



        // Add new config rows
        if (isset($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW']))
        {
            if (is_array($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW']))
            {
                foreach ($GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW'] as $key => $row)
                {
                    $this->addRow($key, $row);
                }
            }
        }

        return $this->strBuffer;
    }


    /**
     * Add a new config row to tinymce.init({})
     * @param $key
     * @param $value
     */
    private function addRow($key, $value)
    {
        $tinyMceRowPattern = '/script\>window.tinymce(.*)setTimeout(.*)window.tinymce(.*)tinymce.init(.*)\({(.*)<\/script>/sU';
        if (preg_match($tinyMceRowPattern, $this->strBuffer, $matches))
        {

            if (isset($matches[5]))
            {
                $strRow = "\n\t" . $key . ": " . $value . ",";
                $this->strBuffer = str_replace($matches[5], $strRow . $matches[5], $this->strBuffer);
            }
        }
    }
}

