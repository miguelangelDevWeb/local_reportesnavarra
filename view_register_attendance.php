<?php
require_once('../../config.php');
require_login();

// Obtener parámetros de la URL.
$categoryid = required_param('categoryid', PARAM_INT); // ID de la categoría.
$context = context_system::instance(); // Contexto del sistema.

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/reportesnavarra/index.php'));
$PAGE->set_title(get_string('register_attendance', 'local_reportesnavarra'));

$PAGE->navbar->add(get_string('list_categories', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/view_category.php'));
$PAGE->navbar->add(get_string('register_attendance', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/view_register_attendance.php', ['categoryid' => $categoryid]));
// Obtener la fecha actual para el campo de texto.
$current_date = date('Y-m-d');

// Obtener el primer curso de la categoría.
global $DB, $USER;
$sql = "SELECT c.id AS courseid
        FROM  {course} c 
        WHERE c.category = :categoryid
        ORDER BY c.sortorder ASC
        LIMIT 1";
$course = $DB->get_record_sql($sql, ['categoryid' => $categoryid]);
if (!$course) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('no_courses_found', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
    echo $OUTPUT->continue_button(new moodle_url('/local/reportesnavarra/list_categories.php'));
    echo $OUTPUT->footer();
    exit;
}

// Obtener estudiantes matriculados en el curso.
$students = get_enrolled_users(context_course::instance($course->courseid), 'mod/assignment:submit', 0, 'u.id, u.firstname, u.lastname, u.email');


echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('register_attendance', 'local_reportesnavarra'));

// Formulario de asistencia.
echo html_writer::start_tag('form', [
    'method' => 'POST',
    'action' => 'save_users_attendance.php',
]);

// Campo de texto de tipo fecha.
echo html_writer::start_div();
echo html_writer::tag('label', get_string('date').': ', ['for' => 'date']);
echo html_writer::empty_tag('input', [
    'type' => 'date',
    'id' => 'date',
    'name' => 'date',
    'value' => $current_date,
]);
echo html_writer::end_div();
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => "categoryid",
    'value' => $categoryid,
]);
// Construir la tabla.
$table = new html_table();
$table->head = [
    'Foto',
    'Estudiante',
    'Mail',
    'Estado',
];
$table->data = [];

foreach ($students as $student) {
    $name = "{$student->firstname} {$student->lastname}";
    $selector = html_writer::select([
        get_config('local_reportesnavarra', 'days_attended') => get_string('days_attended', 'local_reportesnavarra'),
        get_config('local_reportesnavarra', 'faults') => get_string('faults', 'local_reportesnavarra'),
        get_config('local_reportesnavarra', 'arrears') => get_string('arrears', 'local_reportesnavarra'),
        get_config('local_reportesnavarra', 'justified_absences') => get_string('justified_absences', 'local_reportesnavarra'),
        get_config('local_reportesnavarra', 'justified_delays') => get_string('justified_delays', 'local_reportesnavarra'),
    ], "status[{$student->id}]", 'DA');
    $url_image_profile = local_reportesnavarra_get_picture_profile_for_template($student);
    $tag_img = "<div align='center'><img src=".$url_image_profile." class='mr-1 rounded-circle' style='width: 30px; height: 30px;' alt='Foto de Perfil'></div>";
    $table->data[] = [
        $tag_img,
        $name,
        $student->email,
        $selector . html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => "student_ids[]",
            'value' => $student->id,
        ]),
    ];
}

// Mostrar la tabla.
echo html_writer::table($table);

// Botones de guardar y cancelar.
echo html_writer::start_div();
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('save')]);
echo html_writer::tag('a', get_string('cancel'), [
    'href' => new moodle_url('/local/reportesnavarra/view_category.php'),
    'class' => 'btn btn-secondary',
]);
echo html_writer::end_div();

echo html_writer::end_tag('form');
echo $OUTPUT->footer();
