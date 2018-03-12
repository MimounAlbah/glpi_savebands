<?php

include ('../../../inc/includes.php');

Profile::canView();

$profile = new PluginRtntestalexProfile();

//Save profile
if (isset($_POST['update'])) {
    $profile->update($_POST);
}
Html::back();
