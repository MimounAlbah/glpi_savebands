<?php

include ('../../../inc/includes.php');

PluginRtntestalexArchive::canView();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (!isset($_GET["sort"])) {
    $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
    $_GET["order"] = "";
}

$rtnarchive = new PluginRtntestalexArchive();

//Add a new archive
if (isset($_POST["add"])) {
    $rtnarchive->check(-1, CREATE, $_POST);
    if ($newID = $rtnarchive->add($_POST)) {
        Event::log($newID, "rtntestalex", 4, "tools", sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
    }
    Html::back();

// delete a archive
} else if (isset($_POST["delete"])) {
    $rtnarchive->check($_POST['id'], DELETE);
    $ok = $rtnarchive->delete($_POST);
    if ($ok) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
    }
    $rtnarchive->redirectToList();
} else if (isset($_POST["restore"])) {
    $rtnarchive->check($_POST['id'], PURGE);
    if ($rtnarchive->restore($_POST)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s restores the item'), $_SESSION["glpiname"]));
    }
    $rtnarchive->redirectToList();
} else if (isset($_REQUEST["purge"])) {
    $rtnarchive->check($_REQUEST['id'], PURGE);
    if ($rtnarchive->delete($_REQUEST, 1)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
    }
    $rtnarchive->redirectToList();

//update a archive
} else if (isset($_POST["update"])) {
    $rtnarchive->check($_POST['id'], UPDATE);
    $rtnarchive->update($_POST);
    Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
    Html::back();
} else if (isset($_GET["unglobalize"])) {
    $rtnarchive->check($_GET["id"], UPDATE);

    //TODO There is probably a bug here... 
    Html::redirect(Toolbox::getItemTypeFormURL('PluginRtntestalexArchive') . "?id=" . $_GET["id"]);
} else {
    // Affichage du fil d'Ariane
    Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexArchive");
    //show archive form to add
    $rtnarchive->display(array('id' => $_GET["id"]));
    html::footer();
}
