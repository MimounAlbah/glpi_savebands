<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

//      Class PluginRtntestalexPlanning
class PluginRtntestalexPlanning extends CommonDBTM {
  
  /**
  * Retourne le nom de la classe
  */
  static function getTypeName($nb = 0) {
    global $LANG;
    return __s('Planning', 'rtntestalex');
  }

  /**
   * Affiche le cadre avec la date de debut / date de fin
   **/
  function showFormDate() {
    GLOBAL $datecherche;

    if(!isset($datecherche)) {

      //On stock le jour de la date du jour
      $jour = date("d",time());
      //On stock le mois de la date du jour
      $mois = date("m",time());
      //On stock l'année de la date du jour
      $annee = date("Y",time());
      //On stock la date du jour 
      $begin_time = time();

      //On formate la date du jour en mettant l'heure, minute, secondes à 00
      $datecherche["begin"]  = date("Y-m-d 00:00:00",$begin_time);

    }

    //On check si on a appuyé sur le bouton semaine suivante
    if(isset($_POST['nextweek'])) {

      //On stock la date présent dans le champ date
      $tmpbegin = $datecherche["begin"];

      //On reformate la date en ajoutant une semaine à celle saisi
      $datecherche["begin"] = date("Y-m-d H:i:s", strtotime($datecherche["begin"]) + WEEK_TIMESTAMP);
    }

    //On check si on a appuyé sur le bouton semaine précédente
    if(isset($_POST['previousweek'])) {

      //On stock la date présente dans le champ date
      $tmpbegin = $datecherche["begin"];

      //On reformate la date en enlevant une semaine à celle saisi
      $datecherche["begin"] = date("Y-m-d H:i:s", strtotime($datecherche["begin"]) - WEEK_TIMESTAMP);
    }

    echo "<div id='search_page'  class='center'>";
    echo "<form method='post' name='form' action='/glpi/plugins/rtntestalex/front/planning.form.php'>";
    echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'>";
    echo "<td class='center'>";
    echo "<input type='submit' class='submit' name='previousweek' value=\"Semaine precedente\">";// LIBELLE A MODIFIER
    echo "</td>";

    echo "<td class='center'>Date : </td>";     // LIBELLE A MODIFIER
    echo "<td class='center'>";                 
    Html::showDateTimeField("reserve[begin]", array('value' =>  $datecherche["begin"],
	  'maybeempty' => false));
    echo "</td>";
    echo "<td>";
    echo "<input type='submit' class='submit' name='submit' value=\""._sx('button', 'Search')."\">"; //LIBELLE A MODIFIER
    echo "</td>";
    echo "<td class='center'>";
    echo "<input type='submit' class='submit' name='nextweek' value=\"Semaine suivante\">"; // LIBELLE A MODIFIER
    echo "</td></tr>";

    echo "</table>";
    Html::closeForm();
    echo "</div>";

  }
  
  //On affiche le planing pour la date du jour 
  function showCurrentPlanning() {
    global $DB, $CFG_GLPI, $datecherche;

	  $config = new PluginReservationConfig();
    $methode = $config->getConfigurationMethode();
        
    $date = $datecherche["begin"];

    //$date_test = date("Y-m-d");
    $date_test = $datecherche["begin"];
    $good_format = strtotime($date_test);
    $annee = date('Y', $good_format);
    $semaine = date('W', $good_format);

    echo "<div class='search_page'><table class='tab_cadre_pager'><tbody><tr class='tab_bg_2'>";
    echo "<td class='b big'>Semaine n°".$semaine."</td>";
    echo "</tbody></table></div>";

    $good_format = strtotime ($date);
    $annee = date('Y', $good_format);
    $semaine = date('W', $good_format);

    $lundi = new DateTime();
    $datetmp = $lundi->setISOdate($annee, $semaine);

    echo "<table class='tab_cadrehov' width='100%' border='1'>";
    echo "<tr>";
    $datetmp = $lundi;
    $dateentete = $lundi;
    $dateentete = $dateentete->format('d/m/Y');
    echo "<th width='15%'>LUNDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');
    echo "<th width='15%'>MARDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "<th width='15%'>MERCREDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "<th width='15%'>JEUDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "<th width='15%'>VENDREDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "<th width='15%'>SAMEDI</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "<th width='10%'>DIMANCHE</BR></BR>". $dateentete ."</th>";
    $dateentete = $datetmp->add(new DateInterval('P1D'));
    $dateentete = $dateentete->format('d/m/Y');        
    echo "</tr>";
    echo "<tr>";
       
    $lundi = new DateTime();
    $test = $lundi->setISOdate($annee, $semaine);
        
    for($i = 0; $i <= 6; $i++){

      if ($i > 0 ){
        $tmp = $test->add(new DateInterval('P1D'));
      } else {
        $tmp = $test;
      }
      $tmp = $tmp->format('Y-m-d');

      $query = "SELECT DISTINCT `glpi_plugin_rtntestalex_reports_taskslinks`.`id`,
      `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id`,
      `glpi_plugin_rtntestalex_reports_taskslinks`.`is_realized`, 
      `glpi_plugin_rtntestalex_tasks`.`name`, 
      `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_states_id`, 
      `glpi_plugin_rtntestalex_tasks`.`id`,
      `glpi_plugin_rtntestalex_tasks`.`cartridges`, 
      `glpi_plugin_rtntestalex_reports_taskslinks`.`cartridgeslist`, 
      `glpi_plugin_rtntestalex_reports_taskslinks`.`comment`, 
      `glpi_plugin_rtntestalex_reports_taskslinks`.`date_checktask` 
      FROM `glpi_plugin_rtntestalex_tasks` 
      JOIN `glpi_plugin_rtntestalex_reports_taskslinks` 
      ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_tasks_id`
      JOIN `glpi_plugin_rtntestalex_reports` 
      ON `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id` = `glpi_plugin_rtntestalex_reports`.`id`
      JOIN `glpi_plugin_rtntestalex_tasks_items`
      ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id`
      WHERE `glpi_plugin_rtntestalex_reports_taskslinks`.`date_checktask` LIKE '%" . $tmp . "%' AND `glpi_plugin_rtntestalex_reports`.`is_deleted` = 0
      ORDER BY `glpi_plugin_rtntestalex_tasks`.`id`";

      echo "<td valign='top' align='center'>";
      echo "<table>";
      $result = $DB->query($query);   
      if ($DB->numrows($result)) {
        while ($data = $DB->fetch_array($result)) {                  
          echo "<tr>";
          echo "<td><a href='report.form.php?id=".$data['plugin_rtntestalex_reports_id']."'>".$data['name']."</a></td>";
          if ($data['plugin_rtntestalex_states_id'] == 1) echo "<td><img src='/glpi/pics/ok_min.png'/></td>";
          if ($data['plugin_rtntestalex_states_id'] == 2) echo "<td><img src='/glpi/pics/warning_min.png'/></td>";
          if ($data['plugin_rtntestalex_states_id'] == 3) echo "<td><img src='/glpi/pics/ko_min.png'/></td>";
          echo "</tr>";
        }
      }
      echo "</table>";           
      echo "</td>";
    }

    echo "</tr>";      
    echo "</table><br>"; 

    return true;
  }

  /**
  * Fonction executée a l'installation du plugin (pour la gestion des t�ches)
  */
  static function install(Migration $migration) {
    global $DB;
  }

  /**
  * Fonction executée a la mise a jour du plugin (pour la gestion des t�ches)
  */
  static function upgrade(Migration $migration) {
    global $DB;

    switch (plugin_rtntestalex_currentVersion()) {
      case '1.0':
      case '1.x':
    }
  }

  /**
  * Fonction executée a la desinstallation du plugin (pour la gestion des t�ches)
  */
  static function uninstall() {
    global $DB;
  }
   
}   
?>
