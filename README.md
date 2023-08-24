# Contao Tinymce Plugin Builder für Contao 4.4 mit tinyMCE 5
#
Dieses Modul dient als Basis für weitere TinyMCE plugins.

Der Modul modifiziert das javascript des aktuellen tinyMCE Templates.

Die Konfiguration erfolgt im eigenen Package welches diese Extension als Basis benutzt.

Dies geschieht in config.php (bundle/src/Resources/config.php) durch `$GLOBALS`

Beispiel:
```php


    // Add a plugin to the tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['PLUGINS'][] = 'myPlugin';

    // Add a button to the toolbar in tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['TOOLBAR'][] = 'myPlugin';

    // Das Paket muss dafür Sorge tragen, dass der js-Code des Plugins in "assets\tinymce4\js\plugins\myPlugin\plugin.min.js" abgelegt wird.

    // Add a content_css in tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['CONTENT_CSS'][] = 'my_plugin.css';

    // Ein eigener Schlüssel wird durch
    $GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW']['myKey'] = 'myKeyValue';

    // dadurch wird bei der Inititalisierung zusätzlich  myKey: myKeyValue;
    // Die Auswertung von myKey kann im Plugin durch editor getParameter('myKey') erfolgen.
```
