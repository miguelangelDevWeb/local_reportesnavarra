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
 require_once($CFG->libdir.'/tablelib.php');
function local_reportesnavarra_extend_navigation(global_navigation $root) {
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


function local_reportesnavarra_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

    if (empty($course)) {
        // We want to display these reports under the site context.
        $course = get_fast_modinfo(SITEID)->get_course();
    }
    $systemcontext = context_system::instance();
    $usercontext = context_user::instance($user->id);
    $coursecontext = context_course::instance($course->id);
    if (grade_report_overview::check_access($systemcontext, $coursecontext, $usercontext, $course, $user->id)) {
        $url = new moodle_url('/local/reportesnavarra/index.php', array('userid' => $user->id, 'id' => $course->id));
        $node = new core_user\output\myprofile\node('reports', 'local_reportesnavarra', get_string('pluginname', 'local_reportesnavarra'),
            null, $url);
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


function local_reportesnavarra_get_all_categories() {
    global $DB;
    $sql = 'select id,name from {course_categories} where visible = 1 AND coursecount > 0';
    $categories = $DB->get_records_sql($sql);
    return $categories;
}

function local_reportesnavarra_save_user_category($users, $categories) {
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

function local_reportesnavarra_save_teacher_category($users, $categories) {
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

function local_reportesnavarra_get_users_categories($fields = '*', $conditions = '', $params = []) {
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

function local_reportesnavarra_get_teachers_categories($fields = '*', $conditions = '', $params = []) {
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

function local_reportesnavarra_get_users_attendance($fields = '*', $conditions = '', $params = []) {
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
  
function get_context_table_users_categories($userscategories) {
    global $CFG;
    $tableUsersContext = [];
    foreach ($userscategories as $usercategory) {
        $user = local_reportesnavarra_get_all_users(
            'id,username,firstname,lastname,email',
            $conditions = 'id = :id',
            $params = ['id' => $usercategory->userid]
        );
         
        $user = reset($user);
        $pictureUrl = local_reportesnavarra_get_picture_profile_for_template($user);

        $tableUsersContext[] = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'category_name' => $usercategory->name,
            'status' => 'true',
            'profileImage' => $pictureUrl,
            'link' => $CFG->wwwroot."/local/reportesnavarra/manager_delete.php?categoryid=".$usercategory->coursecategoryid."&type=usercategory",
        ];
    }
    $contextTemplate = [
        'users' => $tableUsersContext
    ];
    return $contextTemplate;
}

function local_reportesnavarra_get_table_categories($categories) {
    $context = context_system::instance();
    $isadmin = is_siteadmin();
   // Construir la tabla.
   $table = new html_table();
   $table->head = [
       'Categoria',
       'Acción'
   ];

   foreach ($categories as $category) {
       $view_url_add_teacher = new moodle_url('/local/reportesnavarra/manager_teachers_categories.php', ['categoryid' => $category->categoryid]);
       $view_url_register = new moodle_url('/local/reportesnavarra/view_register_attendance.php', ['categoryid' => $category->categoryid]);
       $view_url_view_register = new moodle_url('/local/reportesnavarra/view_users_attendance.php', ['categoryid' => $category->categoryid]);
       $link_register = "<i class='fa fa-plus'></i>";
       $link_add_teacher = "<i class='fa fa-user-plus'></i>";
       $link_view_register = "<i class='fa fa-list-ul'></i>";
       $action_add_teacher_link = '';
       $action_register_link = '';
       $action_view_register_link = '';
       if ($isadmin || has_capability('local/reportesnavarra:administration_register_teacher', $context))
           $action_add_teacher_link = html_writer::link($view_url_add_teacher, $link_add_teacher);
    
        if ($isadmin || has_capability('local/reportesnavarra:administration_register_attendance', $context))
            $action_view_register_link = html_writer::link($view_url_register, $link_register);

        if ($isadmin && has_capability('local/reportesnavarra:gestor_register_attendance', $context)) 
           $action_register_link = html_writer::link($view_url_view_register, $link_view_register);

       $table->data[] = [
           format_string($category->name),
           $action_add_teacher_link.' '.$action_view_register_link .' '. $action_register_link
       ];
   } 
   return $table;
}

function local_reportesnavarra_get_courses_enrolled($userid){
    $courses = enrol_get_users_courses($userid, false, 'id, shortname, showgrades');

    $arraycourse = array();
    if ($courses) {
        foreach ($courses as $course) {
           array_push($arraycourse, array('courseid' => $course->id, 'shortname' => $course->shortname, 'fullname' => $course->fullname));
        }
    }

    return $arraycourse;
}



function local_reportesnavarra_get_picture_profile_for_template($userproperties){
    global $PAGE;
    //Buscar datos adicionales del susuario:

    $userpicture = new \user_picture($userproperties);
    $userpicture->size = 1;
    $pictureUrl = $userpicture->get_url($PAGE)->out(false);



    return $pictureUrl;
}







function local_reportesnavarra_get_current_date_certificate(){

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

function local_reportesnavarra_get_course_category_by_period($courseid, $categoryName){
    global $DB;

        $params = [$courseid,$categoryName];

        $sql = "SELECT id, courseid,parent, fullname FROM {grade_categories}"
            . " WHERE courseid = ? AND fullname = ?"
            . " ORDER BY id ASC";


        $rs = $DB->get_records_sql($sql, $params);

        return $rs;

}

function local_reportesnavarra_get_course_category($courseId, $parentCategory, $name){
    global $DB;

    //$params = [$parentCategory, $name];

    $sql = "SELECT id, courseid,parent, fullname FROM {grade_categories}"
        . " WHERE courseid = ? AND parent = ? AND fullname like ?";

    $params = array($courseId,$parentCategory, '%' . $DB->sql_like_escape($name) . '%');

    $rs = reset($DB->get_records_sql($sql, $params));

    $categories = new stdClass();

    $categories->id = $rs->id;
    $categories->name = $rs->fullname;

    return $categories;

}

function local_reportesnavarra_get_course_category_items($courseId, $parentCategory){
    global $DB;

    $sql = "SELECT id, courseid,categoryid, itemname, itemtype, itemmodule, iteminstance FROM {grade_items}"
        . " WHERE courseid = ? AND categoryid = ?";

    $params = array($courseId,$parentCategory);

    $rs = $DB->get_records_sql($sql, $params);


    return $rs;

}



function local_reportesnavarra_get_format_date_certificate($date) {
    date_default_timezone_set('America/Mexico_City');

    // Crear un objeto DateTime a partir del timestamp Unix
    $fecha = new DateTime('@' .$date);

    // Configurar el objeto DateFormatter
    $dateFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Mexico_City', IntlDateFormatter::GREGORIAN);

    // Definir un formato personalizado para el día, el mes y el año
    $dateFormatter->setPattern('d/MMMM/yyyy');

    // Formatear la fecha y aplicar mayúsculas a la primera letra de cada palabra
    $fechaFormateada = ucwords($dateFormatter->format($fecha));

    return $fechaFormateada;
    //return strftime('%e/%b/%Y', $date);
}

function local_reportesnavarra_get_average($activies,$userId){
    global $DB;
    $cont = 0;
    $gradeSum = 0;
    $average = 0;
    foreach ($activies as $activy) {
        $sql = "SELECT id, itemid,userid, finalgrade FROM {grade_grades}"
            . " WHERE itemid = ? AND userid = ?";

        $params = array($activy->id, $userId);

        $rs = $DB->get_record_sql($sql, $params);
        // if ($rs->finalgrade !== null AND $rs->finalgrade > 0) {
        //     $cont++;
        //     $gradeSum = $gradeSum + $rs->finalgrade;
        // }
        if ($rs->finalgrade > 0) {

             $cont++;
            $gradeSum = $gradeSum + $rs->finalgrade;
        } else if ($rs->finalgrade == 0 && $rs->finalgrade !== NULL) {
            $cont++;
            $gradeSum = $gradeSum + $rs->finalgrade;
        }
        // else if ($rs->finalgrade == NULL) {
        //     $cont++;
        //     $gradeSum = $gradeSum ;
        // }

    }
    if ($cont > 0) {
        $average = local_reportesnavarra_round_especial(($gradeSum / $cont));
    } 
    
    return $average;
}

function local_reportesnavarra_get_grade_item($itemId,$userId){
    global $DB;

    $grade = '';

        $sql = "SELECT id, itemid,userid, finalgrade FROM {grade_grades}"
            . " WHERE itemid = ? AND userid = ?";

        $params = array($itemId, $userId);

        $rs = $DB->get_record_sql($sql, $params);
        if ($rs->finalgrade > 0) {

            $grade = $rs->finalgrade;
            $grade = round($grade,2);
        } else if ($rs->finalgrade == 0 && $rs->finalgrade !== NULL) {
            $grade = 0;
        }
        else if ($rs->finalgrade == NULL) {
            $grade = '?';
        }

    return $grade;
}

function local_reportesnavarra_round_especial($numero) {
    return round($numero, 2, PHP_ROUND_HALF_UP);
}