<?php

/*
 * Quiz count
 */

function get_course_quiz_participation_count($quizids, $userid, $timestart, $timend) {
    global $DB, $USER;
    if(empty($quizids)){
        return (object)array("noofquiz"=> 0);
    }
    [$quizidssql, $params] = $DB->get_in_or_equal($quizids);
    $params[] = $userid;
    $rs = $DB->get_record_sql("SELECT COUNT(q.id) as noofquiz
                FROM {quiz} q
                JOIN {quiz_attempts} qa ON qa.quiz = q.id
                JOIN {grade_items} gi ON gi.iteminstance = q.id AND gi.itemtype = 'mod' AND gi.itemmodule = 'quiz'
                WHERE q.id $quizidssql AND qa.userid = ? AND qa.state = 'finished' AND 
                qa.preview = 0 AND qa.timefinish  BETWEEN $timestart AND $timend  ",
            $params
    );
    if($rs){
        return $rs;
       
    }
return (object)array("noofquiz"=> 0);
}
