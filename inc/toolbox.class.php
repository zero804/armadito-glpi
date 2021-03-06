<?php

/*
Copyright (C) 2010-2016 by the FusionInventory Development Team.
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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginArmaditoToolbox
{
    static function ExecQuery($query)
    {
        global $DB;

        if ($DB->query($query)) {
            return true;
        } else {
            PluginArmaditoLog::Error($query ." :". $DB->error());
            return false;
        }
    }

    static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        if(!$d || !($d->format($format) == $date)) {
            throw new InvalidArgumentException(sprintf('Invalid Date format ('.$format.') : "%s"', $date));
        }
    }

    static function validateInt($var)
    {
        $ret = filter_var($var, FILTER_VALIDATE_INT);
        if ($ret != $var) {
            throw new InvalidArgumentException(sprintf('Invalid int : "%s"', $var));
        }
        return $ret;
    }

    static function validateKey($var)
    {
        $ret = filter_var($var, FILTER_VALIDATE_REGEXP, array(
            "options" => array(
                "regexp" => "/^[A-Z0-9]{5}\-[A-Z0-9]{5}\-[A-Z0-9]{5}\-[A-Z0-9]{5}\-[A-Z0-9]{5}$/"
            )
        ));
        if ($ret != $var) {
            throw new InvalidArgumentException(sprintf('Invalid Key format : "%s"', $var));
        }
        return $ret;
    }

    static function validateUUID($var)
    {
        $ret = filter_var($var, FILTER_VALIDATE_REGEXP, array(
            "options" => array(
                "regexp" => "/^[A-Z0-9]{8}\-[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{4}\-[A-Z0-9]{12}$/"
            )
        ));
        if ($ret != $var) {
            throw new InvalidArgumentException(sprintf('Invalid UUID : "%s"', $var));
        }
        return $ret;
    }

    static function parseJSON($json_content)
    {
        $jobj = json_decode($json_content);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $jobj;
        } else {
            throw new PluginArmaditoJsonException(sprintf("Json parsing error : %s", json_last_error_msg()));
        }
    }

    static function formatDuration($duration)
    {
        if(preg_match('/^(\d{2}):(\d{2}):(\d{2})$/i', $duration, $matches)) {
            $duration = 'PT'.$matches[1].'H'.$matches[2].'M'.$matches[3].'S';
        }

        return static::FormatISO8601DateInterval($duration);
    }

    static function formatDate($date)
    {
        if(preg_match('/^(\d{10})$/i', $date, $matches)) {
           return static::Timestamp_to_MySQLDateTime($date);
        }
        else {
           throw new InvalidArgumentException(sprintf('Date is not a valid unix timestamp : %s"', $date));
        }
    }

    static function computeDuration($timestamp_start, $timestamp_end)
    {
        $datetime_start = new DateTime();
        $datetime_end = new DateTime();

        $datetime_start->setTimestamp($timestamp_start);
        $datetime_end->setTimestamp($timestamp_end);

        $duration = $datetime_end->diff($datetime_start);
        return $duration->format('%dd %Hh %Im %Ss');
    }

    static function DurationToSeconds($duration)
    {
        if(preg_match('/^(\d+?)d (\d{2}?)h (\d{2}?)m (\d{2}?)s$/i', $duration, $matches)) {
            return $matches[1]*86400 + $matches[2]*3600 + $matches[3]*60 + $matches[4];
        }

        return -1;
    }

    static function Timestamp_to_MySQLDateTime($timestamp) {
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        return $datetime->format("Y-m-d H:i:s");
    }

    static function MySQLDateTime_to_Timestamp($ISO8601_datetime) {
        $datetime = new DateTime($ISO8601_datetime);
        return $datetime->getTimestamp();
    }

    static function getDayOfYearFromISO8601DateTime($ISO8601_datetime) {
        $datetime = new DateTime($ISO8601_datetime);
        return $datetime->format("z");
    }

    static function getHourFromISO8601DateTime($ISO8601_datetime) {
        $datetime = new DateTime($ISO8601_datetime);
        return $datetime->format("G");
    }

    static function FormatISO8601DateInterval($ISO8601_dateinterval) {
        $interval = new DateInterval($ISO8601_dateinterval);
        return $interval->format('%dd %Hh %Im %Ss');
    }

   static function showOutputFormat() {
      global $CFG_GLPI;

      $values['-'.Search::PDF_OUTPUT_LANDSCAPE] = __('All pages in landscape PDF');
      $values['-'.Search::SYLK_OUTPUT]          = __('All pages in SLK');
      $values['-'.Search::CSV_OUTPUT]           = __('All pages in CSV');

      Dropdown::showFromArray('display_type', $values);
      echo "<input type='image' name='export' class='pointer' src='".
             $CFG_GLPI["root_doc"]."/pics/export.png' title=\""._sx('button', 'Export')."\" value=\"".
             _sx('button', 'Export')."\">";
   }
}
?>
