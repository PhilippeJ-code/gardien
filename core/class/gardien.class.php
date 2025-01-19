<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__  . '/../../../../core/php/core.inc.php';

class gardien extends eqLogic
{
    // Fonction exécutée toutes les minutes
    //
    public static function cron()
    {

        // Pour chacun des équipements
        //
        foreach (gardien::byType('gardien', true) as $gardien) {
            if ($gardien->getIsEnable() == 1) {

                // Est-il temps de gardienner ?
                //
                if ($gardien->getConfiguration('cron_gardiennage') != '') {
                    try {
                        $c = new Cron\CronExpression(checkAndFixCron($gardien->getConfiguration('cron_gardiennage')), new Cron\FieldFactory());
                        if ($c->isDue()) {
                            $gardien->gardienner();
                        }
                    } catch (Exception $e) {
                        log::add('gardien', 'error', $gardien->getHumanName() . ' : ' . $e->getMessage());
                    }
                }
            }
        }
    }

    // Fonction de gardiennage
    //
    public function gardienner()
    {
        $bSave = false;
        // Equipements
        //
        if ($this->getConfiguration('equipements')) {
            $equipements = $this->getConfiguration('equipements');
            for ($i = 0; $i < count($equipements);$i++) {
                $eqLogic = eqLogic::byId(str_replace(array('eqLogic','#'), '', $equipements[$i]['eqLogic']));
                if (!is_object($eqLogic)) {
                    log::add('gardien', 'info', 'Equipement ' . $equipements[$i]['eqLogic'] . ' inconnu');
                    continue;
                }
                if (isset($equipements[$i]['options'])) {
                    $humanEqLogic = eqLogic::toHumanReadable($equipements[$i]['eqLogic']);
                    $expression = '(#timestamp# - strtotime(lastCommunication('. $humanEqLogic.'))) '.$equipements[$i]['options']['condition'];
                    $scenario = null;
                    $return = evaluate(scenarioExpression::setTags(jeedom::fromHumanReadable($expression), $scenario, true));
                    $state = 'Vrai';
                    if (is_bool($return)) {
                        if ($return) {
                            $state = 'Faux';
                        }
                    }
                    if (!isset($equipements[$i]['options']['state'])) {
                        $equipements[$i]['options']['state'] = 'Inconnu';
                    }
                    if ($equipements[$i]['options']['state'] != $state) {
                        if ($state == 'Vrai') {
                            $this->ActionsVrai($humanEqLogic);
                        } else {
                            $this->ActionsFaux($humanEqLogic);
                        }
                        $equipements[$i]['options']['state'] = $state;
                        $bSave = true;
                    }
                }
            }
            $this->setConfiguration('equipements', $equipements);
        }
        // Commandes
        //
        if ($this->getConfiguration('commandes')) {
            $commandes = $this->getConfiguration('commandes');
            for ($i = 0; $i < count($commandes);$i++) {
                $cmd = cmd::byId(str_replace('#', '', $commandes[$i]['cmd']));
                if (!is_object($cmd)) {
                    log::add('gardien', 'info', 'Commande ' . $commandes[$i]['cmd'] . ' inconnu');
                    continue;
                }
                if (isset($commandes[$i]['options'])) {
                    $humanCmd = cmd::cmdToHumanReadable($commandes[$i]['cmd']);
                    if ( $commandes[$i]['options']['collectdate'] == 1 )
                        $expression = '(#timestamp# - strtotime(collectDate('. $humanCmd.'))) '.$commandes[$i]['options']['condition'];
                    else
                        $expression = $humanCmd.' '.$commandes[$i]['options']['condition'];
                    $scenario = null;
                    $return = evaluate(scenarioExpression::setTags(jeedom::fromHumanReadable($expression), $scenario, true));
                    $state = 'Vrai';
                    if (is_bool($return)) {
                        if ($return) {
                            $state = 'Faux';
                        }
                    }
                    if (!isset($commandes[$i]['options']['state'])) {
                        $commandes[$i]['options']['state'] = 'Inconnu';
                    }
                    if ($commandes[$i]['options']['state'] != $state) {
                        if ($state == 'Vrai') {
                            $this->ActionsVrai($humanCmd);
                        } else {
                            $this->ActionsFaux($humanCmd);
                        }
                        $commandes[$i]['options']['state'] = $state;
                        $bSave = true;
                    }
                }
            }
            $this->setConfiguration('commandes', $commandes);
        }
        if ( $bSave )
            $this->save();
}

    // On exécute les actions vrai
    //
    public function actionsVrai($humanEqLogic)
    {
        if ($this->getConfiguration('actions_vrai_conf')) {
            foreach ($this->getConfiguration('actions_vrai_conf') as $action) {
                try {
                    $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                    if (!is_object($cmd)) {
                        continue;
                    }
                    $options = array();
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    if (isset($options['title'])) {
                        $title = trim($options['title']);
                        $title = str_replace('#equipment#', $this->getName(), $title);
                        $title = str_replace('#object#', str_replace('#', '', $humanEqLogic), $title);
                        $options['title'] = $title;
                    }
                    if (isset($options['message'])) {
                        $message = trim($options['message']);
                        $message = str_replace('#equipment#', $this->getName(), $message);
                        $message = str_replace('#object#', str_replace('#', '', $humanEqLogic), $message);
                        $options['message'] = $message;
                    }
                  scenarioExpression::createAndExec('action', $action['cmd'], $options);
                } catch (Exception $e) {
                    log::add('gardien', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        }
    }

    // On exécute les actions faux
    //
    public function actionsFaux($humanEqLogic)
    {
        if ($this->getConfiguration('actions_faux_conf')) {
            foreach ($this->getConfiguration('actions_faux_conf') as $action) {
                try {
                    $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                    if (!is_object($cmd)) {
                        continue;
                    }
                    $options = array();
                    if (isset($action['options'])) {
                        $options = $action['options'];
                    }
                    if (isset($options['title'])) {
                        $title = trim($options['title']);
                        $title = str_replace('#equipment#', $this->getName(), $title);
                        $title = str_replace('#object#', str_replace('#', '', $humanEqLogic), $title);
                        $options['title'] = $title;
                    }
                    if (isset($options['message'])) {
                        $message = trim($options['message']);
                        $message = str_replace('#equipment#', $this->getName(), $message);
                        $message = str_replace('#object#', str_replace('#', '', $humanEqLogic), $message);
                        $options['message'] = $message;
                    }
                    scenarioExpression::createAndExec('action', $action['cmd'], $options);
                } catch (Exception $e) {
                    log::add('gardien', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        }
    }

    // Fonction exécutée automatiquement avant la création de l'équipement
    //
    public function preInsert()
    {

    }

    // Fonction exécutée automatiquement après la création de l'équipement
    //
    public function postInsert()
    {

    }

    // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    //
    public function preUpdate()
    {

    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement
    //
    public function postUpdate()
    {
    }

    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    //
    public function preSave()
    {

    }

    // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
    //
    public function postSave()
    {
    }

    // Fonction exécutée automatiquement avant la suppression de l'équipement
    //
    public function preRemove()
    {

    }

    // Fonction exécutée automatiquement après la suppression de l'équipement
    //
    public function postRemove()
    {

    }


}

class gardienCmd extends cmd
{
    // Exécution d'une commande
    //
    public function execute($_options = array())
    {

    }

}
