<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/reportesnavarra/lib.php');

require_login();
$isadmin = is_siteadmin();

if (!$isadmin) {
    print_error('no_permission', 'local_reportesnavarra');
}

global $DB;

// Verifica si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryid = required_param('categoryid', PARAM_INT);
    $student_ids = required_param_array('student_ids', PARAM_INT);
    $attendance_ids = required_param_array('attendanceid', PARAM_INT);
    $statuses = required_param_array('status', PARAM_RAW);
    $datestatuses = required_param_array('datestatus', PARAM_RAW);
    $filterdate = required_param('filter_date', PARAM_TEXT);
    
    // Validar que los arrays tienen la misma longitud
    if (count($student_ids) !== count($attendance_ids)) {
        redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$categoryid.'&filter_date='.$filterdate), 'Faltan datos.', null, \core\output\notification::NOTIFY_ERROR);
    }
    $update_data = [];
    foreach ($attendance_ids as $index => $attendance_id) {
 
        $attendance_id = $attendance_id;
        $status = $statuses[$index];
        $datestatus = $datestatuses[$index];

        // Validar fecha y estado
        $datestatus_time = strtotime($datestatus);
        if ($datestatus_time === false) {
            redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$categoryid.'&filter_date='.$filterdate), 'Faltan datos.', null, \core\output\notification::NOTIFY_ERROR);
        }
        // Actualizar el registro en la base de datos
        $update_data = [
            'id' => $attendance_id,
            'status' => $status,
            'datestatus' => $datestatus_time,
        ];
  
        try {
            $DB->update_record('local_user_attendance', $update_data);
        } catch (Exception $e) {
            debugging("Error al actualizar el registro de asistencia para el estudiante $student_id: " . $e->getMessage());
            redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$categoryid.'&filter_date='.$filterdate), $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
        }
    }

    // Redirigir con un mensaje de éxito
    redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php', ['categoryid' => $categoryid]), 
             get_string('update_success', 'local_reportesnavarra'), 
             null, 
             \core\output\notification::NOTIFY_SUCCESS);
} else {
    redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$categoryid.'&filter_date='.$filterdate), 'El registro no existe.', null, \core\output\notification::NOTIFY_ERROR);
}
