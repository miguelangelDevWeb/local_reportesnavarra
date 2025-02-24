<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/reportesnavarra/lib.php');

require_login();
$isadmin = is_siteadmin();
global $USER;
// Obtener el contexto del sistema.
$context = context_system::instance();
// Configurar la página.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/reportesnavarra/'));
$PAGE->set_title(get_string('list_categories', 'local_reportesnavarra'));

$PAGE->navbar->add(get_string('pluginname', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/index.php'));
$PAGE->navbar->add('Categorias disponibles', new moodle_url('/local/reportesnavarra/view_category.php'));

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('list_categories_heading', 'local_reportesnavarra'));

if (!$isadmin && !has_capability('local/reportesnavarra:administration_register_attendance', $context) && !has_capability('local/reportesnavarra:gestor_register_attendance', $context)) {
    redirect(new moodle_url('/local/reportesnavarra/'), get_string('error_permission', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR);
}

$fields = "u.id, u.firstname, u.lastname, u.email, uc.userid, uc.id as coursecategoryid, uc.categoryid, cc.name";

$categories = [];
if ($isadmin) {
    $fields = 'uc.categoryid,cc.name ';
    $conditions = '';
    $params = [];
    $categories = local_reportesnavarra_get_users_categories($fields, $conditions, $params);
} else {
    $fields = 'uc.categoryid,cc.name ';
    $conditions = 'uc.userid = ?';
    $params = [$USER->id];
    $categories = local_reportesnavarra_get_users_categories($fields, $conditions, $params);
}
if (empty($categories)) {
    echo $OUTPUT->notification(get_string('no_categories_found', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
}

$table = local_reportesnavarra_get_table_categories($categories);
echo html_writer::table($table);




echo $OUTPUT->footer();
