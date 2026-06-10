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
 * Adds a teletask instance.
 *
 * @param stdClass $teletask a data object from the form
 * @param mod_teletask_mod_form $mform the form instance
 * @return int The instance id of the new teletask
 */
function teletask_add_instance($teletask, $mform = null) {
    global $DB;

    $teletask->timecreated = time();
    $teletask->timemodified = $teletask->timecreated;

    // =========================================================================
    // === KORREKTUR 1: Verarbeitet die Daten des Standard-Intro-Editors ===
    // 'intro' kommt vom Formular als Array ['text' => ..., 'format' => ...].
    // Wir teilen es für die Datenbank in die Felder 'intro' und 'introformat' auf.
    if (isset($teletask->intro['text'])) {
        $teletask->introformat = $teletask->intro['format'];
        $teletask->intro = $teletask->intro['text'];
    }

    // =========================================================================
    // === KORREKTUR 2: Verarbeitet die Daten des neuen 'description_new' Editors ===
    // Genau die gleiche Logik wie für den Intro-Editor.
    if (isset($teletask->description_new['text'])) {
        $teletask->description_newformat = $teletask->description_new['format'];
        $teletask->description_new = $teletask->description_new['text'];
    }
    // =========================================================================


    if (isset($_POST["sections"])) {
        $count = 0;
        $sections = array();
        foreach (required_param_array("sections", PARAM_NOTAGS) as $section) {
            $sections[$count] = $section;
            $count++;
        }

        $count = 0;
        $times = array();
        foreach (required_param_array("sectiontimes", PARAM_TEXT) as $time) {
            $times[$count] = $time;
            $count++;
        }

        // Zuerst den Haupt-Datensatz einfügen, um die ID zu bekommen.
        $id = $DB->insert_record("teletask", $teletask);

        // Dann die Sektionen mit der neuen ID verknüpfen.
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
 * Update a teletask instance.
 *
 * @param stdClass $teletask a data object from the form
 * @param mod_teletask_mod_form $mform the form instance
 * @return bool success
 */
function teletask_update_instance($teletask, $mform = null) {
    global $DB;

    $teletask->id = $teletask->instance;
    $teletask->timemodified = time();

    // =========================================================================
    // === KORREKTUR 1: Verarbeitet die Daten des Standard-Intro-Editors ===
    if (isset($teletask->intro['text'])) {
        $teletask->introformat = $teletask->intro['format'];
        $teletask->intro = $teletask->intro['text'];
    }

    // =========================================================================
    // === KORREKTUR 2: Verarbeitet die Daten des neuen 'description_new' Editors ===
    if (isset($teletask->description_new['text'])) {
        $teletask->description_newformat = $teletask->description_new['format'];
        $teletask->description_new = $teletask->description_new['text'];
    }
    // =========================================================================

    // Update Sections (Remove and add again).
    $DB->delete_records("teletask_sections", array("video_id" => $teletask->id));

    if (isset($_POST["sections"])) {
        $count = 0;
        $sections = array();

        foreach (required_param_array("sections", PARAM_NOTAGS) as $section) {
            $sections[$count] = $section;
            $count++;
        }

        $count = 0;
        $times = array();
        foreach (required_param_array("sectiontimes", PARAM_TEXT) as $time) {
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
 * Delete a teletask instance.
 *
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
 * Returning the features of the teletask activity.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function teletask_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;

        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        default:
            return null;
    }
}
