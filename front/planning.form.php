<?php

include ('../../../inc/includes.php');

Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexPlanning");

$planing = new PluginRtntestalexPlanning();

if(!isset($datecherche))
  $datecherche = null;
if(isset($_POST['reserve']))
  $datecherche = $_POST['reserve'];

$planing->showFormDate();
$planing->showCurrentPlanning();
Html::footer();

?>
