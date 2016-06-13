<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Form Validation plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");


$pref = new PluginTicketcleanerUser();
if (isset($_POST["update"])) {

   if( isset($_POST['translationmode']) && $_POST['users_id'] == Session::getLoginUserID()) {
       $_SESSION['glpiticketcleanertranslationmode'] =   $_POST['translationmode'] ;
    }

    Html::back();
}

Html::redirect($CFG_GLPI["root_doc"]."/front/preference.php?forcetab=".
             urlencode('PluginTicketcleanerUser$1'));
