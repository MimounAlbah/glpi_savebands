<?php

include ('../../../inc/includes.php');

PluginRtntestalexTask::canUpdate();

$rtntask_item = new PluginRtntestalexTask_Item();
if (isset($_POST["additem"])) {
    $rtntask_item->can(-1, CREATE, $_POST);
    if ($newID = $rtntask_item->add($_POST)) {
        
    }
} else if (isset($_POST["delete_items"])) {
    if (isset($_POST['todelete'])) {
        foreach ($_POST['todelete'] as $id => $val) {
            if ($val == 'on') {
                $rtntask_item->can($id, DELETE, $_POST);
                $ok = $rtntask_item->delete(array('id' => $id));
            }
        }
    }
}
Html::back();
