<?php

include ('../../../inc/includes.php');

PluginRtntestalexCartridge::canView();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (!isset($_GET["sort"])) {
    $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
    $_GET["order"] = "";
}

$rtncartridge = new PluginRtntestalexCartridge();

//Add a new cartridge
if (isset($_POST["add"])) {
    $rtncartridge->check(-1, CREATE, $_POST);
    if ($newID = $rtncartridge->add($_POST)) {
        Event::log($newID, "rtntestalex", 4, "tools", sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
    }
    Html::back();

// delete a cartridge
} else if (isset($_POST["delete"])) {
    $rtncartridge->check($_POST['id'], DELETE);
    $ok = $rtncartridge->delete($_POST);
    if ($ok) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
    }
    $rtncartridge->redirectToList();
} else if (isset($_POST["restore"])) {
    $rtncartridge->check($_POST['id'], PURGE);
    if ($rtncartridge->restore($_POST)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s restores the item'), $_SESSION["glpiname"]));
    }
    $rtncartridge->redirectToList();
} else if (isset($_REQUEST["purge"])) {
    $rtncartridge->check($_REQUEST['id'], PURGE);
    if ($rtncartridge->delete($_REQUEST, 1)) {
        Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
    }
    $rtncartridge->redirectToList();

//update a cartridge
} else if (isset($_POST["update"])) {
    $rtncartridge->check($_POST['id'], UPDATE);
    $rtncartridge->update($_POST);
    Event::log($_POST["id"], "rtntestalex", 4, "tools", sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
    Html::back();
} else if (isset($_GET["unglobalize"])) {
    $rtncartridge->check($_GET["id"], UPDATE);

    //TODO There is probably a bug here... 
    Html::redirect(Toolbox::getItemTypeFormURL('PluginRtntestalexCartridge') . "?id=" . $_GET["id"]);
} else {
    // Affichage du fil d'Ariane
    Html::header(__s('gen_plugin_name', 'rtntestalex'), '', "tools", "PluginRtntestalexMenuTools", "PluginRtntestalexCartridge");
    //show cartridge form to add
    $rtncartridge->display(array('id' => $_GET["id"]));
    html::footer();
}
