<?php
defined('MOODLE_INTERNAL') || die();

function save_mbti_93($course,$extra_res,$intra_res,$sensi_res,$intui_res,$ratio_res,$emoti_res,$estru_res,$perce_res) {
    GLOBAL $DB, $USER, $CFG;
    if (!$entry = $DB->get_record('personality_test', array('user' => $USER->id, 'course' => $course))) {
        $entry = new stdClass();
        $entry->user = $USER->id;
        $entry->course = $course;
        $entry->state = "1";
        $entry->extraversion = $extra_res;
        $entry->introversion = $intra_res;
        $entry->sensing = $sensi_res;
        $entry->intuition = $intui_res;
        $entry->thinking = $ratio_res;
        $entry->feeling = $emoti_res;
        $entry->judging = $estru_res;
        $entry->perceptive = $perce_res;
        $entry->time_done = time();
        $entry->updated_at = time();
        $entry->id = $DB->insert_record('personality_test', $entry);
        return true;
    }else{
        return false;
    }
}

