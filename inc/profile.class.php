<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

//Classe Profile
class PluginRtntestalexProfile extends Profile {

    const RIGHT_RTNTESTALEX_TASK = "rtntestalex:rtntask";
    const RIGHT_RTNTESTALEX_REPORT = "rtntestalex:rtnreport";
    const RIGHT_RTNTESTALEX_ARCHIVE = "rtntestalex:rtnarchive";
    const RIGHT_RTNTESTALEX_CARTRIDGE = "rtntestalex:rtncartridge";
    const RIGHT_RTNTESTALEX_STATETYPE = "rtntestalex:rtnstatetype";
    const RIGHT_RTNTESTALEX_TASKTYPE = "rtntestalex:rtntasktype";
    const RIGHT_RTNTESTALEX_ARCHIVETYPE = "rtntestalex:rtnarchivetype";
    const RIGHT_RTNTESTALEX_VERIFTYPE = "rtntestalex:rtnveriftype";
    
    static $rightname = 'profile';

    /**
     * création de l'accés pour l'utilisateur
     * @param $ID l'id de l'utilisateur 
     */
    function createAccess($ID) {
        $this->add(array('profiles_id' => $ID));
    }

    /**
     * Création du premier accès 
     * @param $ID id de l'utilisateur 
     *
     */
    static function createFirstAccess($ID) {
        $profileRight = new ProfileRight();

        // DROITS INITIAUX POUR LES TACHES
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_TASK));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_TASK => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE
        ));
        $profileRight->updateProfileRights($ID, $firstAccessRights);
        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_TASK] = $firstAccessRights[self::RIGHT_RTNTESTALEX_TASK];

        // DROITS INITIAUX POUR LES RAPPORTS
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_REPORT));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_REPORT => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE
        ));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_REPORT] = $firstAccessRights[self::RIGHT_RTNTESTALEX_REPORT];

        // DROITS INITIAUX POUR LES ARCHIVAGES
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_ARCHIVE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_ARCHIVE => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE
        ));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_ARCHIVE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_ARCHIVE];

        // DROITS INITIAUX POUR LA LISTE DES BANDES
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_CARTRIDGE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_CARTRIDGE => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE
        ));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_CARTRIDGE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_CARTRIDGE];
        
        // DROITS INITIAUX POUR LA LISTE DES ETATS
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_STATETYPE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_STATETYPE => ALLSTANDARDRIGHT));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_STATETYPE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_STATETYPE];

        // DROITS INITIAUX POUR LES TYPES DES TACHES
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_TASKTYPE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_TASKTYPE => ALLSTANDARDRIGHT));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_TASKTYPE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_TASKTYPE];

        // DROITS INITIAUX POUR LES TYPES D'ARCHIVES
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_ARCHIVETYPE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_ARCHIVETYPE => ALLSTANDARDRIGHT));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_ARCHIVETYPE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_ARCHIVETYPE];

        // DROITS INITIAUX POUR LES TYPES DE VERIFICATION
        $currentRights = ProfileRight::getProfileRights($ID, array(self::RIGHT_RTNTESTALEX_VERIFTYPE));
        $firstAccessRights = array_merge($currentRights, array(self::RIGHT_RTNTESTALEX_VERIFTYPE => ALLSTANDARDRIGHT));
        $profileRight->updateProfileRights($ID, $firstAccessRights);

        // On ajoute des droits pour la session courante
        $_SESSION['glpiactiveprofile'][self::RIGHT_RTNTESTALEX_VERIFTYPE] = $firstAccessRights[self::RIGHT_RTNTESTALEX_VERIFTYPE];        
    }

    /**
     * Actions perlettant d'afficher le formulaire de modification 
     * @param $ID id de l'utilisateur connecté
      $ôptions array
     *
     */
    function showForm($ID, $options = array()) {
        global $LANG;
        //On test si l'utilisateur connecté peut voir le formulaire sinon on return false qui affichera un écran blanc
        if (!Profile::canView()) {
            return false;
        }

        $canedit = self::canUpdate();
        $profile = new Profile();

        if ($ID) {
            //$this->getFromDBByProfile($ID);
            $profile->getFromDB($ID);
        }

        //Si l'utilisateur possède les droits 
        if ($canedit) {
            echo "<form action='" . $profile->getFormURL() . "' method='post'>";
        }
        echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
        echo "<tr><th>".__s('gen_profil_general', 'rtntestalex')."</th></tr>";
        echo "</table>";

        $rights = $this->getAllRights();
        $profile->displayRightsChoiceMatrix($rights, array('canedit' => $canedit,
            'default_class' => 'tab_bg_2'));
                
        // On affiche les informations par rapports aux droits
        echo "<div class='spaced'><table class='tab_cadre_fixehov'>"; 
        echo "<tr><th><span class='red'>".__s('gen_profil_msg', 'rtntestalex')."</span></th></tr>";
        echo "</table>";
        $this->showLegend();
        
        echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
        echo "<tr><th>" . __s('gen_profil_mgmt_list', 'rtntestalex') . "</th></tr>";
        echo "</table>";
        $rights = $this->getTypeRights();
        $profile->displayRightsChoiceMatrix($rights, array('canedit' => $canedit,
            'default_class' => 'tab_bg_2'));
        
        // On affiche les informations par rapports aux droits
        echo "<div class='spaced'><table class='tab_cadre_fixehov'>"; 
        echo "<tr><th><span class='red'>".__s('gen_profil_msg', 'rtntestalex')."</span></th></tr>";
        echo "</table>";
        $this->showLegend();
        
        // On ajoute le bouton de sauvegarde du profil
        if ($canedit) {
            echo "<div class='center'>";
            echo "<input type='hidden' name='id' value=" . $ID . ">";
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
            echo "</div></br></br>";
        }
        Html::closeForm();
    }

    static function install(Migration $migration) {
        global $DB;

        // Table no longer needed in GLPI 0.85+; drop it. Needed for upgrades
        $migration->dropTable(getTableForItemType(__CLASS__));
        PluginRtntestalexProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    }

    /**
     * Actions permettant lors de la mise à jour du plugin de faire des changements
     *
     * @since 1.3
     * */
    static function upgrade(Migration $migration) {
        global $DB;

        $table = getTableForItemType(__CLASS__);
        switch (plugin_rtntestalex_currentVersion()) {
            case '1.0':
            case '1.x':
        }
    }

    /**
     * Init profiles
     *
     * */
    static function translateARight($old_right) {
        switch ($old_right) {
            case 'r' :
                return READ;

            case 'w':
                return ALLSTANDARDRIGHT;

            case '1':

            case '0':
            case '':
            default:
                return 0;
        }
    }

    //fonction qui permet la desinstallation de la table glpi_plugin_rtntestalex_profile
    static function uninstall() {
        global $DB;

        // Taches
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_TASK));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_TASK]);

        // Rapports
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_REPORT));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_REPORT]);

        // Archivages
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_ARCHIVE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_ARCHIVE]);

        // Liste des bandes
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_CARTRIDGE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_CARTRIDGE]);
        
        // Liste des etats
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_STATETYPE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_STATETYPE]);

        // Type de taches
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_TASKTYPE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_TASKTYPE]);

        // Type de d'archives
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_ARCHIVETYPE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_ARCHIVETYPE]);
        
        // Type de vérification
        ProfileRight::deleteProfileRights(array(self::RIGHT_RTNTESTALEX_VERIFTYPE));
        unset($_SESSION["glpiactiveprofile"][self::RIGHT_RTNTESTALEX_VERIFTYPE]);        
    }

    //Permet d'attribuer le nom qui sera afficher dans le plugin Profile
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $LANG;
        if ($item->getType() == 'Profile') {
            return __s('gen_plugin_name', 'rtntestalex');
        }
        return '';
    }

    //Lors du clic sur ROUTIN Test ALEX pour un profil(exemple : superadmin) dans le plugin PROFIL permet d'afficher le formulaire de gestion des droits 
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'Profile') {
            $profile = new self();
            $profile->showForm($item->getField('id'));
        }
        return true;
    }

    function getAllRights() {
        $rights = array(
            array('itemtype' => 'PluginRtntestalexTask',
                'label' => PluginRtntestalexTask::getTypeName(2),
                'field' => 'rtntestalex:rtntask'
            ),
            array('itemtype' => 'PluginRtntestalexReport',
                'label' => PluginRtntestalexReport::getTypeName(2),
                'field' => 'rtntestalex:rtnreport'
            ),
            array('itemtype' => 'PluginRtntestalexArchive',
                'label' => PluginRtntestalexArchive::getTypeName(2),
                'field' => 'rtntestalex:rtnarchive'
            ),
            array('itemtype' => 'PluginRtntestalexCartridge',
                'label' => PluginRtntestalexCartridge::getTypeName(2),
                'field' => 'rtntestalex:rtncartridge'
            ),              
        );
        return $rights;
    }

    function getTypeRights() {
        $rights = array(
            array('itemtype' => 'PluginRtntestalexStateType',
                'label' => __s('gen_list_states', 'rtntestalex'),
                'field' => 'rtntestalex:rtnstatetype'
            ),
            array('itemtype' => 'PluginRtntestalexTaskType',
                'label' => __s('gen_type_tasks', 'rtntestalex'),
                'field' => 'rtntestalex:rtntasktype'
            ),
            array('itemtype' => 'PluginRtntestalexArchiveType',
                'label' => __s('gen_type_archives', 'rtntestalex'),
                'field' => 'rtntestalex:rtnarchivetype'
            ),
            array('itemtype' => 'PluginRtntestalexVerifType',
                'label' => __s('gen_type_verification', 'rtntestalex'),
                'field' => 'rtntestalex:rtnveriftype'
            ),            
        );
        return $rights;
    }

}

?>