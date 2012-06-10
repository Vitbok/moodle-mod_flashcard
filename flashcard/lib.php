<?PHP  // $Id: lib.php,v 1.1 2011-10-15 12:25:56 vf Exp $

/**
* Library of functions and constants for module flashcard
* @package mod-flashcard
* @category mod
* @author Gustav Delius
* @contributors Valery Fremaux
*/

/**
* Includes and requires
*/
if (file_exists($CFG->libdir.'/filesystemlib.php')){
    require_once($CFG->libdir.'/filesystemlib.php');
} else {
    require_once($CFG->dirroot.'/mod/flashcard/filesystemlib.php');
}
require_once($CFG->dirroot.'/lib/ddllib.php');
require_once($CFG->dirroot.'/mod/flashcard/locallib.php');

// patch the question match if it hasn't be done
$table = new XMLDBTable('question_match');
$field = new XMLDBField('numquestions');
$field->setAttributes (XMLDB_TYPE_INTEGER, '10', 'true', 'true', null, null, null, '0');
if (!field_exists($table, $field)){
    add_field($table, $field, true, true);
}

/**
* Given an object containing all the necessary data, 
* (defined by the form in mod.html) this function 
* will create a new instance and return the id number 
* of the new instance.
* @uses $COURSE
*/
function flashcard_add_instance($flashcard) {
    global $COURSE;

    $flashcard->timemodified = time();
    
    if (!isset($flashcard->starttimeenable)){
        $flashcard->starttime = 0;
    }

    if (!isset($flashcard->endtimeenable)){
        $flashcard->endtime = 0;
    }

    $newid = insert_record('flashcard', $flashcard);

    // Make physical repository for customisation
    filesystem_create_dir($COURSE->id.'/moddata/flashcard/'.$newid, FS_RECURSIVE);

    // Import all information from question
    if (isset($flashcard->forcereload) && $flashcard->forcereload){
        flashcard_import($flashcard);
    }

    return $newid;
}

/**
* Given an object containing all the necessary data, 
*(defined by the form in mod.html) this function 
* will update an existing instance with new data.
*
*/
function flashcard_update_instance($flashcard) {
    global $COURSE;

    $flashcard->timemodified = time();
    $flashcard->id = $flashcard->instance;
    
    // Make physical repository for customisation
    if (!file_exists($COURSE->id.'/moddata/flashcard/'.$flashcard->id)){
        filesystem_create_dir($COURSE->id.'/moddata/flashcard/'.$flashcard->id, FS_RECURSIVE);
    }

    // update first deck with questions that might be added
    
    if (isset($flashcard->forcereload) && $flashcard->forcereload){
        flashcard_import($flashcard);        
    }

    if (!isset($flashcard->starttimeenable)){
        $flashcard->starttime = 0;
    }

    if (!isset($flashcard->endtimeenable)){
        $flashcard->endtime = 0;
    }

    return update_record('flashcard', $flashcard);
}

/**
* Given an ID of an instance of this module, 
* this function will permanently delete the instance 
* and any data that depends on it.  
* @uses $COURSE
*/
function flashcard_delete_instance($id) {
    global $COURSE;

    // clear anyway what remains here
    filesystem_clear_dir($COURSE->id.'/moddata/flashcard/'.$id, FS_FULL_DELETE);

    if (! $flashcard = get_record('flashcard', 'id', $id)) {
        return false;
    }

    $result = true;

    // Delete any dependent records here
          
    delete_records('flashcard_card', 'flashcardid', $flashcard->id);

    if (! delete_records('flashcard', 'id', $flashcard->id)) {
        $result = false;
    }

    return $result;
}

/**
* Return a small object with summary information about what a 
* user has done with a given particular instance of this module
* Used for user activity reports.
* $return->time = the time they did it
* $return->info = a short text description
*/
function flashcard_user_outline($course, $user, $mod, $flashcard) {
    $return = false;
    return $return;
}

/**
* Print a detailed representation of what a  user has done with 
* a given particular instance of this module, for user activity reports.
*/
function flashcard_user_complete($course, $user, $mod, $flashcard) {
    return true;
}

/**
* Given a course and a time, this module should find recent activity 
* that has occurred in flashcard activities and print it out. 
* Return true if there was output, or false is there was none.
* @uses $CFG
*/
function flashcard_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
* Function to be run periodically according to the moodle cron
* This function searches for things that need to be done, such 
* as sending out mail, toggling flags etc ... 
* @uses $CFG
*
*/
function flashcard_cron () {
    global $CFG;
    
    // get all flashcards
    $flashcards = get_records('flashcard');
    
    foreach($flashcards as $flashcard){
        if (!$flashcard->autodowngrade) continue;
        if ($flashcard->starttime != 0 && time() < $flashcard->starttime) continue;
        if ($flashcard->endtime != 0 && time() > $flashcard->endtime) continue;
        
        $cards = get_records_select('flashcard_card', "flashcardid = $flashcard->id AND deck > 1");
        foreach($cards as $card){
            // downgrades to deck 3 (middle low)
            if ($flashcard->decks > 3){
                if ($card->deck == 4 && time() > $card->lastaccessed + ($flashcard->deck4_delay * HOURSECS + $flashcard->deck4_release * HOURSECS)){
                    set_field('flashcard_card', 'deck', 3, 'id', $card->id);
                }
            }
            // downgrades to deck 2 (middle)
            if ($flashcard->decks > 2){
                if ($card->deck == 3 && time() > $card->lastaccessed + ($flashcard->deck3_delay * HOURSECS + $flashcard->deck3_release * HOURSECS)){
                    set_field('flashcard_card', 'deck', 2, 'id', $card->id);
                }
            }
            // downgrades to deck 1 (difficult)
            if ($card->deck == 2 && time() > $card->lastaccessed + ($flashcard->deck2_delay * HOURSECS + $flashcard->deck2_release * HOURSECS)){
                set_field('flashcard_card', 'deck', 1, 'id', $card->id);
            }
        }
    }

    return true;
}

/**
* Must return an array of grades for a given instance of this module, 
* indexed by user.  It also returns a maximum allowed grade.
*
*    $return->grades = array of grades;
*    $return->maxgrade = maximum allowed grade;
*
*    return $return;
*/
function flashcard_grades($flashcardid) {
   return NULL;
}

/**
* Must return an array of user records (all data) who are participants
* for a given instance of flashcard. Must include every user involved
* in the instance, independient of his role (student, teacher, admin...)
* See other modules as example.
*/
function flashcard_get_participants($flashcardid) {

     $userids = get_records_menu('flashcard_card', 'flashcardid', $flashcardid, '', 'userid,id');
     $useridlist = implode(",", array_keys($userids));
     
     $users = get_records_list('user', 'id', $useridlist);

     if (!empty($users)) return $users;

     return false;
}

/**
* This function returns if a scale is being used by one flashcard
* it it has support for grading and scales. Commented code should be
* modified if necessary. See forum, glossary or journal modules
* as reference.
*/
function flashcard_scale_used ($flashcardid, $scaleid) {
   
    $return = false;

    //$rec = get_record("flashcard","id","$flashcardid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other flashcard functions go here.  Each of them must have a name that 
/// starts with flashcard_

/// See locallib.phpfor additional primitives.


?>