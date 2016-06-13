<?php
/*
 * @version $Id$
-------------------------------------------------------------------------
GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2015 Teclib'.

http://glpi-project.org

based on GLPI - Gestionnaire Libre de Parc Informatique
Copyright (C) 2003-2014 by the INDEPNET Development Team.

-------------------------------------------------------------------------

LICENSE

This file is part of GLPI.

GLPI is free software; you can redistribute it and/or modify
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

/** @file
 * @brief
 */

include ('../../../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = '';
}

Session::checkLoginUser();

$filter = new PluginTicketcleanerFilter();
if (isset($_POST["add"])) {
   $filter->check(-1, CREATE, $_POST);

   $newID = $filter->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($filter->getFormURL()."?id=".$newID);
   } else {
      Html::back();
   }

} else if (isset($_POST["purge"])) {
   $filter->check($_POST["id"], PURGE);
   $filter->delete($_POST,1);

   $filter->redirectToList();

} else if (isset($_POST["update"])) {

   $filter->check($_POST["id"], UPDATE);

   $filter->update($_POST);

   Html::back();

} else {
   Html::header(__('Ticket Cleaner','ticketcleaner'), $_SERVER['PHP_SELF'] , "config", "PluginTicketcleanerMenu", "ticketcleanerfilter");
   $filter->display($_GET);
   Html::footer();
}
?>
