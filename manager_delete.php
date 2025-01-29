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
global $DB;
// Obtener el parámetro `coursecategoryid` de la URL.

$coursecategoryid = optional_param('categoryid',NULL, PARAM_INT);
$userattendanceid = optional_param('attendanceid',NULL, PARAM_INT);
$filterdate = optional_param('filter_date',NULL, PARAM_TEXT);
$type = required_param('type', PARAM_TEXT);
$isadmin = is_siteadmin();

if ($type == 'usercategory' && $coursecategoryid) {
    if  ($isadmin ||  has_capability('local/reportesnavarra:administration_users_categories', context_system::instance())){
        $exists = $DB->record_exists('local_user_category', ['id' => $coursecategoryid]);
        if (!$exists) {
            redirect(new moodle_url('/local/reportesnavarra/manager_users_categories.php'), 'El registro no existe.', null, \core\output\notification::NOTIFY_ERROR);
        }
        $DB->delete_records('local_user_category', ['id' => $coursecategoryid]);
        redirect(new moodle_url('/local/reportesnavarra/manager_users_categories.php'), 'El registro se eliminó correctamente.', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo $OUTPUT->notification(get_string('error_save', 'local_reportesnavarra'), \core\output\notification::NOTIFY_ERROR);
    }
} 

if ($type == 'userattendance' && $userattendanceid && $filterdate) {
    if  ($isadmin ||  has_capability('local/reportesnavarra:administration_user_attendance', context_system::instance())){
        $exists = $DB->record_exists('local_user_attendance', ['id' => $userattendanceid]);
        if (!$exists) {
            redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$coursecategoryid.'&filter_date='.$filterdate), 'El registro no existe.', null, \core\output\notification::NOTIFY_ERROR);
        }
        $DB->delete_records('local_user_attendance', ['id' => $userattendanceid]);
        redirect(new moodle_url('/local/reportesnavarra/view_users_attendance.php?categoryid='.$coursecategoryid.'&filter_date='.$filterdate), 'El registro se eliminó correctamente.', null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo $OUTPUT->notification(get_string('error_save', 'local_reportesnavarra'), \core\output\notification::NOTIFY_ERROR);
    }
} 


