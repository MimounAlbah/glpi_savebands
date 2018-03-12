<?php

include ('../../../inc/includes.php');

// Affichage du fil d'Ariane
Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexCartridge");

// TODO Is Checking canCreate useful before showing a list ? 
if (PluginRtntestalexCartridge::canView() || PluginRtntestalexCartridge::canCreate()) {
    Search::show("PluginRtntestalexCartridge");
} else {
    Html::displayRightError();
}

Html::footer();
