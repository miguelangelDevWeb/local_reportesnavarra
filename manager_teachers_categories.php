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

require_once('../../config.php');
require_once($CFG->dirroot. '/local/reportesnavarra/lib.php');
require_once($CFG->dirroot. '/local/reportesnavarra/forms/manager_users_categories_form.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');


$context = context_system::instance(); // Obtén el contexto del sistema.
require_login(); // Asegúrate de que el usuario está autenticado.

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/reportesnavarra/index.php'));
$PAGE->set_title(get_string('form_heading_teacher_categories', 'local_reportesnavarra'));

$PAGE->navbar->add(get_string('list_categories', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/view_category.php'));
$PAGE->navbar->add('Asignación de profesores a categorías', new moodle_url('/local/reportesnavarra/manager_teachers_categories.php'));

$mform = new local_reportesnavarra_manager_users_categories_form(); 
$isadmin = is_siteadmin();

if ($isadmin || has_capability('local/reportesnavarra:administration_teacher_categories', context_system::instance())) {

    // Si el formulario es enviado y validado.
    if ($mform->is_submitted() && $mform->is_validated()) {
        global $DB, $USER;

        $data = $mform->get_data(); 

        $users = $data->users ?? [];
        $categories = $data->categories ?? [];
        
        if (empty($users) || empty($categories)) {
            echo $OUTPUT->notification(get_string('error_no_selection', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
            
        } else {
            local_reportesnavarra_save_teacher_category($users, $categories);
            echo $OUTPUT->notification(get_string('success_save', 'local_reportesnavarra'), \core\output\notification::NOTIFY_SUCCESS);
        }
    }
    $fields = "u.id, u.firstname, u.lastname, u.email, uc.userid, uc.id as coursecategoryid, cc.name";
    $conditions = ""; //u.id = ?
    $params = []; 
    $userscategories = local_reportesnavarra_get_teachers_categories($fields, $conditions, $params);
    if (empty($userscategories)) {
        echo $OUTPUT->notification(get_string('no_data', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
    } else {

        $contextTemplate = get_context_table_users_categories($userscategories);
        $outputhtml .= $OUTPUT->render_from_template('local_reportesnavarra/table_users_categories', $contextTemplate);

    }
} else {
    redirect(new moodle_url('/'), get_string('error_permission', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR);

}


echo $OUTPUT->header(); 
echo html_writer::tag('h2', get_string('form_heading_teacher_categories', 'local_reportesnavarra'));
$mform->display(); 
echo $outputhtml;
echo $OUTPUT->footer();



