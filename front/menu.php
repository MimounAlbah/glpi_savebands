<?php

include ('../../../inc/includes.php');

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools");
} else {
    Html::helpHeader(__s('gen_plugin_name', 'rtntestalex'));
}

$menutools = new PluginRtntestalexMenuTools();
$menutools->showMenu();

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::footer();
} else {
    Html::helpFooter();
}
?>