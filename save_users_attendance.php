<?php
require_once('../../config.php');
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/local/reportesnavarra/view_register_attendance.php?categoryid='.$categoryid), get_string('errorinsertingattendance', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR, $e->getMessage());
}

// Validar parámetros.
$date = required_param('date', PARAM_RAW);
$datestatus = strtotime($date);
$student_ids = required_param_array('student_ids', PARAM_INT);
$categoryid = required_param('categoryid', PARAM_INT);
$statuses = required_param_array('status', PARAM_ALPHA);

global $DB;

// Recorrer estudiantes y guardar datos.
foreach ($student_ids as $student_id) {
    if (isset($statuses[$student_id])) {
        $status = $statuses[$student_id];

        // Insertar en la base de datos.
        $record = new stdClass();
        $record->userid = $student_id;
        $record->status = $status;
        $record->categoryid = $categoryid;
        $record->datestatus = $datestatus;

        try {
            $DB->insert_record('local_user_attendance', $record);
        } catch (Exception $e) {
            redirect(new moodle_url('/local/reportesnavarra/view_register_attendance.php?categoryid='.$categoryid), get_string('errorinsertingattendance', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR, $e->getMessage());
        } 
        
    }
}
redirect(new moodle_url('/local/reportesnavarra/view_register_attendance.php?categoryid='.$categoryid), get_string('success_save', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_SUCCESS);

