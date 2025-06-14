<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * 
 * Libary for teletask module.
 * 
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds a teletask instance
 *
 * This is done by calling the add_instance() method of the assignment type class
 * @param stdClass $data
 * @param mod_assign_mod_form $form
 * @return int The instance id of the new assignment
 */
function teletask_add_instance($teletask) {
    global $DB;

    $teletask->timemodified = time();

    if (isset($_POST["sections"])) {
        $count = 0;
        $sections = array();
        foreach (required_param_array("sections", PARAM_NOTAGS) as $section) {
            $sections[$count] = $section;
            $count++;
        }

        $count = 0;
        $times = array();
        foreach (required_param_array("sectiontimes", PARAM_INT) as $time) {
            $times[$count] = $time;
            $count++;
        }

        $id = $DB->insert_record("teletask", $teletask);

        $sectiondb = new stdClass();
        $sectiondb->video_id = $id;
        for ($i = 0; $i < count($sections); $i++) {
            $sectiondb->name = $sections[$i];
            $sectiondb->time = $times[$i];

            $DB->insert_record("teletask_sections", $sectiondb);
        }
    } else {
        $id = $DB->insert_record("teletask", $teletask);
    }

    return $id;
}

/**
 * Update a teletask instance
 *
 * This is done by calling the update_instance() method of the assignment type class
 * @param stdClass $teletask Object of teletask activity
 * @return int The instance id of the new assignment
 */
function teletask_update_instance($teletask) {
    global $DB;

    $teletask->id = $teletask->instance;
    $teletask->timemodified = time();

    // Update Sections (Remove and add again).
    // Remove.
    $DB->delete_records("teletask_sections", array("video_id" => "$teletask->id"));
    // Add.
    if (isset($_POST["sections"])) {
        $count = 0;
        $sections = array();

        foreach (required_param_array("sections", PARAM_NOTAGS) as $section) {
            $sections[$count] = $section;
            $count++;
        }

        $count = 0;
        $times = array();
        foreach (required_param_array("sectiontimes", PARAM_INT) as $time) {
            $times[$count] = $time;
            $count++;
        }

        $sectiondb = new stdClass();
        $sectiondb->video_id = $teletask->id;
        for ($i = 0; $i < count($sections); $i++) {
            $sectiondb->name = $sections[$i];
            $sectiondb->time = $times[$i];

            $DB->insert_record("teletask_sections", $sectiondb);
        }
    }

    return $DB->update_record('teletask', $teletask);

}

/**
 * Delete a teletask instance
 *
 * This is done by calling the delete_instance() method of the assignment type class
 * @param int $id id of the teletask activity that is going to be deleted
 * @return boolean Returns if the action was successful or not
 */
function teletask_delete_instance($id) {
    global $DB;

    if (! $teletask = $DB->get_record("teletask", array("id" => $id))) {
        return false;
    }

    $result = true;

    $DB->delete_records("teletask_sections", array("video_id" => $id));
    if (!$DB->delete_records("teletask", array("id" => $teletask->id))) {
        $result = false;
    }

    return $result;

}

/**
 * Retunring the features of the teletask activity
 * 
 * This is done by calling the supports() method of the assignment type class
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function teletask_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Returns course module info for the course page (Moodle 4.x+).
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function teletask_get_coursemodule_info($coursemodule) {
    // Stub for Moodle 4.x compatibility. Add custom summary or icon if needed.
    return null;
}

/**
 * Allows dynamic modification of the course module info (Moodle 4.x+).
 * @param cm_info $cm
 */
function teletask_cm_info_dynamic(cm_info $cm) {
    // Stub for Moodle 4.x compatibility. Add dynamic info if needed.
}

/**
 * Called when viewing the activity (Moodle 4.x+).
 * @param cm_info $cm
 */
function teletask_cm_info_view(cm_info $cm) {
    // Stub for Moodle 4.x compatibility. Add view tracking if needed.
}

/**
 * File serving support for the module (Moodle 4.x+).
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function teletask_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Stub for Moodle 4.x compatibility. Implement if you need to serve files.
    return false;
}