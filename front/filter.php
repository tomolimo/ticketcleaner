<?php
include ("../../../inc/includes.php");


Html::header(__('Ticket Cleaner','ticketcleaner'), $_SERVER['PHP_SELF'] , "config", "PluginTicketcleanerMenu", "ticketcleanerfilter");

if (Session::haveRight("config", UPDATE)) {
   
   Search::show('PluginTicketcleanerFilter');

} else {
   Html::displayRightError();
}
Html::footer();

?>