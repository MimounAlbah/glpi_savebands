<?php

include ('../../../inc/includes.php');

PluginRtntestalexTask::canView();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (!isset($_GET["sort"])) {
    $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
    $_GET["order"] = "";
}

$rtntask = new PluginRtntestalexTask();

//Add a new task
if (isset($_POST["add"])) {
    $rtntask->check(-1, CREATE, $_POST);
    if ($newID = $rtntask->add($_POST)) {
        Event::log($newID, "rtntestalex", 4, "tools", sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
    }
    Html::back();

// delete a task
} else if (isset($_POST["delete"])) {
    $rtntask->check($_POST['id'], DELETE);
    $ok = $rtntask->delete($_POST);
    if ($ok) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
    }
    $rtntask->redirectToList();
} else if (isset($_POST["restore"])) {
    $rtntask->check($_POST['id'], PURGE);
    if ($rtntask->restore($_POST)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s restores the item'), $_SESSION["glpiname"]));
    }
    $rtntask->redirectToList();
} else if (isset($_REQUEST["purge"])) {
    $rtntask->check($_REQUEST['id'], PURGE);
    if ($rtntask->delete($_REQUEST, 1)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
    }
    $rtntask->redirectToList();

//update a task
} else if (isset($_POST["update"])) {
    $rtntask->check($_POST['id'], UPDATE);
    $rtntask->update($_POST);
    Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
    Html::back();
} else if (isset($_GET["unglobalize"])) {
    $rtntask->check($_GET["id"], UPDATE);

    //TODO There is probably a bug here... 
    Html::redirect(Toolbox::getItemTypeFormURL('PluginRtntestalexTask') . "?id=" . $_GET["id"]);
} else {
    // Affichage du fil d'Ariane
    Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexTask");
    //show task form to add
    $rtntask->display(array('id' => $_GET["id"]));
    html::footer();
}
