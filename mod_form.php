<?php
// This file is part of Moodle - http://moodle.org/
// ... (license header remains the same) ...

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/teletask/lib.php');

/**
 * Provides the form to perform an insert/update teletask activity action
 */
class mod_teletask_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $COURSE;
        $mform =& $this->_form;

        if (empty($this->current->id)) {
            $mform->addElement('html',
                    '<div id="filelist">'.get_string('videouploadrestriction', 'teletask').'</div><br />'.
                    '<div id="container"><a id="pickfiles" href="javascript:;">['.get_string('videouploadselect', 'teletask').']</a>'.
                    '<a id="uploadfiles" href="javascript:;">['.get_string('videouploadfile', 'teletask').']</a>(optional)</div>');
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('videoname', 'teletask'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('text', 'speaker', get_string('videospeaker', 'teletask'));
        $mform->setType('speaker', PARAM_TEXT);
        $mform->addElement('date_selector', 'date', get_string('videorecordingdate', 'teletask'), array('optional'  => false));
        $mform->addRule('date', null, 'required', null, 'client');
        $mform->addElement('text', 'video_url_speaker', get_string('videourlspeaker', 'teletask'));
        $mform->setType('video_url_speaker', PARAM_TEXT);
        $mform->addRule('video_url_speaker', null, 'required', null, 'client');
        $mform->addElement('text', 'video_url_desktop', get_string('videourldesktop', 'teletask'));
        $mform->setType('video_url_desktop', PARAM_TEXT);
        $mform->addElement('text', 'video_url_pip', get_string('videourlpip', 'teletask'));
        $mform->setType('video_url_pip', PARAM_URL);

        if (empty($this->current->id)) {
            $teletasksections = array();
        } else {
            $teletasksections = $DB->get_records('teletask_sections', array('video_id' => $this->current->id), 'time');
        }
        foreach ($teletasksections as $section) {
            $mform->addElement('html',
                    '<div>'.get_string('videosection', 'teletask').': <input type="text" name="sections[]" value="'.
                    htmlspecialchars($section->name).'"><small> '.get_string('videosectiontime', 'teletask').':</small> <input type="text" name="sectiontimes[]" value="'.
                    htmlspecialchars($section->time).'"> <a class="remove_section" style="cursor: pointer;">'.get_string('videosectionremove', 'teletask').'</a></div>');
        }
        $mform->addElement('html', '</div>');

        $this->standard_intro_elements();
        $mform->addElement('header', 'description_new_header', get_string('description_new', 'teletask'));
        $mform->addElement('editor', 'description_new', get_string('description_new', 'teletask'));
        $mform->setType('description_new', PARAM_RAW);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    // =========================================================================
    // === SICHERERE, KOMPATIBLERE VERSION DER METHODE ZUM VORBEREITEN DER DATEN ===
    // =========================================================================
    public function set_data($data) {
        // Prüfen, ob Daten für einen bestehenden Eintrag geladen werden.
        if (!empty($data->id)) {
            // Die 'intro' Daten vorbereiten.
            if (isset($data->intro)) {
                $data->intro = [
                    'text'   => $data->intro,
                    'format' => $data->introformat
                ];
            }

            // Die 'description_new' Daten vorbereiten.
            if (isset($data->description_new)) {
                 $data->description_new = [
                    'text'   => $data->description_new,
                    'format' => $data->description_newformat
                ];
            }
        }

        // Die übergeordnete Methode aufrufen, um alle anderen Felder zu verarbeiten.
        return parent::set_data($data);
    }
    // =========================================================================
    // === ENDE DER NEUEN METHODE ===
    // =========================================================================
}
