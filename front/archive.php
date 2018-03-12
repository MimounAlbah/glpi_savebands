<?php

include ('../../../inc/includes.php');

// Affichage du fil d'Ariane
Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexArchive");

// TODO Is Checking canCreate useful before showing a list ? 
if (PluginRtntestalexArchive::canView() || PluginRtntestalexArchive::canCreate()) {
    Search::show("PluginRtntestalexArchive");
} else {
    Html::displayRightError();
}

Html::footer();
