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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_reportesnavarra
 * @category    upgrade
 * @copyright   2025 Miguel Angel Velasquez Teran <miguelangel.velasquezteran1@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute local_reportesnavarra upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_reportesnavarra_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2025010819) {

        // Define field id to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
    
        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010819) {

        // Define field userid to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field userid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010819) {

        // Define field categoryid to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $field = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'userid');

        // Conditionally launch add field categoryid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010819) {

        // Define key primary (primary) to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Launch add key primary.
        $dbman->add_key($table, $key);

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010819) {

        // Define key user_foreign_key_navarra (foreign) to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $key = new xmldb_key('user_foreign_key_navarra', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key user_foreign_key_navarra.
        $dbman->add_key($table, $key);

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010819) {

        // Define key category_goreign_key_navarra (foreign) to be added to local_user_category.
        $table = new xmldb_table('local_user_category');
        $key = new xmldb_key('category_goreign_key_navarra', XMLDB_KEY_FOREIGN, ['categoryid'], 'course_categories', ['id']);

        // Launch add key category_goreign_key_navarra.
        $dbman->add_key($table, $key);

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010819, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define field id to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define field userid to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field userid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define field status to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '2', null, XMLDB_NOTNULL, null, null, 'userid');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define field datestatus to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $field = new xmldb_field('datestatus', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'status');

        // Conditionally launch add field datestatus.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define key primary (primary) to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Launch add key primary.
        $dbman->add_key($table, $key);

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }

    if ($oldversion < 2025010822) {

        // Define key user_attendance_foreign_key_navarra (foreign) to be added to local_user_attendance.
        $table = new xmldb_table('local_user_attendance');
        $key = new xmldb_key('user_attendance_foreign_key_navarra', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key user_attendance_foreign_key_navarra.
        $dbman->add_key($table, $key);

        // Reportesnavarra savepoint reached.
        upgrade_plugin_savepoint(true, 2025010822, 'local', 'reportesnavarra');
    }











    return true;
}
