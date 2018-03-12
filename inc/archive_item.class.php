<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// Relation entre les archives et les items (ex: bandes)
class PluginRtntestalexArchive_Item extends CommonDBRelation {

    // From CommonDBRelation
    static public $itemtype_1 = 'PluginRtntestalexArchive';
    static public $items_id_1 = 'plugin_rtntestalex_archives_id';
    static public $itemtype_2 = 'itemtype';
    static public $items_id_2 = 'items_id';
    // Items li�s aux archives
    static protected $linkableClasses = array('PluginRtntestalexCartridge');

    /**
     * Name of the type
     *
     * @param $nb  integer  number of item in the type (default 0)
     * */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Direct Connections');
    }

    /**
     * Gestion des droits
     * */
    function can($ID, $right, array & $input = NULL) {
        if ($ID < 0) {
            // Mode Ajout
            if (!($item = new $input['itemtype'])) {
                return false;
            }

            if (!$item->getFromDB($input['items_id'])) {
                return false;
            }

            if ($item->getField('is_global') == 0 && self::countForItem($ID) > 0) {
                return false;
            }
        }
        return parent::can($ID, $right, $input);
    }

    /**
     * Permet de compter le nombre de connexion � un item 
     * @param $id id de l'objet selectionn�
     *
     */
    static function countForItem($id) {
        return 0;
    }

    /**
     * @param CommonDBTM $item Item en relation avec les archives
     * @return integer Nombre de relations entre l'item et les archives
     */
    static function countForItemByItemtype(CommonDBTM $item) {
        global $DB;

        $query = "SELECT * FROM `glpi_plugin_rtntestalex_archives` WHERE cartridgeslist LIKE '%" . $item->getName() . "%'";
        $result = $DB->query($query);

        return $DB->numrows($result);
    }

    /**
     * Fonction permettant la suppresion des relations apres la desinstallation du plugin
     * @param $item 
     */
    static function cleanForItem(CommonDBTM $item) {
        $temp = new self();
        $temp->deleteByCriteria(
                array('itemtype' => $item->getType(),
                    'items_id' => $item->getField('id')));
    }

    /**
     * Retourne les classes li�es aux archives
     */
    static function getClasses() {
        return self::$linkableClasses;
    }

    /**
     * D�claration d'un nouveau type d'item pour la relation avec les archives
     */
    static function registerItemtype($itemtype) {
        if (!in_array($itemtype, self::$linkableClasses)) {
            array_push(self::$linkableClasses, $itemtype);
            Plugin::registerClass('PluginRtntestalexArchive_Item', array('addtabon' => $itemtype));
        }
    }

    /**
     * Fonction execut�e � l'installation du plugin (pour la gestion des liens avec les archives)
     */
    static function install(Migration $migration) {
        global $DB;
    }

    /**
     * Fonction execut�e � la mise � jour du plugin (pour la gestion des liens avec les archives)
     */
    static function upgrade(Migration $migration) {
        global $DB;
    }

    /**
     * Fonction execut�e � la d�sinstallation du plugin (pour la gestion des liens avec les archives)
     */
    static function uninstall() {
        global $DB;
    }

    /**
     * Affichage des informations pour l'item li�
     */
    static function showForItem(CommonDBTM $item) {
        global $DB, $LANG;

        if ($item->isNewID($item->getID())) {
            return false;
        }
        if (!$item->canView()) {
            return false;
        }

        //Permet de récupèrer la liste des archives ou les bandes sont présentes 
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_archives` WHERE cartridgeslist LIKE '%" . $item->getName() . "%'";

        $result = $DB->query($query);



        echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s("Name") . "</th></tr>";
        if ($DB->numrows($result)) {
            while ($data = $DB->fetch_array($result)) {
                echo "<tr>";
                //On affiche l'archivage associé à la bande sur laquel on est présent
                echo "<td class='center'><a href='archive.form.php?id=$data[id]'>$data[name]</a></td>";
                //echo "</br>";
                echo "</tr>";
            }
        }
        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    /**
     * Affichage de l'onglet vertical pour l'item li�
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $CFG_GLPI;

        if (PluginRtntestalexArchive::canView()) {
            switch ($item->getType()) {
                // Onglet pour les bandes
                case 'PluginRtntestalexCartridge' :
                    $ong = array();
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        return self::createTabEntry(__s('Archives', 'rtntestalex'), self::countForItemByItemtype($item)); // LIBELLE A MODIFER
                    } else {
                        return __s('Archives', 'rtntestalex'); // LIBELLE A MODIFER 
                    }
                    
                default :
                    return '';
            }
        }
        return '';
    }

    /**
     * Affichage dans la fiche Archive ou dans l'item li�
     *
     * @param CommonGLPI $item
     * @param number $tabnum
     * @param number $withtemplate
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        if (in_array(get_class($item), PluginRtntestalexArchive_Item::getClasses())) {
            self::showForItem($item);
        }
        return true;
    }

}

?>
