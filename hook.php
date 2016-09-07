<?php

// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// Purpose of file: Provides cleaning of Ticket, for title, description
//                  and followups.
//                  It cleans text for creation and edition (from email or from web interface)
//                  and it cleans attached pictures to emails
//                  It has been succesfully tested with plain TEXT and HTML emails
// ----------------------------------------------------------------------

/**
 * Summary of loadSha1IntoDB
 * This function checks if an update of SHA is mandatory
 * The check is based on last modification date of folder 'pictures'
 * If last modification date is more recent than the saved date in DB,
 * then a refreh is started.
 *
 * Will save the file name and SHA into DB, in table 'glpi_plugin_ticketcleaner_picturehashes'
 * for each file found in the 'pictures' folder
 */
function loadSha1IntoDB(){
	global $DB;

	// now fill the table
	// get files from plugin pictures folder
	$dir = GLPI_ROOT . "/plugins/ticketcleaner/pictures" ;
	$files = scandir( $dir ) ;

	$lastupdate ="" ;
	$query = "SELECT * FROM `glpi_plugin_ticketcleaner_picturehashes_lastupdate`;" ;
	$res = $DB->query($query) ;
	if($DB->numrows($res) > 0) {
		$row = $DB->fetch_array($res) ;
		$lastupdate = $row['lastupdate'];
	}

	$stats = stat($dir);
	$datetime = date( "YmdHis", $stats['mtime'] );

	if( $datetime > $lastupdate ) { // means picture folder has been modified since last update
		$DB->query("TRUNCATE TABLE glpi_plugin_ticketcleaner_picturehashes") ; //or die("error on 'truncate' glpi_plugin_ticketcleaner_picturehashes ". $DB->error()) ;
		// compute hash for each file and then insert it in DB with REPLACE INTO to prvent double entries
		foreach( $files as $pict ){
			if($pict <> "." && $pict <> "..") {
				$sha = sha1_file( $dir."/".$pict ) ;
				$query = "INSERT INTO `glpi_plugin_ticketcleaner_picturehashes` (`hash`, `filename`) VALUES ('".$sha."', '".$pict."');" ;
				$DB->query($query) or die("error on 'insert' into glpi_plugin_ticketcleaner_picturehashes with ".$pict." hash: ". $DB->error());
			}
		}

		if( count($files) )
			Toolbox::logInFile('TicketCleaner', "Loading of sha1 files from '".$dir."' into DB done.\n" ) ;
		else
			Toolbox::logInFile('TicketCleaner', "No files in '".$dir."'.\n" ) ;

		// update of lastupdate into DB, with $datetime
		$query = "REPLACE INTO `glpi_plugin_ticketcleaner_picturehashes_lastupdate` SET lastupdate='".$datetime."', id=1;" ;
		$res = $DB->query($query) ;

	}
}

/**
 * Summary of plugin_ticketcleaner_install
 * Installs plugin into current GLPI instance
 * Creates DB tables and loads initial SHA of pictures into DB
 * @return true or 'die'
 */
function plugin_ticketcleaner_install() {
	global $DB ;

	if (!TableExists("glpi_plugin_ticketcleaner_picturehashes_lastupdate")) {
		$query = "CREATE TABLE `glpi_plugin_ticketcleaner_picturehashes_lastupdate` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`lastupdate` VARCHAR(50) NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;
			";

		$DB->query($query) or die("error creating glpi_plugin_ticketcleaner_picturehashes_lastupdate " . $DB->error());
	}


	if (!TableExists("glpi_plugin_ticketcleaner_picturehashes")) {
		$query = "CREATE TABLE `glpi_plugin_ticketcleaner_picturehashes` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`hash` CHAR(40) NOT NULL,
				`filename` VARCHAR(255) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `hash` (`hash`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;
			";

		$DB->query($query) or die("error creating glpi_plugin_ticketcleaner_picturehashes " . $DB->error());
	}

	loadSha1IntoDB() ; // also done on the fly

   if( TableExists("backup_glpi_plugin_ticketcleaner_filters") ) {
      $query = "DROP TABLE `backup_glpi_plugin_ticketcleaner_filters`;";
      $DB->query($query) or die("error droping old backup_glpi_plugin_ticketcleaner_filters" . $DB->error());
   }

	if( TableExists("glpi_plugin_ticketcleaner_filters") && FieldExists( 'glpi_plugin_ticketcleaner_filters', 'filter' ) ) {

      $query = "RENAME TABLE `glpi_plugin_ticketcleaner_filters` TO `backup_glpi_plugin_ticketcleaner_filters`;";
      $DB->query($query) or die("error renaming glpi_plugin_ticketcleaner_filters to backup_glpi_plugin_ticketcleaner_filters" . $DB->error());
   }

	if (!TableExists("glpi_plugin_ticketcleaner_filters")) {
      $query = "
            CREATE TABLE `glpi_plugin_ticketcleaner_filters` (
	                  `id` INT(11) NOT NULL AUTO_INCREMENT,
	                  `name` VARCHAR(255) NOT NULL,
	                  `type` INT(1) NOT NULL DEFAULT '1',
	                  `order` INT(11) NULL,
	                  `regex` TEXT NOT NULL,
	                  `replacement` TEXT NOT NULL,
	                  `is_active` INT(1) NOT NULL DEFAULT '0',
	                  `comment` TEXT NULL,
	                  `date_mod` TIMESTAMP NULL DEFAULT NULL,
	                  PRIMARY KEY (`id`),
	                  INDEX `type` (`type`),
                     INDEX `order` (`order`)
                  )
                  COLLATE='utf8_general_ci'
                  ENGINE=InnoDB
                  ;";

		$DB->query($query) or die("error creating glpi_plugin_ticketcleaner_filters " . $DB->error());
   } else {
      // change regex and replacement field type
      $fields = $DB->list_fields( 'glpi_plugin_ticketcleaner_filters' ) ;
      if( strcasecmp( $fields['regex']['Type'], 'text' ) != 0) {

         $query = "ALTER TABLE `glpi_plugin_ticketcleaner_filters`
                  ALTER `regex` DROP DEFAULT,
                  ALTER `replacement` DROP DEFAULT;";
         $DB->query($query) or die("error droping defaults for 'regex' and 'replacement' in glpi_plugin_ticketcleaner_filters " . $DB->error());

         $query = "ALTER TABLE `glpi_plugin_ticketcleaner_filters`
                  CHANGE COLUMN `order` `order` INT(11) NULL AFTER `type` ,
                  CHANGE COLUMN `regex` `regex` TEXT NOT NULL AFTER `order` ,
                  CHANGE COLUMN `replacement` `replacement` TEXT NOT NULL AFTER `regex`;" ;
         $DB->query($query) or die("error changing type of 'regex' and 'replacement' in glpi_plugin_ticketcleaner_filters " . $DB->error());
      }
   }

	return true;
}


/**
 * Summary of plugin_ticketcleaner_uninstall
 * Drop tables containing SHA and last update date,
 * but keeps the filters
 * @return true or 'die'!
 */
function plugin_ticketcleaner_uninstall() {
	global $DB;

	// Current version tables
	if (TableExists("glpi_plugin_ticketcleaner_picturehashes")) {
		$query = "DROP TABLE `glpi_plugin_ticketcleaner_picturehashes`";
		$DB->query($query) or die("error deleting glpi_plugin_ticketcleaner_picturehashes");
	}

	if (TableExists("glpi_plugin_ticketcleaner_picturehashes_lastupdate")) {
		$query = "DROP TABLE `glpi_plugin_ticketcleaner_picturehashes_lastupdate`";
		$DB->query($query) or die("error deleting glpi_plugin_ticketcleaner_picturehashes_lastupdate");
	}


	return true;
}


/**
 * Summary of PluginTicketCleaner
 * This class manages cleaning of:
 *      text and images from ticket creation
 *      text only for ticket update
 */
class PluginTicketCleaner {

	/**
	 * Summary of cleanText
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
     * loads filters from DB and applies them to object name and content.
     * Filters are divided into a type and an order
     * types (0 - 3):
     *      0 - 1: filters of this type are used to delete any signature from content of ticket or content of follow-ups
     *          0: regex for begin of signature
     *          1: regex for end of signature
     *      2    : filters of this type are used to delete (or replace) any text that will match a regex from content of Tickets or content of Followups
     *      3    : filters of this type are used to delete (or replace) any text that will match a regex from name (=title) of Tickets
     * orders (0 - n): used to apply filters in this defined order
     * see filter examples in Plugin website
	 */
	public static function cleanText($parm) {
		global $DB ;

		$is_content =  array_key_exists('content', $parm->input) ;
		$is_name = array_key_exists('name', $parm->input) ;

		if( $is_content || $is_name ) {
			// load filters from DB
			$filters = array( ) ;
			$query = "SELECT * FROM glpi_plugin_ticketcleaner_filters WHERE is_active=1 ORDER BY type, `order`;" ;

			// preparation for starts of filter
			foreach ($DB->request($query) as $filter){
            $filters[ $filter['type'] ][] = $filter ;
			}

		   if( $is_content && isset($filters[ PluginTicketcleanerFilter::DESCRIPTION_TYPE ])) {
            $temp_content = html_entity_decode( $parm->input['content'], ENT_QUOTES, 'UTF-8');
            if( isset($_SESSION['glpi_use_mode']) && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE)) {
               Toolbox::logInFile('TicketCleaner', "\tInitial text content: " . $temp_content . "\n" ) ;
            }
			   foreach($filters[ PluginTicketcleanerFilter::DESCRIPTION_TYPE ] as $ptn){
               $temp_content = preg_replace( $ptn['regex'], $ptn['replacement'], $temp_content );
               if( isset($_SESSION['glpi_use_mode']) && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE)) {
                  Toolbox::logInFile('TicketCleaner', "\tAfter filter: " . $ptn['name'] . "\t text: " . $temp_content . "\n" ) ;
               }
			   }
            $parm->input['content'] = htmlentities( $temp_content, ENT_QUOTES, 'UTF-8') ;
		   }

		   if( $is_name && isset($filters[ PluginTicketcleanerFilter::TITLE_TYPE ]) ) {
			   foreach($filters[ PluginTicketcleanerFilter::TITLE_TYPE ] as $ptn){
				   $parm->input['name'] = preg_replace( $ptn['regex'], $ptn['replacement'], $parm->input['name'] ) ;
			   }
		   }

      }
	}

	/**
	 * Summary of cleanImages
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
     * For each picture a log is written into TicketCleaner log file to indicate
     * if it has been deleted or not
	 */
	public static function cleanImages($parm){
		global $DB;

		// this ticket has been created via email receiver.
		// has any FILE attached to it?
		if( array_key_exists('name', $parm->input)
			&& array_key_exists('_mailgate', $parm->input)
			&& array_key_exists('_filename', $parm->input)
			&& is_array($parm->input['_filename']) ) {

			// if necessary will reload sha1
			loadSha1IntoDB() ;

			$msg_log = "Ticket: '".$parm->input['name']."'\n";

			// signature FILES are deleted from array $parm->input['_filename']

			// load pictures signatures from DB
			$files_hash = array( ) ;
			$query = "SELECT hash FROM glpi_plugin_ticketcleaner_picturehashes" ;

			foreach ($DB->request($query) as $data){
				$files_hash[] = $data['hash'] ;
			}

			foreach( $parm->input['_filename'] as $loc_key => $loc_file) {
            $loc_file = GLPI_TMP_DIR. "/$loc_file" ;
				$loc_type = Toolbox::getMime( $loc_file ) ;
				$loc_sha = "";
				$loc_deleted = false ;
				if( stripos( $loc_type, "IMAGE/") !== false){
					$loc_sha = sha1_file( $loc_file ) ;

					if( in_array($loc_sha, $files_hash) ) {
						unset($parm->input['_filename'][$loc_key]) ;
						unlink($loc_file);
						$loc_deleted = true ;
					}
				}
				if( $loc_sha <> "" )
					$msg_log .= "\tFile: '".$loc_file."'\ttype: '".$loc_type."'\tsha1: '".$loc_sha."'\tdeleted: '".($loc_deleted?"True":"False")."'\n" ;
				else
					$msg_log .= "\tFile: '".$loc_file."'\ttype: '".$loc_type."'\n" ;
			}

			Toolbox::logInFile('TicketCleaner', $msg_log ) ;

		}
	}

	/**
	 * Summary of plugin_pre_item_add_ticketcleaner
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
	 */
	public static function plugin_pre_item_add_ticketcleaner($parm) {
		global $DB, $GLOBALS ;

		PluginTicketCleaner::cleanText($parm) ;

		PluginTicketCleaner::cleanImages($parm) ;

	}

	/**
	 * Summary of plugin_pre_item_add_ticketcleaner_followup
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
	 */
	public static function plugin_pre_item_add_ticketcleaner_followup($parm) {
		global $DB ;

		PluginTicketCleaner::cleanText($parm) ;

		PluginTicketCleaner::cleanImages($parm) ;

	}

	/**
	 * Summary of plugin_pre_item_update_ticketcleaner
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
	 */
	public static function plugin_pre_item_update_ticketcleaner($parm) {
		PluginTicketCleaner::cleanText($parm) ;
	}

	/**
	 * Summary of plugin_pre_item_update_ticketcleaner_followup
     * @param $parm contains current object (i.e. a Ticket or a TicketFollowup)
	 */
	public static function plugin_pre_item_update_ticketcleaner_followup($parm) {
		PluginTicketCleaner::cleanText($parm) ;
	}

}

?>