<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

if ($GLOBALS['TL_CONFIG']['useRTE'])
{
    if ( \version_compare(VERSION,'4.10.0','>=') ) {    // template modifizieren tinymcs 5
        $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'myParseTemplate');
        $GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'myParseTemplate');
    } else {
        $GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'outputTemplate');
        $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'outputTemplate');
    }
  
}
