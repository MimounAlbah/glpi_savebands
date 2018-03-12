<?php

include ('../../../inc/includes.php');

// Affichage du fil d'Ariane
Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "pluginrtntestalexmenutools", "PluginRtntestalexReport");

// TODO Is Checking canCreate useful before showing a list ? 
if (PluginRtntestalexReport::canView() || PluginRtntestalexReport::canCreate()) {
    Search::show("PluginRtntestalexReport");
} else {
    Html::displayRightError();
}

Html::footer();
