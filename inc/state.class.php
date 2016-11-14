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

class PluginArmaditoState extends PluginArmaditoCommonDBTM
{
    protected $id;
    protected $agentid;
    protected $agent;
    protected $jobj;
    protected $antivirus;
    protected $statedetails_id;

    static function getTypeName($nb = 0)
    {
        return __('State', 'armadito');
    }

    function initFromJson($jobj)
    {
        $this->agentid = PluginArmaditoToolbox::validateInt($jobj->agent_id);
        $this->agent   = new PluginArmaditoAgent();
        $this->agent->initFromDB($this->agentid);
        $this->jobj = $jobj;
    }

    function initFromDB($state_id)
    {
        if ($this->getFromDB($state_id)) {
            $this->id    = $state_id;
            $this->agent = new PluginArmaditoAgent();
            $this->agent->initFromDB($this->fields["plugin_armadito_agents_id"]);
        } else {
            PluginArmaditoLog::Error("Unable to get State DB fields");
        }
    }

    function setAgentId($agentid_)
    {
        $this->agentid = PluginArmaditoToolbox::validateInt($agentid_);
    }

    static function getAvailableStatuses()
    {
        $colortbox = new PluginArmaditoColorToolbox();
        $palette   = $colortbox->getPalette(5);

        return array(
            "up-to-date" => $palette[0],
            "critical" => $palette[1],
            "late" => $palette[2],
            "non-available" => $palette[3]
        );
    }

    function toJson()
    {
        return '{}';
    }

    function getAgent()
    {
        return $this->agent;
    }

    function getSearchOptions()
    {
        $search_options = new PluginArmaditoSearchoptions('State');

        $items['Details']             = new PluginArmaditoSearchitemlink('id', 'glpi_plugin_armadito_statedetails', 'PluginArmaditoStatedetail');
        $items['Agent Id']            = new PluginArmaditoSearchitemlink('id', 'glpi_plugin_armadito_agents', 'PluginArmaditoAgent');
        $items['Update Status']       = new PluginArmaditoSearchtext('update_status', $this->getTable());
        $items['Last Update']         = new PluginArmaditoSearchtext('last_update', $this->getTable());
        $items['Antivirus']           = new PluginArmaditoSearchitemlink('fullname', 'glpi_plugin_armadito_antiviruses', 'PluginArmaditoAntivirus');
        $items['Antivirus On-access'] = new PluginArmaditoSearchtext('realtime_status', $this->getTable());
        $items['Antivirus On-demand'] = new PluginArmaditoSearchtext('service_status', $this->getTable());

        return $search_options->get($items);
    }

    function run()
    {
        if ($this->jobj->task->antivirus->name == "Armadito") {
            $this->insertOrUpdateStateModules();
        }

        $this->statedetails_id = $this->getTableIdForAgentId("glpi_plugin_armadito_statedetails");

        if ($this->isStateinDB()) {
            $this->updateState();
        } else {
            $this->insertState();
        }
    }

    function insertOrUpdateStateModules()
    {
        foreach ($this->jobj->task->obj->modules as $jobj_module) {
            $module = new PluginArmaditoStateModule();
            $module->init($this->agentid, $this->jobj, $jobj_module);
            $module->run();
        }
    }

    function isStateinDB()
    {
        global $DB;

        $query = "SELECT update_status FROM `glpi_plugin_armadito_states`
                 WHERE `plugin_armadito_agents_id`='" . $this->agentid . "'";
        $ret   = $DB->query($query);

        if (!$ret) {
            throw new InvalidArgumentException(sprintf('Error isStateinDB : %s', $DB->error()));
        }

        if ($DB->numrows($ret) > 0) {
            return true;
        }

        return false;
    }

    function getTableIdForAgentId($table)
    {
        global $DB;

        $id    = 0;
        $query = "SELECT id FROM `" . $table . "`
                 WHERE `plugin_armadito_agents_id`='" . $this->agentid . "'";

        $ret = $DB->query($query);

        if (!$ret) {
            throw new InvalidArgumentException(sprintf('Error getTableIdForAgentId : %s', $DB->error()));
        }

        if ($DB->numrows($ret) > 0) {
            $data = $DB->fetch_assoc($ret);
            $id   = $data["id"];
        }

        return $id;
    }

    function insertState()
    {
        $dbmanager = new PluginArmaditoDbManager();
        $params = $this->setCommonQueryParams();
        $query = "NewState";

        $dbmanager->addQuery($query, "INSERT", $this->getTable(), $params);
        $dbmanager->prepareQuery($query);
        $dbmanager->bindQuery($query);

        $this->antivirus = $this->agent->getAntivirus();
        $dbmanager = $this->setCommonQueryValues($dbmanager, $query);
        $dbmanager->executeQuery($query);

        $this->id = PluginArmaditoDbToolbox::getLastInsertedId();
        PluginArmaditoToolbox::validateInt($this->id);
    }

    function updateState()
    {
        $dbmanager = new PluginArmaditoDbManager();
        $params = $this->setCommonQueryParams();
        $query = "UpdateState";

        $dbmanager->addQuery($query, "UPDATE", $this->getTable(), $params, "plugin_armadito_agents_id");
        $dbmanager->prepareQuery($query);
        $dbmanager->bindQuery($query);

        $this->antivirus = $this->agent->getAntivirus();
        $dbmanager = $this->setCommonQueryValues($dbmanager, $query);
        $dbmanager->executeQuery($query);
    }

    function setCommonQueryParams()
    {
        $params["plugin_armadito_agents_id"]["type"]       = "i";
        $params["plugin_armadito_antiviruses_id"]["type"]  = "i";
        $params["plugin_armadito_statedetails_id"]["type"] = "i";
        $params["update_status"]["type"]                   = "s";
        $params["last_update"]["type"]                     = "s";
        $params["realtime_status"]["type"]                 = "s";
        $params["service_status"]["type"]                  = "s";
        return $params;
    }

    function setCommonQueryValues($dbmanager, $query)
    {
        $dbmanager->setQueryValue($query, "plugin_armadito_statedetails_id", $this->statedetails_id);
        $dbmanager->setQueryValue($query, "plugin_armadito_antiviruses_id", $this->antivirus->getId());
        $dbmanager->setQueryValue($query, "update_status", $this->jobj->task->obj->global_status);
        $dbmanager->setQueryValue($query, "last_update", date("Y-m-d H:i:s", $this->jobj->task->obj->global_update_timestamp));
        $dbmanager->setQueryValue($query, "realtime_status", "unknown");
        $dbmanager->setQueryValue($query, "service_status", "unknown");
        $dbmanager->setQueryValue($query, "plugin_armadito_agents_id", $this->agentid);
        return $dbmanager;
    }
}
?>
