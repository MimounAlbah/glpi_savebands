<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// Relation between Report and Items (computer only)
class PluginRtntestalexReport_Item extends CommonDBRelation {

    // From CommonDBRelation
    static public $itemtype_1 = 'PluginRtntestalexReport';
    static public $items_id_1 = 'plugin_rtntestalex_reports_id';
    static public $itemtype_2 = 'itemtype';
    static public $items_id_2 = 'items_id';
    // Elements liés aux rapports
    static protected $linkableClasses = array('PluginRtntestalexCartridge');

    /**
     * Nom du type
     *
     * @param $nb  integer  nombre de l'item 
     * */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Direct Connections'); //LIBELLE A MODIFIER
    }

    /**
     * Check right on an item - overloaded to check is_global
     *
     * @param $ID     ID of the item (-1 if new item)
     * @param $right  Right to check : r / w / recursive
     * @param $input  array of input data (used for adding item) (default NULL)
     *
     * @return boolean
     * */
    function can($ID, $right, array &$input = NULL) {
        if ($ID < 0) {
            // Ajout
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
     * Actions permettant de compter le nombre d'élèment présent dans la base au total
     *
     */
    static function countForItem($id) {
        return 0;
    }

    /**
    * Actions permettant de compter le nombre d'élement present dans la base pour un élément donné
    *
     */
    static function countForItemByItemtype(CommonDBTM $item) {
        global $DB;

        //Requete permettant de recuperer tous les rapports pour un certain nom de bande
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_reports_taskslinks` WHERE cartridgeslist LIKE '%" . $item->getName() . "%'";
        $result = $DB->query($query);

        //On renvoi le nombre de lignes trouvés par la requête SQL
        return $DB->numrows($result);
    }

    /**
     * Hool appele apres la surpression d'un élément
     */
    static function cleanForItem(CommonDBTM $item) {
        $temp = new self();
        $temp->deleteByCriteria(
                array('itemtype' => $item->getType(),
                    'items_id' => $item->getField('id')));
    }

    static function getClasses() {
        return self::$linkableClasses;
    }

    /**
     * Declare a new itemtype to be linkable to a report
     */
    static function registerItemtype($itemtype) {
        if (!in_array($itemtype, self::$linkableClasses)) {
            array_push(self::$linkableClasses, $itemtype);
            Plugin::registerClass('PluginRtntestalexReport_Item', array('addtabon' => $itemtype));
        }
    }

    static function install(Migration $migration) {
        global $DB;
    }

    /**
     * @since 1.3
     * */
    static function upgrade(Migration $migration) {
        global $DB;
    }

    static function uninstall() {
        global $DB;
    }

    static function showForItem(CommonDBTM $item) {
        global $DB, $LANG;

        if ($item->isNewID($item->getID())) {
            return false;
        }

        if (!$item->canView()) {
            return false;
        }

        //Requete permettant de récupérer le rapport où la bande est présente 
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_reports_taskslinks` JOIN `glpi_plugin_rtntestalex_reports` ON `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id` = `glpi_plugin_rtntestalex_reports`.`id` WHERE cartridgeslist LIKE '%" . $item->getName() . "%'";

        $result = $DB->query($query);



        echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s("Name") . "</th></tr>";
        if ($DB->numrows($result)) {
            while ($data = $DB->fetch_array($result)) {
                echo "<tr>";
                //On affidhe le nom du rapport et on crée une redirecton vers ce rapport
                echo "<td class='center'><a href='report.form.php?id=$data[id]'>$data[name]</a></td>";
                //echo "</br>";
                echo "</tr>";
            }
        }
        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $CFG_GLPI;

        if (PluginRtntestalexReport::canView()) {
            switch ($item->getType()) {
                case 'PluginRtntestalexCartridge' :
                    $ong = array();
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        return self::createTabEntry(__s('Rapports', 'rtntestalex'), self::countForItemByItemtype($item)); //LIBELLE A MODIFIER
                    } else {
                        return __s('Rapports', 'rtntestalex'); //LIBELLE A MODIFIER                            
                    }

                default :
                    return '';
            }
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if (in_array(get_class($item), PluginRtntestalexReport_Item::getClasses())) {
            self::showForItem($item);
        }
        return true;
    }

}

?>
