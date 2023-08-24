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

use Markocupic\ContaoTinymcePluginBuilderBundle\TinymcePluginBuilder;

// Modify TinyMCE templates
if ($GLOBALS['TL_CONFIG']['useRTE']) {
    if (version_compare(VERSION, '4.10.0', '>=')) {
        $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = [TinymcePluginBuilder::class, 'myParseTemplate'];
        $GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = [TinymcePluginBuilder::class, 'myParseTemplate'];
    } else {
        $GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = [TinymcePluginBuilder::class, 'outputTemplate'];
        $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = [TinymcePluginBuilder::class, 'outputTemplate'];
    }
}
