<?php

include ('../../../inc/includes.php');

// Affichage du fil d'Ariane
Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexTask");

// TODO Is Checking canCreate useful before showing a list ? 
if (PluginRtntestalexTask::canView() || PluginRtntestalexTask::canCreate()) {
    Search::show("PluginRtntestalexTask");
} else {
    Html::displayRightError();
}

Html::footer();
