<?php
require_once('../../config.php');
require_login();
$isadmin = is_siteadmin();
global $USER;
// Obtener el contexto del sistema.
$context = context_system::instance();
// Configurar la página.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/reportesnavarra/'));
$PAGE->set_title(get_string('list_categories', 'local_reportesnavarra'));

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('list_categories_heading', 'local_reportesnavarra'));

if (!$isadmin && !has_capability('local/reportesnavarra:administration_register_attendance', $context) && !has_capability('local/reportesnavarra:gestor_register_attendance',$context)) {
    redirect(new moodle_url('/local/reportesnavarra/'), get_string('error_permission', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR);
}

$fields = "u.id, u.firstname, u.lastname, u.email, uc.userid, uc.id as coursecategoryid, uc.categoryid, cc.name";

    $conditions = ""; //u.id = ?
    $params = [];    
    $categories = local_reportesnavarra_get_users_categories($fields,$conditions,$params);
    if (empty($categories)) {
        echo $OUTPUT->notification(get_string('no_categories_found', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
    }

    $table = local_reportesnavarra_get_table_categories($categories);
    echo html_writer::table($table);




echo $OUTPUT->footer();
