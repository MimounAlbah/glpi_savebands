<?php

include ('../../../inc/includes.php');

PluginRtntestalexArchive::canUpdate();

$rtnarchive_item = new PluginRtntestalexArchive_Item();
if (isset($_POST["additem"])) {//ajout
    $rtnarchive_item->can(-1, CREATE, $_POST);
    if ($newID = $rtnarchive_item->add($_POST)) {
        
    }
} else if (isset($_POST["delete_items"])) {//suppression
    if (isset($_POST['todelete'])) {
        foreach ($_POST['todelete'] as $id => $val) {
            if ($val == 'on') {
                $rtnarchive_item->can($id, DELETE, $_POST);
                $ok = $rtnarchive_item->delete(array('id' => $id));
            }
        }
    }
}
Html::back();
