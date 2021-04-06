<?php
/*
 * -------------------------------------------------------------------------
Ticket Cleaner plugin
Copyright (C) 2016-2021 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Ticket Cleaner plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");


Html::header(__('Ticket Cleaner', 'ticketcleaner'), $_SERVER['PHP_SELF'], "config", "PluginTicketcleanerMenu", "ticketcleanerfilter");

if (Session::haveRight("config", UPDATE)) {

   Search::show('PluginTicketcleanerFilter');

} else {
   Html::displayRightError();
}
Html::footer();

