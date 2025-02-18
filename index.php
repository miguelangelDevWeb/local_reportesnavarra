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
 * Plugin administration pages are defined here.
 *
 * @package     local_reportesnavarra
 * @category    admin
 * @copyright   2025 Miguel Angel Velasquez Teran <miguelangel.velasquezteran1@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();
require_login();
global $USER;
$systemcontext = context_system::instance();

// Verificar si el usuario tiene la capacidad "moodle/site:manage".
// if (has_capability('moodle/site:manage', $systemcontext)) {
//     echo "El usuario tiene el rol de Gestor en el contexto del sistema.";
// } else {
//     echo "El usuario no tiene el rol de Gestor en el contexto del sistema.";
// }
$PAGE->set_context($context);
$PAGE->set_url('/local/reportesnavarra/index.php');
$PAGE->set_title('Gestión de Acciones');
$PAGE->set_heading('Gestión de Acciones');

// Agregar estilos personalizados
$PAGE->requires->css('/local/reportesnavarra/styles.css');

echo $OUTPUT->header();
echo html_writer::tag('h2', 'Acciones disponibles', ['class' => 'text-center']);

$isadmin = is_siteadmin();

// Contenedor de botones
echo html_writer::start_tag('div', ['class' => 'button-container']);
$courses = local_reportesnavarra_get_courses_enrolled($USER->id);

// Botón: Descarga de certificados
if ($isadmin || (count($courses) > 0 && has_capability('local/reportesnavarra:downloadcertificates', context_course::instance($courses[0]['courseid'])))) {
    echo html_writer::link(
        new moodle_url('/local/reportesnavarra/view_user_certificate.php'),
        '<i class="fa fa-download"></i><span>Certificados</span>',
        ['class' => 'btn btn-square btn-primary']
    );
}

// Botón: Administración
// if ($isadmin || has_capability('local/reportesnavarra:administration', $systemcontext)) {
//     echo html_writer::link(
//         new moodle_url('/local/reportesnavarra/manager_users_categories.php'),
//         '<i class="fa fa-cogs"></i><span>Administración</span>',
//         ['class' => 'btn btn-square btn-success']
//     );
// }

// Botón: Registro de asistencias
if ($isadmin || has_capability('local/reportesnavarra:administration_register_attendance', $systemcontext) || has_capability('local/reportesnavarra:gestor_register_attendance', $systemcontext)) {
    echo html_writer::link(
        new moodle_url('/local/reportesnavarra/view_category.php'),
        '<i class="fa fa-cogs"></i><span>Administración</span>',
        ['class' => 'btn btn-square btn-success']
    );
}

echo html_writer::end_tag('div');
echo $OUTPUT->footer();
