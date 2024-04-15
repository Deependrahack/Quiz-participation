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
 * Block block_quiz_participation is defined here.
 *
 * @package     block_quiz_participation
 * @copyright   2022 Deependra Kumar Singh <deepcs20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once 'lib.php';
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$cid = optional_param('cid', 0, PARAM_INT);
global $CFG, $DB, $USER, $OUTPUT, $PAGE;
// Page configurations.
$PAGE->set_context(CONTEXT_COURSE::instance($cid));
$PAGE->set_title(get_string('alllearners', 'block_quiz_participation'));
$PAGE->set_heading(get_string('alllearners', 'block_quiz_participation'));
$url = new moodle_url('/blocks/vlearn_learners/allusers.php');
$PAGE->set_url($url);

$PAGE->set_pagelayout('standard');
require_login();
$course = $DB->get_record('course', array('id'=>$cid));
$courseurl = new \moodle_url("/course/view.php", array('id'=>$cid));
$PAGE->set_course($course);
$PAGE->navbar->add($course->fullname, $courseurl);
$PAGE->navbar->add('allusers', $url);
$PAGE->set_title("Quiz users");


echo $OUTPUT->header();
$url_params = array();
$context = CONTEXT_COURSE::instance($cid);
$quizmodule = $DB->get_record('modules', ['name' => 'quiz']);
if (empty($quizmodule)) {
    return $this->content;
}

$quizids = [];
foreach (get_fast_modinfo($cid)->cms as $cm) {
    if ($cm->module === $quizmodule->id && $cm->uservisible === true) {
        $quizids[] = $cm->instance;
    }
}
$output = '';
$enrolledusers = get_role_users(5, $context, false, '*,u.id', "u.id ASC");
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
        $learners['trainers'][$i]['email'] = $user->email;
        $learners['trainers'][$i]['imageurl'] = "$imgurl";
        $learners['trainers'][$i]['quizcount'] = $attempts->noofquiz;
        $learners['trainers'][$i]['totalquiz'] = count($quizids);
        $i++;
    }
    array_multisort(array_column($learners['trainers'], 'quizcount'), SORT_DESC, $learners['trainers']);
    echo '<div class="program-filter justify-content-start align-items-center">
            <div class="input-group col-sm-5 col-12 col-md-5 pl-0 serch-input-box px-0">
            <i class=" lightgrey-text"></i>
            <input type="text" class="form-control learner-search" placeholder="Search Name">
        </div>
        </div><br><br>';
    $output .= html_writer::start_div('alllearners-display');
    $output .= $OUTPUT->render_from_template('block_quiz_participation/alluser', $learners);
    $url_params = array("cid" => $cid);
    $url = new moodle_url('/blocks/vlearn_learners/allusers.php', $url_params);
    $output .= html_writer::start_div('pagination-nav-filter');
    $output .= $OUTPUT->paging_bar(count($enrolledusers), $page, $perpage, $url);
    $output .= html_writer::end_div();
    $output .= html_writer::end_div();
} else {
    $output = html_writer::div(get_string('nothingtodisplay', 'block_quiz_participation'), 'alert alert-info mt-3');
}
echo $output;
//echo "</div>";
echo $OUTPUT->footer();
