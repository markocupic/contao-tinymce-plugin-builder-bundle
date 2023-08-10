# Contao Tinymce Plugin Builder für Contao 4.4 mit tinyMCE 5
# 
Dieses Modul dient als Basis für weitere tinymce plugins.

Der Modul modifiziert das js des aktuellen Templates von tinyMCE

Die Konfiguration erfolgt in dem Paket das diese Basis verwendet.

Dies geschieht in config.php (bundle\src\Resources\config.php) durch $GLOBALS

Beispiel:
    // Add a plugin to the tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['PLUGINS'][] = 'myPlugin';

    // Add a button to the toolbar in tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['TOOLBAR'][] = 'myPlugin';
    
    das Paket muss dafür Sorge tragen, dass der js-Code des Plugins
    in \assets\tinymce4\js\plugins\myPlugin\plugin.min.js
    abgelegt wird.


    // Add a content_css in tinymce editor
    $GLOBALS['TINYMCE']['SETTINGS']['CONTENT_CSS'][] = 'anton.css';
    
    ein eigener Schluessel wird durch
    $GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW']['myKey'] = 'myKeyValue';
    daurch wird bei der Inititalisierung zusaetzlich  myKey: myKeyValue; 
    Die Auswertung von myKey kann im Plugin durch editor getParameter('myKey') erfolgen.

