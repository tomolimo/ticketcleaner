<?php
/*
 *
*  */

// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// Purpose of file: script to be used to purge logos from DB
// ----------------------------------------------------------------------

// Ensure current directory as run command prompt
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));


define('DO_NOT_CHECK_HTTP_REFERER', 1);
define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

include_once( 'hook.php' ) ;  
loadSha1IntoDB() ; // to be sure sha1 are up to date

// get hashes from DB
$query = "SELECT * FROM  `glpi_plugin_ticketcleaner_picturehashes` ;" ;
$res = $DB->query($query) ;

foreach ($DB->request($query) as $file_hash){
	// search if this document is already in the DB
	
	$doc = new Document();
	
	$criteria = array('sha1sum'     => $file_hash['hash'] );
	
	foreach ($DB->request($doc->getTable(), $criteria) as $data) {
		$doc->fields = $data;
		echo "Document: ".$doc->fields['users_id']." - ".$doc->fields['id']." - ".$doc->fields['tickets_id']." - ".$doc->fields['name']." - ".$doc->fields['filename']." - ".$doc->fields['filepath'];
		if( $doc->fields['users_id'] == 0 ){ // 0 means cron_mail_ate
			if( $doc->deleteFromDB(1) ) {
				// logs history
				$changes[0] = 0;
				$changes[1] = $changes[2] = "";				
				Log::history($doc->fields["id"], $doc->getType(), $changes, 0,	Log::HISTORY_DELETE_ITEM);
				echo " --> Deleted\n";
			}
			else 
				echo " --> Not Deleted\n" ;
		}else
			echo " --> Kept\n";
				
	}
		
}




?>