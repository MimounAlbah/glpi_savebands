<?php

include ('../../../inc/includes.php');

PluginRtntestalexReport::canUpdate();

$rtnreport_item = new PluginRtntestalexReport_Item();
if (isset($_POST["additem"])) {
    $rtnreport_item->can(-1, CREATE, $_POST);
    if ($newID = $rtnreport_item->add($_POST)) {
        
    }
} else if (isset($_POST["delete_items"])) {
    if (isset($_POST['todelete'])) {
        foreach ($_POST['todelete'] as $id => $val) {
            if ($val == 'on') {
                $rtnreport_item->can($id, DELETE, $_POST);
                $ok = $rtnreport_item->delete(array('id' => $id));
            }
        }
    }
}
Html::back();
