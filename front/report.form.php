<?php

include ('../../../inc/includes.php');

PluginRtntestalexReport::canView();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (!isset($_GET["sort"])) {
    $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
    $_GET["order"] = "";
}

$rtnreport = new PluginRtntestalexReport();

//Add a new report
if (isset($_POST["add"])) {
    $rtnreport->check(-1, CREATE, $_POST);
    if ($newID = $rtnreport->add($_POST)) {
        Event::log($newID, "rtntestalex", 4, "tools", sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
    }
    Html::back();

// delete a report
} else if (isset($_POST["delete"])) {
    $rtnreport->check($_POST['id'], DELETE);
    $ok = $rtnreport->delete($_POST);
    if ($ok) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
    }
    $rtnreport->redirectToList();
} else if (isset($_POST["restore"])) {
    $rtnreport->check($_POST['id'], PURGE);
    if ($rtnreport->restore($_POST)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s restores the item'), $_SESSION["glpiname"]));
    }
    $rtnreport->redirectToList();
} else if (isset($_REQUEST["purge"])) {
    $rtnreport->check($_REQUEST['id'], PURGE);
    if ($rtnreport->delete($_REQUEST, 1)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
    }
    $rtnreport->redirectToList();

//update a report
} else if (isset($_POST["update"])) {
    $rtnreport->check($_POST['id'], UPDATE);
    $rtnreport->update($_POST);
    Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
    Html::back();
} else if (isset($_GET["unglobalize"])) {
    $rtnreport->check($_GET["id"], UPDATE);

    //TODO There is probably a bug here... 
    Html::redirect(Toolbox::getItemTypeFormURL('PluginRtntestalexReport') . "?id=" . $_GET["id"]);
} else {
    // Affichage du fil d'Ariane
    Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexReport");
    //show report form to add
    $rtnreport->display(array('id' => $_GET["id"]));
    html::footer();
}
