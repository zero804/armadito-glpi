<?php 

/* This file is part of ArmaditoPlugin.

ArmaditoPlugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

ArmaditoPlugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ArmaditoPlugin.  If not, see <http://www.gnu.org/licenses/>.

*/

// ----------------------------------------------------------------------
// Original Author of file: Valentin HAMON
// Purpose of file: 
// ----------------------------------------------------------------------

// Hook called on profile change
// Good place to evaluate the user right on this plugin
// And to save it in the session
function plugin_change_profile_armadito() {
   // For example : same right of computer
   if (Session::haveRight('computer','w')) {
      $_SESSION["glpi_plugin_armadito_profile"] = array('armadito' => 'w');

   } else if (Session::haveRight('computer','r')) {
      $_SESSION["glpi_plugin_armadito_profile"] = array('armadito' => 'r');

   } else {
      unset($_SESSION["glpi_plugin_armadito_profile"]);
   }
}


function plugin_armadito_install() {
    return true;
}

function plugin_armadito_uninstall() {
    return true;
}

?>
