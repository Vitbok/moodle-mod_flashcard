<?PHP

function xmldb_flashcard_upgrade($oldversion = 0) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    /* @var $DB mysqli_native_moodle_database */
    global $CFG, $DB;
    
    /* @var $dbman database_manager */
    $dbman = $DB->get_manager();
    $result = true;

//===== 1.9.0 upgrade line ======//

    // this should patch the question_match anyway

    $table = new xmldb_table('question_match');
    $field = new xmldb_field('numquestions');
    $field->set_attributes (XMLDB_TYPE_INTEGER, '10', 'true', 'true', null, null, null, '0');
    if (!$dbman->field_exists($table, $field)){
        $dbman->add_field($table, $field, true, true);
    }

    if ($oldversion < 2008050400){
    
    /// Define field starttime to be added to flashcard
        $table = new xmldb_table('flashcard');

    /// Launch add field starttime
        $field = new xmldb_field('starttime');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, null, null, 'timemodified');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field endtime
        $field = new xmldb_field('endtime');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, null, null, 'starttime');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field autodowngrade
        $field = new xmldb_field('autodowngrade');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1, 'questionid');
        $result = $result && $dbman->add_field($table, $field);
        
    /// Launch add field deck2_release
        $field = new xmldb_field('deck2_release');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 96, 'autodowngrade');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field deck3_release
        $field = new xmldb_field('deck3_release');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 96, 'deck2_release');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field deck1_delay
        $field = new xmldb_field('deck1_delay');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 48, 'deck3_release');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field deck2_delay
        $field = new xmldb_field('deck2_delay');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 96, 'deck1_delay');
        $result = $result && $dbman->add_field($table, $field);    

    /// Launch add field deck3_delay
        $field = new xmldb_field('deck3_delay');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 168, 'deck2_delay');
        $result = $result && $dbman->add_field($table, $field);

    /// Define table flashcard_card to be created
        $table = new xmldb_table('flashcard_card');

    /// Adding fields to table flashcard_card
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field('flashcardid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->add_field('entryid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->add_field('deck', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->add_field('lastaccessed', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, null, '0');

    /// Adding keys to table flashcard_card
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for flashcard_card
        $result = $result && $dbman->create_table($table);
    }

    if ($oldversion < 2008050500){
    
    /// Define field starttime to be added to flashcard
        $table = new xmldb_table('flashcard');

    /// Launch add field deck4_release
        $field = new xmldb_field('deck4_release');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 96, 'deck3_release');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field deck4_delay
        $field = new xmldb_field('deck4_delay');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 336, 'deck3_delay');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field questionsasimages
        $field = new xmldb_field('questionsasimages');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'deck4_delay');
        $result = $result && $dbman->add_field($table, $field);

    /// Launch add field answersasimages
        $field = new xmldb_field('answersasimages');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'questionsasimages');
        $result = $result && $dbman->add_field($table, $field);
    }

    if ($oldversion < 2008050501){
    
    /// Define field starttime to be added to flashcard
        $table = new xmldb_table('flashcard');

    /// Launch add field decks
        $field = new xmldb_field('decks');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '3', 'autodowngrade');
        $result = $result && $dbman->add_field($table, $field);    
    }

    if ($result && $oldversion < 2008050800) {

    /// Define table flashcard_deckdata to be created
        $table = new xmldb_table('flashcard_deckdata');

    /// Adding fields to table flashcard_deckdata
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field('flashcardid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field('questiontext', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);
        $table->add_field('answertext', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);

    /// Adding keys to table flashcard_deckdata
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for flashcard_deckdata
        $result = $result && $dbman->create_table($table);
    }

    if ($result && $oldversion < 2008050900) {

    /// Define field accesscount to be added to flashcard_card
        $table = new xmldb_table('flashcard_card');
        $field = new xmldb_field('accesscount');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'lastaccessed');

    /// Launch add field accesscount
        $result = $result && $dbman->add_field($table, $field);
    }

    if ($result && $oldversion < 2008051100) {

    /// Rename field questionsasimages on table flashcard to questionsmediatype
        $table = new xmldb_table('flashcard');
        $field = new xmldb_field('questionsasimages');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'deck4_delay');

    /// Launch rename field questionsmediatype
        $result = $result && $dbman->rename_field($table, $field, 'questionsmediatype');

    /// Rename field answersasimages on table flashcard to answersmediatype
        $table = new xmldb_table('flashcard');
        $field = new xmldb_field('answersasimages');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'deck4_delay');

    /// Launch rename field questionsmediatype
        $result = $result && $dbman->rename_field($table, $field, 'answersmediatype');

    /// Define field flipdeck to be added to flashcard
        $table = new xmldb_table('flashcard');
        $field = new xmldb_field('flipdeck');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0', 'answersmediatype');
        $result = $result && $dbman->add_field($table, $field);

       upgrade_mod_savepoint(true, 2008051100, 'flashcard');
    }

//===== 2.0.0 upgrade line ======//

    if ($result && $oldversion < 2012040200) {
    /// Rename summary into intro and	summaryformat into introformat
       $table = new xmldb_table('flashcard');

       $field = new xmldb_field('summary');
       $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'name');
       $dbman->rename_field($table, $field, 'intro');

       $field = new xmldb_field('summaryformat');
       $field->set_attributes( XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');
       $dbman->rename_field($table, $field, 'introformat');
       $DB->execute("UPDATE {flashcard} SET introformat=1");

    /// Workaround for MDL-26469
       $record = $DB->get_record('modules', array('name'=>'flashcard'));
       $record->cron = 3600;
       $DB->update_record('modules', $record);
       
       upgrade_mod_savepoint(true, 2012040200, 'flashcard');
    }

    if ($result && $oldversion < 2012040201) {
       	$table = new xmldb_table('flashcard');
		
       	$field = new xmldb_field('audiostart');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'answersmediatype');
	       	$dbman->add_field($table, $field);
	    }

       	upgrade_mod_savepoint(true, 2012040201, 'flashcard');
    }

    if ($result && $oldversion < 2012040202) {
       	$table = new xmldb_table('flashcard');
		
       	$field = new xmldb_field('custombackfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'flipdeck');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('customfrontfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'custombackfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('customemptyfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'customfrontfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('customreviewfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'customemptyfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('customreviewedfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'customreviewfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('customreviewemptyfileid');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'customreviewedfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('completionallviewed');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'customreviewemptyfileid');
	       	$dbman->add_field($table, $field);
	    }

       	$field = new xmldb_field('completionallgood');
		if (!$dbman->field_exists($table, $field)){
	       	$field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'completionallviewed');
	       	$dbman->add_field($table, $field);
	    }
       	upgrade_mod_savepoint(true, 2012040202, 'flashcard');
    }
    
    return true;
}

?>
