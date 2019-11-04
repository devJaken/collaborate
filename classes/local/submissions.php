<?php
/**
 * Class for handling student submissions.
 *
 * @package   mod_collaborate
 * @copyright 2018 Richard Jones https://richardnz.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborate\local;
use \mod_collaborate\local\collaborate_editor;
use \mod_collaborate\local\debugging;
use \mod_collaborate\local\submission_form;
defined('MOODLE_INTERNAL') || die();
class submissions {
    /**
     * Add a submission record to the DB.
     *
     * @param object $data - the data to add
     * @param object $context - our module context
     * @param int $cid our collaborate instance id.
     * @return int $id - the id of the inserted record
     */
    public static function save_submission($data, $context, $cid, $page) {
        global $DB, $USER;
        $exists = self::get_submission($cid, $USER->id, $page);
        if($exists) {
            $DB->delete_records('collaborate_submissions',
                    ['collaborateid' => $cid, 'userid' => $USER->id, 'page' => $page]);
        }
        $options = collaborate_editor::get_editor_options($context);
        // Insert a dummy record and get the id.
        $data->timecreated = time();
        $data->timemodified = time();
        $data->collaborateid = $cid;
        $data->userid = $USER->id;
        $data->page = $page;
        $data->submission = ' ';
        $data->submissionformat = FORMAT_HTML;
        $dataid = $DB->insert_record('collaborate_submissions', $data);
        $data->id = $dataid;
        // Massage the data into a form for saving.
        $data = file_postupdate_standard_editor(
                $data,
                'submission',
                $options,
                $context,
                'mod_collaborate',
                'submission',
                $data->id);
        // Update the record with full editor data.
        $DB->update_record('collaborate_submissions', $data);
        return $data->id;
    }
    public static function get_submission($cid, $userid, $page) {
        global $DB;
        return $DB->get_record('collaborate_submissions', ['collaborateid' => $cid, 'userid' => $userid, 'page' => $page], '*', IGNORE_MISSING);
    }
}