<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginRtntestalexMenuTools extends CommonGLPI {

    static $rightname = 'plugin_rtnrtntestalex';

    /**
     * Action permettant la récupération du nom poura affichage
     *
     */
    static function getMenuName() {
        return __s('gen_plugin_name', 'rtntestalex');
    }

    /**
     * Action permettant de checker si l'utilsateur connecté peut visualiser le formulaire
     */
    static function canView() {
        return Session::haveRight(self::$rightname, READ);
    }

    /**
     * Action permettant de checker si l'utilisateur peut créer, modifier et supprimer
     */
    static function canCreate() {
        return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, DELETE));
    }

    /**
     * Permet l'affichage de certains éléments dans le fil d'Ariane
     */
    static function getMenuContent() {
        global $CFG_GLPI;

        $menu = array();
        $menu['title'] = self::getMenuName();
        $menu['page'] = "/plugins/rtntestalex/front/menu.php";

        $menu['options']['PluginRtntestalexTask']['title'] = _n('task_task', 'task_tasks', 2, 'rtntestalex');
        $menu['options']['PluginRtntestalexTask']['page'] = '/plugins/rtntestalex/front/task.php';
        $menu['options']['PluginRtntestalexTask']['links']['add'] = '/plugins/rtntestalex/front/task.form.php?id=-1';

        $menu['options']['PluginRtntestalexReport']['title'] = _n('report_report', 'report_reports', 2, 'rtntestalex');
        $menu['options']['PluginRtntestalexReport']['page'] = '/plugins/rtntestalex/front/report.php';
        $menu['options']['PluginRtntestalexReport']['links']['add'] = '/plugins/rtntestalex/front/report.form.php?id=-1';

        $menu['options']['PluginRtntestalexArchive']['title'] = _n('archive_archive', 'archive_archives', 2, 'rtntestalex');
        $menu['options']['PluginRtntestalexArchive']['page'] = '/plugins/rtntestalex/front/archive.php';
        $menu['options']['PluginRtntestalexArchive']['links']['add'] = '/plugins/rtntestalex/front/archive.form.php?id=-1';

        $menu['options']['PluginRtntestalexCartridge']['title'] = _n('cartridge_cartridge', 'cartridge_cartridges', 2, 'rtntestalex');
        $menu['options']['PluginRtntestalexCartridge']['page'] = '/plugins/rtntestalex/front/cartridge.php';
        $menu['options']['PluginRtntestalexCartridge']['links']['add'] = '/plugins/rtntestalex/front/cartridge.form.php?id=-1';

        $menu['options']['PluginRtntestalexVerif']['title'] = _n('verification_verification', 'verification_verifications', 2, 'rtntestalex');
        $menu['options']['PluginRtntestalexVerif']['page'] = '/plugins/rtntestalex/front/verification.php';
        $menu['options']['PluginRtntestalexVerif']['links']['add'] = '/plugins/rtntestalex/front/verification.form.php?id=-1';

        $menu['options']['PluginRtntestalexPlanning']['title'] = __s('planning_planning', 'rtntestalex');
        $menu['options']['PluginRtntestalexPlanning']['page'] = '/plugins/rtntestalex/front/planning.form.php';
        $menu['options']['PluginRtntestalexPlanning']['links']['add'] = '/plugins/rtntestalex/front/report.form.php?id=-1';
        return $menu;
    }

    /**
     * Permet de rajouter des en-têtes à l'onglet 
     *
     */
    function defineTabs($options = array()) {
        $ong = array();
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
    }

    function showMenu() {
        global $CFG_GLPI;

        //if(!$this->canView()) return false;

        echo "<div align='center'>";
        echo "<table class='tab_cadre' cellpadding='5' height='150'>";
        echo "<tr>";
        echo "<th colspan='5'>". __s('gen_menu_name', 'rtntestalex')."</th>";
        echo "</tr>";
        echo "<tr class='tab_bg_1' style='background-color:white;'>";

        // Taches
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./task.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/format-list-ordered.png' alt=\"Taches\">";
        echo "</br></br>"._n('task_task', 'task_tasks', 2, 'rtntestalex')."</a>";
        echo "</td>";

        // Rapports
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./report.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/view-calendar-tasks.png' alt=\"Rapports\">";
        echo "</br></br>"._n('report_report', 'report_reports', 2, 'rtntestalex')."</a>";
        echo "</td>";

        // Archives
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./archive.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/folder-locked.png' alt=\"Archives\">";
        echo "</br></br>"._n('archive_archive', 'archive_archives', 2, 'rtntestalex')."</a>";
        echo "</td>";

        echo "</tr>";
        echo "<tr class='tab_bg_1' style='background-color:white;'>";
        
        // Taches (Bouton Ajouter)
        echo "<td class='center rtntestalex_menu_add'>";
        echo "<a class='rtntestalex_menu_add_a' href=\"./task.form.php?id=-1\">";
        echo "<img class='rtntestalex_menu_add_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/list-add.png' alt=\"Taches\"></a>";
        echo "</td>";

        // Rapports (Bouton Ajouter)
        echo "<td class='center rtntestalex_menu_add'>";
        echo "<a class='rtntestalex_menu_add_a' href=\"./report.form.php?id=-1\">";
        echo "<img class='rtntestalex_menu_add_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/list-add.png' alt=\"Rapports\"></a>";
        echo "</td>";

        // Archives (Bouton Ajouter)
        echo "<td class='center rtntestalex_menu_add'>";
        echo "<a class='rtntestalex_menu_add_a' href=\"./archive.form.php?id=-1\">";
        echo "<img class='rtntestalex_menu_add_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/list-add.png' alt=\"Archives\"></a>";
        echo "</td>";

        echo "</tr>";
        echo "<tr class='tab_bg_1' style='background-color:white;'>";
        
        // Bandes
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./cartridge.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/media-tape.png' alt=\"Bandes\">";
        echo "</br></br>"._n('cartridge_cartridge', 'cartridge_cartridges', 2, 'rtntestalex')."</a>";
        echo "</td>";

        // Vérification
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./commandgroup.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/preflight-verifier.png' alt=\"Vérification\">";
        echo "</br></br>"._n('verification_verification', 'verification_verifications', 2, 'rtntestalex')."</a>";
        echo "</td>";

        // Planning
        echo "<td class='center rtntestalex_menu_item'>";
        echo "<a  class='rtntestalex_menu_a' href=\"./planning.form.php\">";
        echo "<img class='rtntestalex_menu_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/view-calendar.png' alt=\"Planning\">";
        echo "</br></br>".__s('planning_planning', 'rtntestalex')."</a>";
        echo "</td>";

        echo "</tr>";
        echo "<tr class='tab_bg_1' style='background-color:white;'>";
        
        // Bandes (Bouton Ajouter)
        echo "<td class='center rtntestalex_menu_add'>";
        echo "<a class='rtntestalex_menu_add_a' href=\"./cartridge.form.php?id=-1\">";
        echo "<img class='rtntestalex_menu_add_img' src='" . $CFG_GLPI["root_doc"] . "/plugins/rtntestalex/pics/list-add.png' alt=\"Rapports\"></a>";
        echo "</td>";

        // Vérification
        echo "<td class='center rtntestalex_menu_add'>";
        echo "</td>";

        // Planning
        echo "<td class='center rtntestalex_menu_add'>";
        echo "</td>";

        echo "</tr>";
        echo "</table></div>";
    }

}

?>