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

use Google\Service\Classroom\Teacher;

require_once('../../config.php');
require_once($CFG->dirroot. '/local/reportesnavarra/lib.php');
require_once($CFG->dirroot. '/local/reportesnavarra/forms/user_certificate_form.php');
require_once($CFG->dirroot. '/local/reportesnavarra/forms/user_admin_certificate_form.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');


global $DB, $USER;
$context = context_system::instance(); // Obtén el contexto del sistema.
require_login(); // Asegúrate de que el usuario está autenticado.

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/reportesnavarra/index.php'));


$PAGE->navbar->add(get_string('pluginname', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/index.php'));
$PAGE->navbar->add('Descarga de certificados', new moodle_url('/local/reportesnavarra/view_user_certificate.php'));

$mform = new local_reportesnavarra_view_certificate_form(); 
$mformAdmin = new local_reportesnavarra_view_admin_certificate_form(); 
$isadmin = is_siteadmin();


$courses = local_reportesnavarra_get_courses_enrolled($USER->id);

$sw_form_admin = false;
if ($isadmin || has_capability('local/reportesnavarra:downloadallcertificates', context_system::instance())) {
    $sw_form_admin = true;
    // Si el formulario es enviado y validado.
    if ($mformAdmin->is_submitted() && $mformAdmin->is_validated()) {

        $data = $mformAdmin->get_data(); 

        $period = $data->period ?? [];
        $category = $data->category ?? [];
     
        if (empty($period) OR empty($category)) {
            echo $OUTPUT->notification(get_string('error_no_selection', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
            
        } else {
            local_grade_download_certificate_by_category($USER, $period, $category);
           
        }
    }

} else if (count($courses) > 0 && has_capability('local/reportesnavarra:downloadcertificates', context_course::instance($courses[0]['courseid']))){
    $categoryid = $courses[0]['category'];
        // Si el formulario es enviado y validado.
        if ($mform->is_submitted() && $mform->is_validated()) {

            $data = $mform->get_data(); 
    
            $period = $data->period ?? [];
            
            if (empty($period)) {
                echo $OUTPUT->notification(get_string('error_no_selection', 'local_reportesnavarra'), \core\output\notification::NOTIFY_WARNING);
                
            } else {
                local_grade_download_certificate($USER, $period, $categoryid);
            }
        }

} else {
    redirect(new moodle_url('/'), get_string('error_permission', 'local_reportesnavarra'), null, \core\output\notification::NOTIFY_ERROR);

}


echo $OUTPUT->header(); 
echo html_writer::tag('h2', 'Descarga de certificados');
if ($sw_form_admin) {
   $mformAdmin->display();
} else {
   $mform->display(); 
}

//Aqui vendra el formulario adminisrtrativo

echo $outputhtml;
echo $OUTPUT->footer();



