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
 * Displays a teletask recording using the modern XMF player.
 *
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// --- 1. Moodle Setup: Get parameters and load records ---
$id = required_param('id', PARAM_INT); // Course Module ID.

list($course, $cm) = get_course_and_cm_from_cmid($id, 'teletask');
$teletask = $DB->get_record('teletask', ['id' => $cm->instance], '*', MUST_EXIST);

$url = new moodle_url('/mod/teletask/view.php', ['id' => $id]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($teletask->name));
$PAGE->set_heading(format_string($course->fullname));

// --- 2. Security and Context Setup ---
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// --- 3. Activity Completion ---
if (class_exists('\core_completion\api') && method_exists(\core_completion\api::class, 'viewed')) {
    \core_completion\api::viewed($cm);
} else {
    if (!empty($cm->completionview)) {
        require_once($CFG->libdir . '/completionlib.php');
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm)) {
$completion = new completion_info($course);
$completion->set_module_viewed($cm);
//  $completion->update_state($cm, COMPLETION_STATE_COMPLETE);        
 //   completion_update_state($cm, COMPLETION_STATE_COMPLETE);
        }
    }
}

// --- 4. Prepare all data for the template ---
$templatecontext = new stdClass();
$templatecontext->name = format_string($teletask->name, true, ['context' => $context]);

// Prepare text content for display. format_text() is crucial for security and applying filters.
$templatecontext->intro = format_text($teletask->intro, FORMAT_HTML, ['context' => $context, 'noclean' => true]);

if (!empty($teletask->description_new)) {
    $templatecontext->description_new = format_text($teletask->description_new, $teletask->description_newformat, ['context' => $context, 'noclean' =>true]);
}

// Helper function to extract Vimeo video ID from various URL formats.
$extract_vimeo_id = function($url) {
    if (empty($url) || !is_string($url)) {
        return '';
    }
    if (ctype_digit($url)) {
        return $url;
    }
    if (preg_match('/(\d+)\/?$/', $url, $matches)) {
        return $matches[1];
    }
    return '';
};

// Helper to resolve local paths OR extract Vimeo ID from external URLs.
$resolve_video_source = function($url, $type) use ($id, $extract_vimeo_id) {
    if (empty($url)) {
        return '';
    }
    if (strpos($url, '//') !== false) {
        return $extract_vimeo_id($url);
    }
    $proxyurl = new moodle_url('/mod/teletask/serve_video_proxy.php', ['id' => $id, 'type' => $type]);
    return $proxyurl->out(false);
};

// Prepare video sources for the player.
$templatecontext->media_url = (new moodle_url('/mod/teletask/'))->out(false);
$templatecontext->video_url_speaker = $resolve_video_source($teletask->video_url_speaker, 'speaker');
$templatecontext->video_url_desktop = $resolve_video_source($teletask->video_url_desktop, 'desktop');
$templatecontext->has_desktop_video = !empty($templatecontext->video_url_desktop);

$pipurl = '';
if (isset($teletask->video_url_pip)) {
    $pipurl = $resolve_video_source($teletask->video_url_pip, 'pip');
}
$templatecontext->video_url_pip = $pipurl;
$templatecontext->has_pip_video = !empty($pipurl);

// Dynamically build the reference for the presentation component.
$presentation_reference = 'lecturer';
if ($templatecontext->has_desktop_video) {
    $presentation_reference .= ',slides';
}
$templatecontext->presentation_reference = $presentation_reference;


// --- 5. Render the final page ---
echo $OUTPUT->header();
// echo $OUTPUT->heading($templatecontext->name);

// Pass the prepared data to the Mustache template for rendering.
echo $OUTPUT->render_from_template('mod_teletask/view', $templatecontext);

echo $OUTPUT->footer();
