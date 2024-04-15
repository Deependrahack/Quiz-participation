<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block quiz_participation is defined here.
 *
 * @package     block_quiz_participation
 * @copyright   2022 Deependra Kumar Singh <deepcs20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quiz_participation extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_quiz_participation');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . "/blocks/quiz_participation/lib.php";
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $out = "";
            $syscontext = context_system::instance();
            if(!has_capability("block/quiz_participation:viewdata", $syscontext)){
                return $this->content;
            }
//            if (!isloggedin() ||
//                    empty($this->page) ||
//                    empty($this->page->course->id) ||
//                    $this->page->course->id === SITEID
//            ) {
//                return $this->content;
//            }


            $quizmodule = $DB->get_record('modules', ['name' => 'quiz']);
            if (empty($quizmodule)) {
                return $this->content;
            }
           
                $courses = enrol_get_my_courses('id, fullname');
               if (!empty($courses)) {
                $firstcourse = reset($courses);
                $courseid = $firstcourse->id;
                if ($courses) {
                    $filter = [];
                    foreach ($courses as $key => $v) {
                        if ($v->id == $firstcourse->id) {
                            $filter[] = array('value' => $v->id, "text" => $v->fullname, "select" => "selected");
                        } else {
                            $filter[] = array('value' => $v->id, "text" => $v->fullname);
                        }
                    }
                }
                $data = [];
                $data['filtervalue'] = $filter;
                $data['filter'] = true;
                $out .= $OUTPUT->render_from_template('block_quiz_participation/learners', $data);
            } else {
                $courseid = $this->page->course->id;
            }
            $out .= html_writer::start_div("course-data");
            $quizids = [];
            foreach (get_fast_modinfo($courseid)->cms as $cm) {
                if ($cm->module === $quizmodule->id && $cm->uservisible === true) {
                    $quizids[] = $cm->instance;
                }
            }
            if (empty($quizids)) {
//                $this->content->text .= $out;
            }
            $context = CONTEXT_COURSE::instance($courseid);
            $enrolledusers = get_role_users(5, $context, false, '*,u.id', "u.id ASC");
            //Programs start this week
            $today = time();
            $start = (date('w', $today) == 0) ? $today : strtotime('last friday', $today);
            $weekstart = date('Y-m-d', $start);
            $weekenddate = strtotime('next friday', $start);
            if (!empty($enrolledusers)) {
                $i = 0;
                $learners = array();
                foreach ($enrolledusers as $key => $user) {
                    $userpic = new \user_picture($user);
                    $imgurl = $userpic->get_url($PAGE);
                    $attempts = get_course_quiz_participation_count($quizids, $user->id, $start, $weekenddate);
                    $learners['trainers'][$i]['name'] = $user->firstname . ' ' . $user->lastname;
                    $learners['trainers'][$i]['imageurl'] = "$imgurl";
                    $learners['trainers'][$i]['profileurl'] = $CFG->wwwroot . "/user/profile.php?id=" . $user->id;
                    $learners['trainers'][$i]['quizcount'] = $attempts->noofquiz;
                    $learners['trainers'][$i]['totalquiz'] = count($quizids);
                    $i++;
                }
                array_multisort(array_column($learners['trainers'], 'quizcount'), SORT_DESC, $learners['trainers']);
                $learners['headerdisplay'] = true;
                $out .= $OUTPUT->render_from_template('block_quiz_participation/learners', $learners);
                $this->content->text = $out;
//                if (count($enrolledusers) >= 5) {
                $url_params = array("cid" => $this->page->course->id);
                $alllearnersurl = new moodle_url('/blocks/quiz_participation/allusers.php', $url_params);
                $seeall = html_writer::start_div('text-center w-100');
                $seeall .= html_writer::tag('a', "View all", array("class" => "font-w-600 view-all d-block font-14", 'href' => $alllearnersurl));
                $this->content->text .= $seeall;
//                }
            } else {
                $out .= html_writer::div(get_string('nothingtodisplay', 'block_quiz_participation'), 'alert alert-info mt-3');
                $this->content->text = $out;
            }
            $out .= html_writer::end_div();
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_quiz_participation');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'mod' => true,
            'all' => true
        );
    }

}
