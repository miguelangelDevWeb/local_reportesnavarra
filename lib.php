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

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/reportesnavarra/classes/customtcpdf.php');

function local_reportesnavarra_extend_navigation(global_navigation $root)
{
    if (isguestuser()) {
        throw new moodle_exception('noguest');
    }


    // Enlace para todos los usuarios
    $node = navigation_node::create(
        get_string('pluginname', 'local_reportesnavarra'),
        new moodle_url('/local/reportesnavarra/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        null,
        new pix_icon('i/grades', '')
    );

    $node->showinflatnavigation = true;
    $root->add_node($node);
}


function local_reportesnavarra_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course)
{

    if (empty($course)) {
        // We want to display these reports under the site context.
        $course = get_fast_modinfo(SITEID)->get_course();
    }
    $systemcontext = context_system::instance();
    $usercontext = context_user::instance($user->id);
    $coursecontext = context_course::instance($course->id);
    if (grade_report_overview::check_access($systemcontext, $coursecontext, $usercontext, $course, $user->id)) {
        $url = new moodle_url('/local/reportesnavarra/index.php', array('userid' => $user->id, 'id' => $course->id));
        $node = new core_user\output\myprofile\node(
            'reports',
            'local_reportesnavarra',
            get_string('pluginname', 'local_reportesnavarra'),
            null,
            $url
        );
        $tree->add_node($node);
    }
}

function local_reportesnavarra_get_all_users(
    $fields = '',
    $conditions = '',
    $params = []
) {
    global $DB;

    // Validar campos.
    if (empty($fields)) {
        $fields = '*';
    }

    // Construir la consulta base.
    $sql = "SELECT $fields FROM {user}";

    // Agregar condiciones por defecto.
    $defaultConditions = "deleted <> :deleted AND suspended <> :suspended AND id > 2";
    $defaultParams = ['deleted' => 1, 'suspended' => 1];

    // Combinar condiciones adicionales con las predeterminadas.
    if (!empty($conditions)) {
        $sql .= " WHERE ($conditions) AND $defaultConditions";
    } else {
        $sql .= " WHERE $defaultConditions";
    }

    // Combinar parámetros adicionales con los predeterminados.
    $params = array_merge($defaultParams, $params);

    // Agregar orden.
    $sql .= " ORDER BY firstname ASC";

    // Ejecutar la consulta.
    return $DB->get_records_sql($sql, $params);
}


function local_reportesnavarra_get_all_categories()
{
    global $DB;
    $sql = 'select id,name from {course_categories} where visible = 1 AND coursecount > 0';
    $categories = $DB->get_records_sql($sql);
    return $categories;
}

function local_reportesnavarra_save_user_category($users, $categories)
{
    global $DB;
    // Iniciar una transacción.
    $transaction = $DB->start_delegated_transaction();

    try {
        foreach ($users as $userid) {
            foreach ($categories as $categoryid) {
                if (!is_numeric($userid) || !is_numeric($categoryid)) {
                    throw new moodle_exception('invalid_parameter', 'local_reportesnavarra', '', null, 'UserID o CategoryID no son válidos.');
                }

                $record = new stdClass();
                $record->userid = $userid;
                $record->categoryid = $categoryid;

                $DB->insert_record('local_user_category', $record);
            }
        }

        // Confirmar la transacción si todo es exitoso.
        $transaction->allow_commit();

        return true;
    } catch (dml_exception $e) {
        // Cancelar la transacción en caso de error.
        $transaction->rollback($e);
        debugging('Error en la base de datos: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    } catch (Exception $e) {
        // Manejar otros errores.
        $transaction->rollback($e);
        debugging('Error inesperado: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}

function local_reportesnavarra_save_teacher_category($users, $categories)
{
    global $DB;
    // Iniciar una transacción.
    $transaction = $DB->start_delegated_transaction();

    try {
        foreach ($users as $userid) {
            foreach ($categories as $categoryid) {
                if (!is_numeric($userid) || !is_numeric($categoryid)) {
                    throw new moodle_exception('invalid_parameter', 'local_reportesnavarra', '', null, 'UserID o CategoryID no son válidos.');
                }

                $record = new stdClass();
                $record->userid = $userid;
                $record->categoryid = $categoryid;

                $DB->insert_record('local_teacher_category', $record);
            }
        }

        // Confirmar la transacción si todo es exitoso.
        $transaction->allow_commit();

        return true;
    } catch (dml_exception $e) {
        // Cancelar la transacción en caso de error.
        $transaction->rollback($e);
        debugging('Error en la base de datos: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    } catch (Exception $e) {
        // Manejar otros errores.
        $transaction->rollback($e);
        debugging('Error inesperado: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}

function local_reportesnavarra_get_users_categories($fields = '*', $conditions = '', $params = [])
{
    global $DB;

    if (empty($fields)) {
        $fields = '*';
    }

    $sql = "SELECT $fields 
            FROM {local_user_category} uc
            INNER JOIN {user} u ON u.id = uc.userid
            INNER JOIN {course_categories} cc ON cc.id = uc.categoryid";

    if ($conditions !== '') {
        $sql .= " WHERE $conditions";
    }

    $sql .= " ORDER BY u.firstname";

    return $DB->get_recordset_sql($sql, $params);
}

function local_reportesnavarra_get_teachers_categories($fields = '*', $conditions = '', $params = [])
{
    global $DB;

    if (empty($fields)) {
        $fields = '*';
    }

    $sql = "SELECT $fields 
            FROM {local_teacher_category} uc
            INNER JOIN {user} u ON u.id = uc.userid
            INNER JOIN {course_categories} cc ON cc.id = uc.categoryid";

    if ($conditions !== '') {
        $sql .= " WHERE $conditions";
    }

    $sql .= " ORDER BY u.firstname";
    return $DB->get_recordset_sql($sql, $params);
}

function local_reportesnavarra_get_users_attendance($fields = '*', $conditions = '', $params = [])
{
    global $DB;

    if (empty($fields)) {
        $fields = '*';
    }

    $sql = "SELECT $fields 
            FROM {local_user_attendance} ua
            INNER JOIN {user} u ON u.id = ua.userid";

    if ($conditions !== '') {
        $sql .= " WHERE $conditions";
    }

    $sql .= " ORDER BY ua.datestatus DESC";

    return $DB->get_recordset_sql($sql, $params);
}

function get_context_table_users_categories($userscategories)
{
    global $CFG;
    $tableUsersContext = [];
    foreach ($userscategories as $usercategory) {
        $user = local_reportesnavarra_get_all_users(
            'id,username,firstname,lastname,email',
            'id = :id',
            ['id' => $usercategory->userid]
        );

        $user = reset($user);
        $pictureUrl = local_reportesnavarra_get_picture_profile_for_template($user);

        $tableUsersContext[] = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'category_name' => $usercategory->name,
            'status' => 'true',
            'profileImage' => $pictureUrl,
            'link' => $CFG->wwwroot . "/local/reportesnavarra/manager_delete.php?categoryid=" . $usercategory->coursecategoryid . "&type=usercategory",
        ];
    }
    $contextTemplate = [
        'users' => $tableUsersContext
    ];
    return $contextTemplate;
}

function local_reportesnavarra_get_table_categories($categories)
{
    $context = context_system::instance();
    $isadmin = is_siteadmin();
    // Construir la tabla.
    $table = new html_table();
    $table->head = [
        'Categoria',
        'Acción'
    ];

    foreach ($categories as $category) {
        $view_url_add_user = new moodle_url('/local/reportesnavarra/manager_users_categories.php');
        $view_url_add_teacher = new moodle_url('/local/reportesnavarra/manager_teachers_categories.php', ['categoryid' => $category->categoryid]);
        $view_url_register = new moodle_url('/local/reportesnavarra/view_register_attendance.php', ['categoryid' => $category->categoryid]);
        $view_url_view_register = new moodle_url('/local/reportesnavarra/view_users_attendance.php', ['categoryid' => $category->categoryid]);
        $link_register = "<i class='fa fa-plus'></i>";
        $link_add_teacher = "<i class='fa fa-user-plus'></i>";
        $link_add_user = "<i class='fa fa-users'></i>";
        $link_view_register = "<i class='fa fa-list-ul'></i>";
        $action_add_teacher_link = '';
        $action_register_link = '';
        $action_view_register_link = '';
        if ($isadmin || has_capability('local/reportesnavarra:administration_users_categories', context_system::instance()))
            $action_add_user_link = html_writer::link($view_url_add_user, $link_add_user, ['title' => 'Agregar usuario a categoria']);

        if ($isadmin || has_capability('local/reportesnavarra:administration_register_teacher', $context))
            $action_add_teacher_link = html_writer::link($view_url_add_teacher, $link_add_teacher, ['title' => 'Agregar un nuevo profesor']);

        if ($isadmin || has_capability('local/reportesnavarra:administration_register_attendance', $context))
            $action_view_register_link = html_writer::link($view_url_register, $link_register,  ['title' => 'Registro de asistencias']);

        if ($isadmin && has_capability('local/reportesnavarra:gestor_register_attendance', $context))
            $action_register_link = html_writer::link($view_url_view_register, $link_view_register, ['title' => 'Ver registro de asistencias']);

        $table->data[] = [
            format_string($category->name),
            $action_add_user_link . ' ' .$action_add_teacher_link . ' ' . $action_view_register_link . ' ' . $action_register_link
        ];
    }
    return $table;
}

function local_reportesnavarra_get_courses_enrolled($userid)
{
    $courses = enrol_get_users_courses($userid, false, 'id, shortname, category, visible, showgrades');

    $arraycourse = array();
    if ($courses) {
        foreach ($courses as $course) {
            if ($course->visible == 1)
                array_push($arraycourse, array('courseid' => $course->id, 'shortname' => $course->shortname, 'fullname' => $course->fullname, 'category' => $course->category));
        }
    }

    return $arraycourse;
}



function local_reportesnavarra_get_picture_profile_for_template($userproperties)
{
    global $PAGE;
    //Buscar datos adicionales del susuario:

    $userpicture = new \user_picture($userproperties);
    $userpicture->size = 1;
    $pictureUrl = $userpicture->get_url($PAGE)->out(false);



    return $pictureUrl;
}


function local_reportesnavarra_get_grade_categories_config()
{
    $array = array(
        'first_trimester' => get_config('local_reportesnavarra', 'first_trimester_title'),
        'first_partial' => get_config('local_reportesnavarra', 'first_partial_title'),
        'second_partial' => get_config('local_reportesnavarra', 'second_partial_title'),
        'second_trimester' => get_config('local_reportesnavarra', 'second_trimester_title'),
        'third_partial' => get_config('local_reportesnavarra', 'third_partial_title'),
        'fourth_partial' => get_config('local_reportesnavarra', 'fourth_partial_title'),
        'third_trimester' => get_config('local_reportesnavarra', 'third_trimester_title'),
        'fifth_partial' => get_config('local_reportesnavarra', 'fifth_partial_title'),
        'sixth_partial' => get_config('local_reportesnavarra', 'sixth_partial_title'),
    );

    return $array;
}



function local_reportesnavarra_get_current_date_certificate()
{

    date_default_timezone_set('America/Mexico_City');

    // Obtener la fecha actual
    $fechaActual = new DateTime();

    // Configurar el objeto DateFormatter
    $dateFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Mexico_City', IntlDateFormatter::GREGORIAN);

    // Definir un formato personalizado para el día, el mes y el año
    $dateFormatter->setPattern('EEEE, d/MMMM/yyyy');

    // Formatear la fecha y aplicar mayúsculas a la primera letra de cada palabra
    $fechaFormateada = ucwords($dateFormatter->format($fechaActual));

    return $fechaFormateada;
}



function local_reportesnavarra_get_course_users_enrollments($courseid, $userfields = 'u.*', $roleids = [])
{
    global $DB;
    if ($courseid > 1) {
        $params = [];
        if (!empty($roleids)) {
            [$insql, $params] = $DB->get_in_or_equal($roleids);
            $rolessql = "AND r.id " . $insql;
        } else {
            $rolessql = "AND r.shortname IN ('editingteacher', 'teacher')";
        }

        $sql = "SELECT $userfields FROM {role_assignments} rs"
            . " INNER JOIN {user} u ON u.id=rs.userid"
            . " INNER JOIN {context} e ON rs.contextid=e.id"
            . " INNER JOIN {course} c ON c.id=e.instanceid"
            . " INNER JOIN {role} r ON r.id=rs.roleid"
            . " WHERE e.contextlevel=50 $rolessql AND c.id=?"
            . " ORDER BY u.firstname ";
        $params[] = $courseid;
        $rs = $DB->get_records_sql($sql, $params);
        return $rs;
    }
    return [];
}


function local_reportesnavarra_get_course_grade_category($courseId, $name)
{
    global $DB;

    $params = [$courseId, $name];

    $sql = "SELECT id, courseid,parent, fullname FROM {grade_categories}"
        . " WHERE courseid = ?  AND fullname = ?";

    $rs = reset($DB->get_records_sql($sql, $params));

    $category = new stdClass();

    $category->id = $rs->id;
    $category->name = $rs->fullname;

    return $category;
}

function local_reportesnavarra_get_course_grade_items($courseId, $type, $iteminstance)
{
    global $DB;

    $sql = "SELECT id, courseid,categoryid, itemname, itemtype, itemmodule, iteminstance FROM {grade_items}"
        . " WHERE courseid = ? AND itemtype = ? AND iteminstance = ?";

    $params = array($courseId, $type, $iteminstance);

    $rs = reset($DB->get_records_sql($sql, $params));

    $item = new stdClass();

    $item->id = $rs->id;

    return $item;
}

function local_reportesnavarra_get_course_grade_grade($userid, $itemid)
{
    global $DB;

    $sql = "SELECT id, itemid,userid, finalgrade FROM {grade_grades}"
        . " WHERE userid = ? AND itemid = ?";

    $params = array($userid, $itemid);

    $rs = reset($DB->get_records_sql($sql, $params));

    $grade = new stdClass();

    $grade->id = $rs->id;
    $grade->userid = $rs->userid;
    $grade->finalgrade = round($rs->finalgrade, 2);

    return $grade;
}

function get_date_range($period)
{
    switch ($period) {
        case 'first_trimester':
            return explode("-", get_config('local_reportesnavarra', '1erT'));
        case 'first_partial':
            return explode("-", get_config('local_reportesnavarra', '1erT1P'));
        case 'second_partial':
            return explode("-", get_config('local_reportesnavarra', '1erT2P'));
        case 'second_trimester':
            return explode("-", get_config('local_reportesnavarra', '2doT'));
        case 'third_partial':
            return explode("-", get_config('local_reportesnavarra', '2doT3P'));
        case 'fourth_partial':
            return explode("-", get_config('local_reportesnavarra', '2doT4P'));
        case 'third_trimester':
            return explode("-", get_config('local_reportesnavarra', '3erT'));
        case 'fifth_partial':
            return explode("-", get_config('local_reportesnavarra', '3erT5P'));
        case 'sixth_partial':
            return explode("-", get_config('local_reportesnavarra', '3erT6P'));

        default:
            # code...
            break;
    }
}

function convert_date_human_to_unix($date)
{
    $date = DateTime::createFromFormat("d/m/Y", $date);
    $unixtime = $date->getTimestamp();
    return $unixtime;
}
function get_days_attendance($period, $userid, $categoryid)
{
    global $DB;
    $dateRange = get_date_range($period);
    $dateIni = convert_date_human_to_unix($dateRange[0]);
    $dateEnd = convert_date_human_to_unix($dateRange[1]);
    $sql = "SELECT id, userid, status, categoryid, datestatus FROM {local_user_attendance}"
        . " WHERE userid = ? AND categoryid = ? AND datestatus >= ? AND datestatus <= ?";

    $params = array($userid, $categoryid, $dateIni, $dateEnd);

    $rs = $DB->get_records_sql($sql, $params);

    $days_attended = 0;
    $faults = 0;
    $arrears = 0;
    $justified_absences = 0;
    $justified_delays = 0;
    foreach ($rs as $row) {
        if ($row->status == get_config('local_reportesnavarra', 'days_attended')) {
            $days_attended++;
        } else if ($row->status == get_config('local_reportesnavarra', 'faults')) {
            $faults++;
        } else if ($row->status == get_config('local_reportesnavarra', 'arrears')) {
            $arrears++;
        } else if ($row->status == get_config('local_reportesnavarra', 'justified_absences')) {
            $justified_absences++;
        } else if ($row->status == get_config('local_reportesnavarra', 'justified_delays')) {
            $justified_delays++;
        }
    }
    $array = new stdClass();
    $array->days_attended = $days_attended;
    $array->faults = $faults;
    $array->arrears = $arrears;
    $array->justified_absences = $justified_absences;
    $array->justified_delays = $justified_delays;

    return $array;
}


function local_reportesnavarra_get_format_date_certificate($date)
{
    date_default_timezone_set('America/Mexico_City');

    // Crear un objeto DateTime a partir del timestamp Unix
    $fecha = new DateTime('@' . $date);

    // Configurar el objeto DateFormatter
    $dateFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Mexico_City', IntlDateFormatter::GREGORIAN);

    // Definir un formato personalizado para el día, el mes y el año
    $dateFormatter->setPattern('d/MMMM/yyyy');

    // Formatear la fecha y aplicar mayúsculas a la primera letra de cada palabra
    $fechaFormateada = ucwords($dateFormatter->format($fecha));

    return $fechaFormateada;
    //return strftime('%e/%b/%Y', $date);
}

function local_reportesnavarra_get_average($activies, $userId)
{
    global $DB;
    $cont = 0;
    $gradeSum = 0;
    $average = 0;
    foreach ($activies as $activy) {
        $sql = "SELECT id, itemid,userid, finalgrade FROM {grade_grades}"
            . " WHERE itemid = ? AND userid = ?";

        $params = array($activy->id, $userId);

        $rs = $DB->get_record_sql($sql, $params);
   
        if ($rs->finalgrade > 0) {

            $cont++;
            $gradeSum = $gradeSum + $rs->finalgrade;
        } else if ($rs->finalgrade == 0 && $rs->finalgrade !== NULL) {
            $cont++;
            $gradeSum = $gradeSum + $rs->finalgrade;
        }


    }
    if ($cont > 0) {
        $average = local_reportesnavarra_round_especial(($gradeSum / $cont));
    }

    return $average;
}

function local_reportesnavarra_get_grade_grade($itemId, $userId)
{
    global $DB;

    $grade = '';

    $sql = "SELECT id, itemid,userid, finalgrade FROM {grade_grades}"
        . " WHERE itemid = ? AND userid = ?";

    $params = array($itemId, $userId);

    $rs = $DB->get_record_sql($sql, $params);
    if ($rs->finalgrade > 0) {

        $grade = $rs->finalgrade;
        $grade = round($grade, 2);
    } else if ($rs->finalgrade == 0 && $rs->finalgrade !== NULL) {
        $grade = 0;
    } else if ($rs->finalgrade == NULL) {
        $grade = '?';
    }

    return $grade;
}

function local_reportesnavarra_round_especial($numero)
{
    return round($numero, 2, PHP_ROUND_HALF_UP);
}

function local_reportesnavarra_get_stundents_enrolled_in_course_category($courses, $categoryid)
{
    foreach ($courses as $course) {
        if ($course['category'] == $categoryid) {
            $students = local_grade_get_course_users_enrollments($course->id, 'u.*', [5]);
            return $students;
        }
    }
    die;
}

function local_reportesnavarra_get_period_for_certificate($period)
{

    switch ($period) {
        case 'first_trimester':
            return get_config('local_reportesnavarra', 'first_trimester_title');
            break;
        case 'first_partial':
            return get_config('local_reportesnavarra', 'first_partial_title');
            break;
        case 'second_partial':
            return get_config('local_reportesnavarra', 'second_partial_title');
            break;
        case 'second_trimester':
            return get_config('local_reportesnavarra', 'second_trimester_title');
            break;
        case 'third_partial':
            return get_config('local_reportesnavarra', 'third_partial_title');
            break;
        case 'fourth_partial':
            return get_config('local_reportesnavarra', 'fourth_partial_title');
            break;
        case 'third_trimester':
            return get_config('local_reportesnavarra', 'third_trimester_title');
            break;
        case 'fifth_partial':
            return get_config('local_reportesnavarra', 'fifth_partial_title');
            break;
        case 'sixth_partial':
            return get_config('local_reportesnavarra', 'sixth_partial_title');
            break;
        default:
            return '';
            break;
    }
}

function local_reportesnavarra_get_period_title_for_certificate($period)
{

    switch ($period) {
        case 'first_trimester':
            return get_string('first_trimester', 'local_reportesnavarra');
            break;
        case 'first_partial':
            return get_string('first_partial', 'local_reportesnavarra');
            break;
        case 'second_partial':
            return get_string('second_partial', 'local_reportesnavarra');
            break;
        case 'second_trimester':
            return get_string('second_trimester', 'local_reportesnavarra');
            break;
        case 'third_partial':
            return get_string('third_partial', 'local_reportesnavarra');
            break;
        case 'fourth_partial':
            return get_string('fourth_partial', 'local_reportesnavarra');
            break;
        case 'third_trimester':
            return get_string('third_trimester', 'local_reportesnavarra');
            break;
        case 'fifth_partial':
            return get_string('fifth_partial', 'local_reportesnavarra');
            break;
        case 'sixth_partial':
            return get_string('sixth_partial', 'local_reportesnavarra');
            break;
        default:
            return '';
            break;
    }
}

function local_reportesnavarra_get_period_title_for_grade_category($period)
{

    switch ($period) {
        case 'first_trimester':
            return get_config('local_reportesnavarra', 'first_trimester');
            break;
        case 'first_partial':
            return get_config('local_reportesnavarra', 'first_partial');
            break;
        case 'second_partial':
            return get_config('local_reportesnavarra', 'second_partial');
            break;
        case 'second_trimester':
            return get_config('local_reportesnavarra', 'second_trimester');
            break;
        case 'third_partial':
            return get_config('local_reportesnavarra', 'third_partial');
            break;
        case 'fourth_partial':
            return get_config('local_reportesnavarra', 'fourth_partial');
            break;
        case 'third_trimester':
            return get_config('local_reportesnavarra', 'third_trimester');
            break;
        case 'fifth_partial':
            return get_config('local_reportesnavarra', 'fifth_partial');
            break;
        case 'sixth_partial':
            return get_config('local_reportesnavarra', 'sixth_partial');
            break;
        default:
            return '';
            break;
    }
}

function splitLastCharacter($text)
{
    $length = mb_strlen($text, 'UTF-8'); // Get string length
    if ($length === 0) return ["remaining" => "", "last" => ""]; // Handle empty string

    $remaining = mb_substr($text, 0, $length - 1, 'UTF-8'); // Everything except the last character
    $last = mb_substr($text, -1, 1, 'UTF-8'); // Last character

    return ["course" => trim($remaining), "parallel" => $last];
}

function local_reportesnavarra_get_course_category_by_period($courseid, $categoryName)
{
    global $DB;

    $params = [$courseid, $categoryName];

    $sql = "SELECT id, courseid,parent, fullname FROM {grade_categories}"
        . " WHERE courseid = ? AND fullname = ?"
        . " ORDER BY id ASC";


    $rs = $DB->get_records_sql($sql, $params);

    return $rs;
}

function local_reportes_navarra_get_grade_cualitative($grade)
{
    $grade_aplus = explode('-', get_config('local_reportesnavarra', 'grade_aplus'));
    $grade_aminus = explode('-', get_config('local_reportesnavarra', 'grade_aminus'));
    $grade_bplus = explode('-', get_config('local_reportesnavarra', 'grade_bplus'));
    $grade_bminus = explode('-', get_config('local_reportesnavarra', 'grade_bminus'));
    $grade_cplus = explode('-', get_config('local_reportesnavarra', 'grade_cplus'));
    $grade_cminus = explode('-', get_config('local_reportesnavarra', 'grade_cminus'));
    $grade_dplus = explode('-', get_config('local_reportesnavarra', 'grade_dplus'));
    $grade_dminus = explode('-', get_config('local_reportesnavarra', 'grade_dminus'));
    $grade_eplus = explode('-', get_config('local_reportesnavarra', 'grade_eplus'));
    $grade_eminus = explode('-', get_config('local_reportesnavarra', 'grade_eminus'));

    if ($grade >= $grade_aplus[0] && $grade <= $grade_aplus[1]) {
        return get_string('aplus', 'local_reportesnavarra');
    } else if ($grade >= $grade_aminus[0] && $grade <= $grade_aminus[1]) {
        return get_string('aminus', 'local_reportesnavarra');
    } else if ($grade >= $grade_bplus[0] && $grade <= $grade_bplus[1]) {
        return get_string('bplus', 'local_reportesnavarra');
    } else if ($grade >= $grade_bminus[0] && $grade <= $grade_bminus[1]) {
        return get_string('bminus', 'local_reportesnavarra');
    } else if ($grade >= $grade_cplus[0] && $grade <= $grade_cplus[1]) {
        return get_string('cplus', 'local_reportesnavarra');
    } else if ($grade >= $grade_cminus[0] && $grade <= $grade_cminus[1]) {
        return get_string('cminus', 'local_reportesnavarra');
    } else if ($grade >= $grade_dplus[0] && $grade <= $grade_dplus[1]) {
        return get_string('dplus', 'local_reportesnavarra');
    } else if ($grade >= $grade_dminus[0] && $grade <= $grade_dminus[1]) {
        return get_string('dminus', 'local_reportesnavarra');
    } else if ($grade >= $grade_eplus[0] && $grade <= $grade_eplus[1]) {
        return get_string('eplus', 'local_reportesnavarra');
    } else if ($grade >= $grade_eminus[0] && $grade <= $grade_eminus[1]) {
        return get_string('eminus', 'local_reportesnavarra');
    } else {
        return 'F';
    }
}

function local_reportes_navarra_get_grade_comportamental($grade)
{
    $always = get_config('local_reportesnavarra', 'always');
    $frequently = explode('-', get_config('local_reportesnavarra', 'frequently'));
    $occasionally = explode('-', get_config('local_reportesnavarra', 'occasionally'));
    $never = explode('-', get_config('local_reportesnavarra', 'never'));

    if ($always == $grade) {
        return get_string('always_conducta', 'local_reportesnavarra');
    } else if ($grade >= $frequently[0] && $grade <= $frequently[1]) {
        return get_string('frequently_conducta', 'local_reportesnavarra');
    } else if ($grade >= $occasionally[0] && $grade <= $occasionally[1]) {
        return get_string('occasionally_conducta', 'local_reportesnavarra');
    } else if ($grade >= $never[0] && $grade <= $never[1]) {
        return get_string('never_conducta', 'local_reportesnavarra');
    } else {
        return '';
    }
}

function get_qualitative_scale_text($grade)
{
    if ($grade == 'A+' or $grade == 'A-' or $grade == 'B+') {
        return get_string('qualitative_scale_text1', 'local_reportesnavarra');
    } else if ($grade == 'B-' or $grade == 'C+' or $grade == 'C-') {
        return get_string('qualitative_scale_text2', 'local_reportesnavarra');
    } else if ($grade == 'D+' or $grade == 'D-' or $grade == 'E+' or $grade == 'E-') {
        return get_string('qualitative_scale_text3', 'local_reportesnavarra');
    } else {
        return '';
    }
}

function get_comportamental_escale_text($grade)
{
    switch ($grade) {
        case 'S':
            return get_string('comportamental_scale_s', 'local_reportesnavarra');
            break;
        case 'F':
            return get_string('comportamental_scale_f', 'local_reportesnavarra');
            break;
        case 'O':
            return get_string('comportamental_scale_o', 'local_reportesnavarra');
            break;
        case 'N':
            return get_string('comportamental_scale_n', 'local_reportesnavarra');
            break;
        default:
            return '';
            break;
    }
}

function local_grade_download_certificate($USER, $period, $categoryid = null)
{
    global $CFG;

    $imgSchool1 = $CFG->dirroot . '/local/reportesnavarra/images/logo1.jpeg';
    $imgSchool2 = $CFG->dirroot . '/local/reportesnavarra/images/logo2.jpeg';

    $pdf = new CustomPDF(null, null);


    // set document information
    $pdf->SetCreator('NAVARRA');
    $pdf->SetAuthor('NAVARRA');
    $pdf->SetTitle('Boletin de notas');
    $pdf->SetSubject('NAVARRA');
    $pdf->SetKeywords('NAVARRA, PDF, Boletin de notas');

    $pdf->setPrintHeader(true);
    //    $pdf->SetHeaderData($imgSchool, 30, 'asdasdasd', 'asdasd', null, null);

    // set header and footer fonts
    $pdf->setHeaderFont(array('helvetica', '', 10));
    $pdf->setFooterFont(array('helvetica', '', 8));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont('courier');

    // set margins
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // set image scale factor
    //    $pdf->setImageScale(1.25);


    // set font
    $pdf->SetFont('helvetica', 'B', 12);

    // add a page
    $pdf->AddPage();


    $pdf->SetFont('helvetica', '', 8);

    $pdf->Image($imgSchool1, 20, 10, 15, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);

    $pdf->Image($imgSchool2, 170, 10, 15, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);

    // // -----------------------------------------------------------------------------
    $title = get_config('local_reportesnavarra', 'title');
    $direction = get_config('local_reportesnavarra', 'address');
    $phone = get_config('local_reportesnavarra', 'phone');
    $mail = get_config('local_reportesnavarra', 'email');
    $year = get_config('local_reportesnavarra', 'yearSchool');
    $subtitleCertificate = get_string('subtitle_certificate', 'local_reportesnavarra');

    $periodTitle = local_reportesnavarra_get_period_for_certificate($period);
    $tableTitle = <<<EOD
<br>
<table border="0" cellspacing="1" cellpadding="1" style="border: 0px solid #FFFFFF;">
  <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$title}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$direction}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">TELF: {$phone}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">CORREO: {$mail}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">AÑO LECTIVO: {$year}</td>
  </tr>
    <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$subtitleCertificate} {$periodTitle}</td>
  </tr>
</table>

EOD;
    $pdf->writeHTML($tableTitle, true, false, false, false, '');
    //     //Obtención de los datos generales
    //Titles
    $academic_year_title = get_string('academic_year_header', 'local_reportesnavarra');
    $student_title = get_string('student_header', 'local_reportesnavarra');
    $course_title = get_string('course_header', 'local_reportesnavarra');
    $workday_title = get_string('workday_header', 'local_reportesnavarra');
    $period_title = get_string('period_header', 'local_reportesnavarra');
    $parallel_title = get_string('parallel_header', 'local_reportesnavarra');
    $teacher_title = get_string('teacher_header', 'local_reportesnavarra');

    //Title Values
    $student_title = get_string('student_header', 'local_reportesnavarra');
    $period_title = get_string('period_header', 'local_reportesnavarra');
    $workday_title = get_string('workday_header', 'local_reportesnavarra');
    $workday = get_config('local_reportesnavarra', 'day');
    $yearSchool = get_config('local_reportesnavarra', 'yearSchool');
    $studentName = strtoupper($USER->firstname . ' ' . $USER->lastname);
    $periodValue = local_reportesnavarra_get_period_title_for_certificate($period);
    $conditions = $conditions = 'cc.id = ?';

    $params =  ['cc.id' => $categoryid];
    $fields = 'cc.name, cc.id, u.firstname, u.lastname';
    $categories = local_reportesnavarra_get_teachers_categories($fields, $conditions, $params);

    $categoryName = '';
    $teacherName = '';
    foreach ($categories as $category) {
        // var_dump($category);
        $teacherName = strtoupper($category->firstname . ' ' . $category->lastname);
        $categoryName = $category->name;
    }
    $categoryName = splitLastCharacter($categoryName);
    // var_dump($categoryName);
    $categoryNameValue = $categoryName['course'];
    $categoryParallelValue = $categoryName['parallel'];
    $profesionalTitle = get_config('local_reportesnavarra', 'profesional_title');

    $tableHeader = "";
    $tableHeader .= <<<EOD
    <table cellspacing="0" cellpadding="1" style="border-collapse: collapse; width: 100%;">
    <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$academic_year_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$yearSchool</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$workday_title</td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;">$workday</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$student_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$studentName</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$period_title</td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;">$periodValue</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$course_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$categoryNameValue</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$parallel_title</td>
        <td width="31%" colspan="4" style="text-align: left; ont-size: 7rem;">$categoryParallelValue</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$teacher_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$profesionalTitle $teacherName</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;"></td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;"></td>
        </tr>
        </table>
    EOD;


    $pdf->writeHTML($tableHeader, true, false, false, false, '');
    $pdf->SetCellPadding(1);
    //Inicio de la tabla de calificación
    $tableGrade = "";
    $tableGrade .= <<<EOD
    <table cellspacing="0" cellpadding="2" style="border-collapse: collapse; width: 100%; border: 1px solid #000000;">
    EOD;

    $tableGrade .= <<<EOD
        <tr>
            <td width="70%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem;">ASIGNATURA</td>
            <td width="10%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA CUANTITATIVA</td>
            <td width="9%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA CUALITATIVA</td>
            <td width="11%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA COMPORTAMENTAL</td>
        </tr>

    EOD;
    //Listado de cursos

    $courses = local_reportesnavarra_get_courses_enrolled($USER->id);
    $categoryGradeName = local_reportesnavarra_get_period_title_for_grade_category($period);

    //first_trimester
    $totalGradeCourse = 0;
    $totalCourses = count($courses);
    foreach ($courses as $course) {
        if ($course['category'] == $categoryid) {
            //Obtengo la categoria principal
            $categoriesPrincipal = reset(local_reportesnavarra_get_course_category_by_period($course['courseid'], $categoryGradeName));
            //Obtengo el item de calificación
            $gradeItem = local_reportesnavarra_get_course_grade_items($course['courseid'], 'category', $categoriesPrincipal->id);
            //Obtengo la calificación
            $grade = local_reportesnavarra_get_course_grade_grade($USER->id, $gradeItem->id);
            $finalGrade = $grade->finalgrade;
            $totalGradeCourse += $finalGrade;
            $courseName = strtoupper($course['fullname']);

            $gradeCualitative = local_reportes_navarra_get_grade_cualitative($finalGrade);
            $gradeComportamental = local_reportes_navarra_get_grade_comportamental($finalGrade);
            $tableGrade .= <<<EOD
                <tr>
                    <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-size: 7rem; padding: 20px;">$courseName</td>
                    <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$finalGrade</td>
                    <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeCualitative</td>
                    <td width="11%" style="border: 0.5px solid #000000; text-align: center;  font-size: 7rem; padding: 20px;">$gradeComportamental</td>
                </tr>
            EOD;
        }
    }
    $tableGrade .= <<<EOD
    <tr>
        <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="11%" style="border: 0.5px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
    </tr>
    <tr>
        <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="11%" style="border: 0.5px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
    </tr>

EOD;

    //Promedio del estudiante
    $gradeAvarage = ($totalGradeCourse / $totalCourses);
    $gradeAvarageCualitative = local_reportes_navarra_get_grade_cualitative($gradeAvarage);
    $gradeAvarageComportamental = local_reportes_navarra_get_grade_comportamental($gradeAvarage);
    $tableGrade .= <<<EOD
        <tr>
            <td width="70%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">PROMEDIO DEL ESTUDIANTE</td>
            <td width="10%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeAvarage</td>
            <td width="9%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeAvarageCualitative</td>
            <td width="11%" style="border: 1px solid #000000; text-align: center;  font-size:7rem; padding: 20px;">$gradeAvarageComportamental</td>
        </tr>

    EOD;
    //Fila de observaciones
    $textQualitativeText = get_qualitative_scale_text($gradeAvarageCualitative);
    $textComportamentalText = strtoupper(get_comportamental_escale_text($gradeAvarageComportamental));
    $tableGrade .= <<<EOD
    <tr>
        <td width="25%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 20px;">OBSERVACIONES:</td>
        <td width="75%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;">$textQualitativeText</td>
    </tr>
     <tr>
        <td width="25%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 30px;">RECOMENDACIONES:</td>
        <td width="75%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 30px;">$textComportamentalText</td>
    </tr>
    EOD;
    //Cierre de la tabla
    $tableGrade .= <<<EOD
    </table>
    EOD;

    //Table descripcion qualitativa

    $tableGrade .= <<<EOD
    <br><br>
    <table cellspacing="0" cellpadding="2" style="border-collapse: collapse; width: 100%; border: 1px solid #000000;">
    EOD;
    $qualitativeText1 = get_string('qualitative_scale_text1', 'local_reportesnavarra');
    $qualitativeText2 = get_string('qualitative_scale_text2', 'local_reportesnavarra');
    $qualitativeText3 = get_string('qualitative_scale_text3', 'local_reportesnavarra');
    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">ESCALA CUALITATIVA</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">A+</td>
        <td width="70%" rowspan = "3" style="border: 1px solid #000000; vertical-align: middle; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText1
            </div>
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center;  font-size: 7rem; padding: 20px;">9,01 -10</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">A-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">8,01 - 9</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">B+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">7,01 - 8</td>
    </tr>
 
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">B-</td>
        <td width="70%" rowspan = "3" style="border: 1px solid #000000; vertical-align: middle; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText2
            </div> 
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">6,01 - 7</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">C+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">5,01 - 6</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">C-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">4,01 - 5</td>
    </tr>
  
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">D+</td>
        <td width="70%" rowspan = "4" style="border: 1px solid #000000; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText3
            </div>
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">3,01 - 4</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">D-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">2,01 - 3</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">E+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">1,01 - 2</td>
    </tr>
     <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">E-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">0,01 - 1</td>
    </tr>
    EOD;

    $tableGrade .= <<<EOD
    </table>
    EOD;
    $attendances = get_days_attendance($period, $USER->id, $categoryid);
    $days_attended = $attendances->days_attended;
    $faults = $attendances->faults;
    $arrears = $attendances->arrears;
    $justified_absences = $attendances->justified_absences;
    $justified_delays = $attendances->justified_delays;

    $tableGrade .= <<<EOD
    <br><br>
    <table style="width: 100%; border-collapse: collapse;">
    EOD;

    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 40px;">
             FALTAS Y ATRASOS
        </td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 80px;">DIAS ASISTIDOS</td>
        <td width="70%" style="border: 1px solid #000000; text-align: center;  font-size: 7rem; padding: 80px;">$days_attended</td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 40px;">FALTAS</td>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px;">$faults</td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px; font-weight: bold;">ATRASOS</td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px;">$arrears</td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; ">
                FALTAS JUSTIFICADAS
        </td>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-size: 7rem;">
               $justified_absences
        </td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; font-weight: bold;">
                ATRASOS JUSTIFICADOS
        </td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem;">
                $justified_delays
        </td>
    </tr>
    EOD;

    $tableGrade .= <<<EOD
    </table>
    EOD;

    $tableGrade .= <<<EOD
    <br><br>
    <table style="width: 100%; border-collapse: collapse;">
    EOD;

    $criterio1 = get_string('comportamental_scale_s', 'local_reportesnavarra');
    $criterio2 = get_string('comportamental_scale_f', 'local_reportesnavarra');
    $criterio3 = get_string('comportamental_scale_o', 'local_reportesnavarra');
    $criterio4 = get_string('comportamental_scale_n', 'local_reportesnavarra');
    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem;">
                PARÁMETROS DE CONDUCTA
        </td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">S - SIEMPRE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio1</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">F - FRECUENTEMENTE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio2</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">O - OCASIONALMENTE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio3</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">N - NUNCA</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio4</td>
    </tr>
   
    EOD;

    $tableGrade .= <<<EOD
    </table><br><br><br><br><br><br>
    EOD;
    $signature = get_config('local_reportesnavarra', 'signature');
    $tableGrade .= <<<EOD
    <tr>
        <td style="border: none; text-align: center; padding-top: 30px; width: 45%;">
            <!-- Línea de firma 1 -->
            <div style="width: 200px; border-top: 2px solid #000000; margin-left: auto; margin-right: auto;">
                <div style="text-align: center; font-size: 10px; padding-top: 5px;">$signature</div>
            </div>
        </td>
        <td style="border: none; text-align: center; padding-top: 30px; width: 10%;">&nbsp;</td> <!-- Celda para separación -->
        <td style="border: none; text-align: center; padding-top: 30px; width: 45%;">
            <!-- Línea de firma 2 -->
            <div style="width: 200px; border-top: 2px solid #000000; margin-left: auto; margin-right: auto;">
                <div style="text-align: center; font-size: 10px; padding-top: 5px;">$profesionalTitle $teacherName</div>
            </div>
        </td>
    </tr>
EOD;



    $pdf->writeHTML($tableGrade, true, false, false, false, '');



    $tableComplete = "";
    $pdf->writeHTML($tableComplete, true, false, false, false, '');
    //Close and output PDF document
    $pdf->Output( $studentName, 'I');
}

function local_grade_download_certificate_by_category($USER, $period, $categoryid = null) {
    global $CFG;
    $pdfDir = $CFG->dataroot. "/temp/pdfs/";

    $zip = new ZipArchive();
    $zipFilename = 'archivos_pdfs.zip';

    if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
        exit("No se pudo crear el archivo ZIP.");
    }

    $pdfFiles = []; // Array para almacenar los nombres de los PDFs

    $imgSchool1 = $CFG->dirroot . '/local/reportesnavarra/images/logo1.jpeg';
    $imgSchool2 = $CFG->dirroot . '/local/reportesnavarra/images/logo2.jpeg';

    $pdf = new CustomPDF(null, null);


    // set document information
    $pdf->SetCreator('NAVARRA');
    $pdf->SetAuthor('NAVARRA');
    $pdf->SetTitle('Boletin de notas');
    $pdf->SetSubject('NAVARRA');
    $pdf->SetKeywords('NAVARRA, PDF, Boletin de notas');

    $pdf->setPrintHeader(true);
    //    $pdf->SetHeaderData($imgSchool, 30, 'asdasdasd', 'asdasd', null, null);

    // set header and footer fonts
    $pdf->setHeaderFont(array('helvetica', '', 10));
    $pdf->setFooterFont(array('helvetica', '', 8));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont('courier');

    // set margins
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // set image scale factor
    //    $pdf->setImageScale(1.25);


    // set font
    $pdf->SetFont('helvetica', 'B', 12);

    // add a page
    $pdf->AddPage();


    $pdf->SetFont('helvetica', '', 8);

    $pdf->Image($imgSchool1, 20, 10, 15, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);

    $pdf->Image($imgSchool2, 170, 10, 15, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);

    // // -----------------------------------------------------------------------------
    $title = get_config('local_reportesnavarra', 'title');
    $direction = get_config('local_reportesnavarra', 'address');
    $phone = get_config('local_reportesnavarra', 'phone');
    $mail = get_config('local_reportesnavarra', 'email');
    $year = get_config('local_reportesnavarra', 'yearSchool');
    $subtitleCertificate = get_string('subtitle_certificate', 'local_reportesnavarra');

    $periodTitle = local_reportesnavarra_get_period_for_certificate($period);
    $tableTitle = <<<EOD
<br>
<table border="0" cellspacing="1" cellpadding="1" style="border: 0px solid #FFFFFF;">
  <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$title}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$direction}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">TELF: {$phone}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">CORREO: {$mail}</td>
  </tr>
   <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">AÑO LECTIVO: {$year}</td>
  </tr>
    <tr>
    <td style="border: 0px solid #FFFFFF; text-align: center; font-weight: bold; font-size: medium; color: #000000; " colspan="5">{$subtitleCertificate} {$periodTitle}</td>
  </tr>
</table>

EOD;
    $pdf->writeHTML($tableTitle, true, false, false, false, '');
    //     //Obtención de los datos generales
    //Titles
    $academic_year_title = get_string('academic_year_header', 'local_reportesnavarra');
    $student_title = get_string('student_header', 'local_reportesnavarra');
    $course_title = get_string('course_header', 'local_reportesnavarra');
    $workday_title = get_string('workday_header', 'local_reportesnavarra');
    $period_title = get_string('period_header', 'local_reportesnavarra');
    $parallel_title = get_string('parallel_header', 'local_reportesnavarra');
    $teacher_title = get_string('teacher_header', 'local_reportesnavarra');

    //Title Values
    $student_title = get_string('student_header', 'local_reportesnavarra');
    $period_title = get_string('period_header', 'local_reportesnavarra');
    $workday_title = get_string('workday_header', 'local_reportesnavarra');
    $workday = get_config('local_reportesnavarra', 'day');
    $yearSchool = get_config('local_reportesnavarra', 'yearSchool');
    $studentName = strtoupper($USER->firstname . ' ' . $USER->lastname);
    $periodValue = local_reportesnavarra_get_period_title_for_certificate($period);
    $conditions = $conditions = 'cc.id = ?';

    $params =  ['cc.id' => $categoryid];
    $fields = 'cc.name, cc.id, u.firstname, u.lastname';
    $categories = local_reportesnavarra_get_teachers_categories($fields, $conditions, $params);

    $categoryName = '';
    $teacherName = '';
    foreach ($categories as $category) {
        // var_dump($category);
        $teacherName = strtoupper($category->firstname . ' ' . $category->lastname);
        $categoryName = $category->name;
    }
    $categoryName = splitLastCharacter($categoryName);
    // var_dump($categoryName);
    $categoryNameValue = $categoryName['course'];
    $categoryParallelValue = $categoryName['parallel'];
    $profesionalTitle = get_config('local_reportesnavarra', 'profesional_title');

    $tableHeader = "";
    $tableHeader .= <<<EOD
    <table cellspacing="0" cellpadding="1" style="border-collapse: collapse; width: 100%;">
    <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$academic_year_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$yearSchool</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$workday_title</td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;">$workday</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$student_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$studentName</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$period_title</td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;">$periodValue</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$course_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$categoryNameValue</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$parallel_title</td>
        <td width="31%" colspan="4" style="text-align: left; ont-size: 7rem;">$categoryParallelValue</td>
        </tr>
        <tr>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;">$teacher_title</td>
        <td width="45%" colspan="4" style="text-align: left; font-size: 7rem;">$profesionalTitle $teacherName</td>
        <td width="12%" style="text-align: left; font-weight: bold; font-size: 7rem;"></td>
        <td width="31%" colspan="4" style="text-align: left; font-size: 7rem;"></td>
        </tr>
        </table>
    EOD;


    $pdf->writeHTML($tableHeader, true, false, false, false, '');
    $pdf->SetCellPadding(1);
    //Inicio de la tabla de calificación
    $tableGrade = "";
    $tableGrade .= <<<EOD
    <table cellspacing="0" cellpadding="2" style="border-collapse: collapse; width: 100%; border: 1px solid #000000;">
    EOD;

    $tableGrade .= <<<EOD
        <tr>
            <td width="70%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem;">ASIGNATURA</td>
            <td width="10%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA CUANTITATIVA</td>
            <td width="9%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA CUALITATIVA</td>
            <td width="11%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 5rem;">NOTA COMPORTAMENTAL</td>
        </tr>

    EOD;
    //Listado de cursos

    $courses = local_reportesnavarra_get_courses_enrolled($USER->id);
    $categoryGradeName = local_reportesnavarra_get_period_title_for_grade_category($period);

    //first_trimester
    $totalGradeCourse = 0;
    $totalCourses = count($courses);
    foreach ($courses as $course) {
        if ($course['category'] == $categoryid) {
            //Obtengo la categoria principal
            $categoriesPrincipal = reset(local_reportesnavarra_get_course_category_by_period($course['courseid'], $categoryGradeName));
            //Obtengo el item de calificación
            $gradeItem = local_reportesnavarra_get_course_grade_items($course['courseid'], 'category', $categoriesPrincipal->id);
            //Obtengo la calificación
            $grade = local_reportesnavarra_get_course_grade_grade($USER->id, $gradeItem->id);
            $finalGrade = $grade->finalgrade;
            $totalGradeCourse += $finalGrade;
            $courseName = strtoupper($course['fullname']);

            $gradeCualitative = local_reportes_navarra_get_grade_cualitative($finalGrade);
            $gradeComportamental = local_reportes_navarra_get_grade_comportamental($finalGrade);
            $tableGrade .= <<<EOD
                <tr>
                    <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-size: 7rem; padding: 20px;">$courseName</td>
                    <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$finalGrade</td>
                    <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeCualitative</td>
                    <td width="11%" style="border: 0.5px solid #000000; text-align: center;  font-size: 7rem; padding: 20px;">$gradeComportamental</td>
                </tr>
            EOD;
        }
    }
    $tableGrade .= <<<EOD
    <tr>
        <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="11%" style="border: 0.5px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
    </tr>
    <tr>
        <td width="70%" style="border: 0.1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="10%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="9%" style="border: 0.1px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
        <td width="11%" style="border: 0.5px solid #000000; text-align: center; font-weight: bold; font-size: 6rem; padding: 20px;"></td>
    </tr>

EOD;

    //Promedio del estudiante
    $gradeAvarage = ($totalGradeCourse / $totalCourses);
    $gradeAvarageCualitative = local_reportes_navarra_get_grade_cualitative($gradeAvarage);
    $gradeAvarageComportamental = local_reportes_navarra_get_grade_comportamental($gradeAvarage);
    $tableGrade .= <<<EOD
        <tr>
            <td width="70%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">PROMEDIO DEL ESTUDIANTE</td>
            <td width="10%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeAvarage</td>
            <td width="9%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">$gradeAvarageCualitative</td>
            <td width="11%" style="border: 1px solid #000000; text-align: center;  font-size:7rem; padding: 20px;">$gradeAvarageComportamental</td>
        </tr>

    EOD;
    //Fila de observaciones
    $textQualitativeText = get_qualitative_scale_text($gradeAvarageCualitative);
    $textComportamentalText = strtoupper(get_comportamental_escale_text($gradeAvarageComportamental));
    $tableGrade .= <<<EOD
    <tr>
        <td width="25%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 20px;">OBSERVACIONES:</td>
        <td width="75%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 20px;">$textQualitativeText</td>
    </tr>
     <tr>
        <td width="25%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 30px;">RECOMENDACIONES:</td>
        <td width="75%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 6rem; padding: 30px;">$textComportamentalText</td>
    </tr>
    EOD;
    //Cierre de la tabla
    $tableGrade .= <<<EOD
    </table>
    EOD;

    //Table descripcion qualitativa

    $tableGrade .= <<<EOD
    <br><br>
    <table cellspacing="0" cellpadding="2" style="border-collapse: collapse; width: 100%; border: 1px solid #000000;">
    EOD;
    $qualitativeText1 = get_string('qualitative_scale_text1', 'local_reportesnavarra');
    $qualitativeText2 = get_string('qualitative_scale_text2', 'local_reportesnavarra');
    $qualitativeText3 = get_string('qualitative_scale_text3', 'local_reportesnavarra');
    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">ESCALA CUALITATIVA</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">A+</td>
        <td width="70%" rowspan = "3" style="border: 1px solid #000000; vertical-align: middle; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText1
            </div>
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center;  font-size: 7rem; padding: 20px;">9,01 -10</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">A-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">8,01 - 9</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">B+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">7,01 - 8</td>
    </tr>
 
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">B-</td>
        <td width="70%" rowspan = "3" style="border: 1px solid #000000; vertical-align: middle; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText2
            </div> 
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">6,01 - 7</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">C+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">5,01 - 6</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">C-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">4,01 - 5</td>
    </tr>
  
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">D+</td>
        <td width="70%" rowspan = "4" style="border: 1px solid #000000; text-align: left; font-size: 7rem; padding: 60px;">
            <div style="display: flex; align-items: left; justify-content: center; height: 100%;">
                $qualitativeText3
            </div>
        </td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">3,01 - 4</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">D-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">2,01 - 3</td>
    </tr>
    <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">E+</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">1,01 - 2</td>
    </tr>
     <tr>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 20px;">E-</td>
        <td width="15%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 20px;">0,01 - 1</td>
    </tr>
    EOD;

    $tableGrade .= <<<EOD
    </table>
    EOD;
    $attendances = get_days_attendance($period, $USER->id, $categoryid);
    $days_attended = $attendances->days_attended;
    $faults = $attendances->faults;
    $arrears = $attendances->arrears;
    $justified_absences = $attendances->justified_absences;
    $justified_delays = $attendances->justified_delays;

    $tableGrade .= <<<EOD
    <br><br>
    <table style="width: 100%; border-collapse: collapse;">
    EOD;

    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 40px;">
             FALTAS Y ATRASOS
        </td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 80px;">DIAS ASISTIDOS</td>
        <td width="70%" style="border: 1px solid #000000; text-align: center;  font-size: 7rem; padding: 80px;">$days_attended</td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; padding: 40px;">FALTAS</td>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px;">$faults</td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px; font-weight: bold;">ATRASOS</td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; padding: 40px;">$arrears</td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem; ">
                FALTAS JUSTIFICADAS
        </td>
        <td width="30%" style="border: 1px solid #000000; text-align: center; font-size: 7rem;">
               $justified_absences
        </td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem; font-weight: bold;">
                ATRASOS JUSTIFICADOS
        </td>
        <td width="20%" style="border: 1px solid #000000; text-align: center; font-size: 7rem;">
                $justified_delays
        </td>
    </tr>
    EOD;

    $tableGrade .= <<<EOD
    </table>
    EOD;

    $tableGrade .= <<<EOD
    <br><br>
    <table style="width: 100%; border-collapse: collapse;">
    EOD;

    $criterio1 = get_string('comportamental_scale_s', 'local_reportesnavarra');
    $criterio2 = get_string('comportamental_scale_f', 'local_reportesnavarra');
    $criterio3 = get_string('comportamental_scale_o', 'local_reportesnavarra');
    $criterio4 = get_string('comportamental_scale_n', 'local_reportesnavarra');
    $tableGrade .= <<<EOD
    <tr>
        <td width="100%" style="border: 1px solid #000000; text-align: center; font-weight: bold; font-size: 7rem;">
                PARÁMETROS DE CONDUCTA
        </td>
    </tr>
    <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">S - SIEMPRE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio1</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">F - FRECUENTEMENTE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio2</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">O - OCASIONALMENTE</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio3</td>
    </tr>
     <tr>
        <td width="30%" style="border: 1px solid #000000; text-align: left; font-weight: bold; font-size: 7rem; padding: 80px;">N - NUNCA</td>
        <td width="70%" style="border: 1px solid #000000; text-align: left;  font-size: 7rem; padding: 80px;">$criterio4</td>
    </tr>
   
    EOD;

    $tableGrade .= <<<EOD
    </table><br><br><br><br><br><br>
    EOD;
    $signature = get_config('local_reportesnavarra', 'signature');
    $tableGrade .= <<<EOD
    <tr>
        <td style="border: none; text-align: center; padding-top: 30px; width: 45%;">
            <!-- Línea de firma 1 -->
            <div style="width: 200px; border-top: 2px solid #000000; margin-left: auto; margin-right: auto;">
                <div style="text-align: center; font-size: 10px; padding-top: 5px;">$signature</div>
            </div>
        </td>
        <td style="border: none; text-align: center; padding-top: 30px; width: 10%;">&nbsp;</td> <!-- Celda para separación -->
        <td style="border: none; text-align: center; padding-top: 30px; width: 45%;">
            <!-- Línea de firma 2 -->
            <div style="width: 200px; border-top: 2px solid #000000; margin-left: auto; margin-right: auto;">
                <div style="text-align: center; font-size: 10px; padding-top: 5px;">$profesionalTitle $teacherName</div>
            </div>
        </td>
    </tr>
EOD;



    $pdf->writeHTML($tableGrade, true, false, false, false, '');



    $tableComplete = "";
    $pdf->writeHTML($tableComplete, true, false, false, false, '');
    //Close and output PDF document
    // $pdf->Output($studentName, 'F');

    $zipFilename = $pdfDir . 'archivos_pdfs.zip';
    $pdfFilename = $pdfDir."temp_pdf_$studentName.pdf";
    $pdf->Output($pdfFilename, 'F'); // Guardar el PDF en el sistema de archivos
    $pdfFiles[] = $pdfFilename; // Guardar el nombre del archivo en el array

    $zip->addFile($pdfFilename, basename($pdfFilename)); // Agregar al ZIP

    // Cerrar el archivo ZIP
    $zip->close();

    // Forzar la descarga del ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . filesize($zipFilename));

    readfile($zipFilename);

    // Eliminar los archivos temporales
    foreach ($pdfFiles as $file) {
        unlink($file);
    }
    unlink($zipFilename);
}
