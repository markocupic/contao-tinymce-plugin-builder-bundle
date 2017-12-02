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
    // Modify tinymce.init method in template before sending the output to the browser
    $GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'outputTemplate');
    $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder', 'outputTemplate');
}
