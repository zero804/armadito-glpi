<?php

/*
Copyright (C) 2016 Teclib'

This file is part of Armadito Plugin for GLPI.

Armadito Plugin for GLPI is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Armadito Plugin for GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Armadito Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.

**/

include_once("toolbox.class.php");

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginArmaditoAVConfigDetail extends PluginArmaditoEAVCommonDBTM
{
    protected $id;
    protected $agentid;
    protected $agent;
    protected $entries;
    protected $antivirus;

    static function getTypeName($nb = 0)
    {
        return __('AVConfig Details', 'armadito');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginArmaditoAVConfigDetail') {
            return __('Antiviruses\' configurations', 'armadito');
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginArmaditoAVConfigDetail') {
            $paAVConfig = new self();
            $paAVConfig->showForm($item->fields["plugin_armadito_agents_id"]);
        }

        return TRUE;
    }

    function getAgent()
    {
        return $this->agent;
    }

    function getId()
    {
        return $this->id;
    }

    function initFromJson($jobj)
    {
        $this->setAgentFromJson($jobj);
        $this->antivirus = $this->agent->getAntivirus();
        $this->entries = $jobj->task->obj;
    }

    function run()
    {
        $i = 0;
        foreach ($this->entries as $entry)
        {
            $i++;

            PluginArmaditoLog::Verbose("[".$i."] ".$entry->{'attr'}."=".$entry->{'value'});

            $is_agentrow_indb       = $this->isValueForAgentInDB($entry->{'attr'}, $this->agentid);
            $is_baserow_indb        = $this->isValueForAgentInDB($entry->{'attr'}, 0);
            $is_baserow_equal       = $this->isValueEqualForAgentInDB($entry->{'attr'}, $entry->{'value'}, 0);

            if($is_baserow_equal) {
                $this->rmValueFromDB($entry->{'attr'}, $entry->{'value'}, $this->agentid);
                continue;
            }

            if($is_agentrow_indb) {
                $this->updateValueInDB($entry->{'attr'}, $entry->{'value'}, $this->agentid);
                continue;
            }

            if ($is_baserow_indb) {
                $this->addOrUpdateValueForAgent($entry->{'attr'}, $entry->{'value'}, $this->agentid);
            } else {
                $this->insertValueInDB($entry->{'attr'}, $entry->{'value'}, 0);
            }
        }

        $this->addOrUpdateValueForAgent("hasAVConfig", 1, $this->agentid);

        $this->id = PluginArmaditoDbToolbox::getLastInsertedId();
        PluginArmaditoToolbox::validateInt($this->id);
    }

    function showForm($id, $options = array())
    {
        PluginArmaditoToolbox::validateInt($id);
        $agent_id = $this->fields["plugin_armadito_agents_id"];

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th >" . __('Type', 'armadito') . "</th>";
        echo "<th >" . __('Value', 'armadito') . "</th>";
        echo "</tr>";

        $agent_entries  = $this->findEntries($agent_id);

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2' > Configuration specific to agent n° ". htmlspecialchars($agent_id) ."</th>";
        echo "</tr>";

        foreach ($agent_entries as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td align='center'>" . htmlspecialchars($data["type"]) . "</td>";
            echo "<td align='center'>" . htmlspecialchars($data["value"]) . "</td>";
            echo "</tr>";
        }

        $common_entries = $this->findEntries(0);

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2' > Common configuration </th>";
        echo "</tr>";

        foreach ($common_entries as $data) {
            echo "<tr class='tab_bg_1'>";
            echo "<td align='center'>" . htmlspecialchars($data["type"]) . "</td>";
            echo "<td align='center'>" . htmlspecialchars($data["value"]) . "</td>";
            echo "</tr>";
        }


        echo "</table>";
    }

    function findEntries($agent_id)
    {
        global $DB;

        $query = "SELECT id, value, type FROM `glpi_plugin_armadito_avconfigdetails`
                 WHERE `plugin_armadito_agents_id`='" . $agent_id . "'";

        $data = array();
        if ($result = $DB->query($query)) {
            if($DB->numrows($result)) {
                while ($line = $DB->fetch_assoc($result)) {
                    $data[$line['id']] = $line;
                }
            }
        }

        return $data;
    }

    function getEffectiveEntry($common_data, $agent_entries)
    {

        return $common_data;
    }
}
?>
