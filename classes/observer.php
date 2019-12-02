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
 * Plugin observer classes are defined here.
 *
 * @package     local_quizaddons
 * @category    event
 * @author      Jean-Roch Meurisse
 * @copyright   2018 - Cellule TICE - University of Namur
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer class.
 *
 * @package    local_quizaddons
 * @copyright  2018 Jean-Roch Meurisse <jean-roch.meurisse@unamur.be>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_quizaddons_observer {

    /**
     * Triggered via $event.
     *
     * @param mod_quiz\event\question_manually_graded $event The event.
     * @return bool True on success.
     */
    public static function question_manually_graded($event) {

        global $DB, $CFG, $COURSE;
        $data = $event->get_data();
        $usergraded = $DB->get_field('quiz_attempts', 'userid', array('id' => $data['other']['attemptid']));
        $quiz = $DB->get_record('quiz', array('id' => $data['other']['quizid']));
        if ($DB->record_exists('quiz_grades', array('quiz' => $quiz->id, 'userid' => $usergraded))) {

            $quizname = $DB->get_field('quiz', 'name', array('id' => $data['other']['quizid']));
            $user = $DB->get_record('user', array('id' => $usergraded));
            $stringparams = new stdClass();
            $stringparams->quiz = $quizname;
            $stringparams->coursename = $COURSE->fullname;
            $stringparams->courseshortname = $COURSE->shortname;
            $stringparams->userfullname = fullname($user);
            $stringparams->url = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $data['contextinstanceid'];
            $msg = get_string('gradereceived', 'local_quizaddons', $stringparams);
            $message = new \core\message\message();
            $message->component = 'local_quizaddons';
            $message->name = 'manually_graded';
            $message->userfrom = get_admin();
            $message->userto = $user;
            $message->subject = get_string('eventquestionmanuallygraded', 'mod_quiz');
            $message->fullmessage = $msg;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = '';
            $message->notification = '1';
            $message->contexturl = $stringparams->url;
            $message->contexturlname = get_string('viewmygrade', 'local_quizaddons');
            $message->courseid = $COURSE->id;
            $message->replyto = "";

            message_send($message);

        }

        return true;
    }
}