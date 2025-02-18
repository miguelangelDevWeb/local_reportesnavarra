<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot. '/local/reportesnavarra/lib.php');

require_login();
$isadmin = is_siteadmin();

global $DB, $PAGE, $OUTPUT;

// Configurar la página
$PAGE->set_url(new moodle_url('/local/reportesnavarra/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('attendance_list', 'local_reportesnavarra'));
// $PAGE->set_heading(get_string('attendance_list', 'local_reportesnavarra'));

$PAGE->navbar->add(get_string('list_categories', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/view_category.php'));
$PAGE->navbar->add(get_string('attendance_list', 'local_reportesnavarra'), new moodle_url('/local/reportesnavarra/view_users_attendance.php'));

// Parámetros
$categoryid = required_param('categoryid', PARAM_INT); // ID de la categoría.
$filter_date = optional_param('filter_date', null, PARAM_RAW); // Fecha seleccionada para filtrar

// Condiciones de consulta
$fields = "ua.id as attendanceid, ua.datestatus, ua.status, u.id, u.firstname, u.lastname, u.email";
$conditions = "ua.categoryid = ?";
$params = ['categoryid' => $categoryid];

$records;
// Agregar filtro de fecha si está presente
if (!empty($filter_date)) {
    $filter_time = strtotime($filter_date);
    $conditions .= " AND ua.datestatus = ?";
    $params['filter_date'] = $filter_time;
    $records = local_reportesnavarra_get_users_attendance($fields, $conditions, $params);
    
}

// Obtener los registros

echo $OUTPUT->header();
?>

<!-- Filtro por fecha -->
<div style="margin-bottom: 20px;">
    <form method="get" action="view_users_attendance.php">
        <input type="hidden" name="categoryid" value="<?php echo $categoryid; ?>" />
        <label for="filter_date">Filtrar por fecha:</label>
        <input 
            type="date" 
            name="filter_date" 
            id="filter_date" 
            value="<?php echo $filter_date; ?>" 
        />
        <button type="submit">Aplicar filtro</button>
        <!-- <a href="view_users_attendance.php?categoryid=<?php echo $categoryid; ?>" style="margin-left: 10px;">Ver todos</a> -->
    </form>
</div>
<?php
if (empty($records)) {
    echo $OUTPUT->footer();
    die();
}
?>
<!-- Formulario principal -->
<form method="post" action="update_users_attendance.php">
    <table class="generaltable">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Estudiante</th>
                <th>Mail</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciòn</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td align='center'>
                      <img src="<?php echo local_reportesnavarra_get_picture_profile_for_template($record); ?>" class="mr-1 rounded-circle" style="width: 30px; height: 30px;" alt="Foto de Perfil">
                      <input type="hidden" name="categoryid" value="<?php echo $categoryid; ?>" />
                      <input type="hidden" name="student_ids[<?php echo $record->attendanceid; ?>]" value="<?php echo $record->attendanceid; ?>" />
                      <input type="hidden" name="attendanceid[<?php echo $record->attendanceid; ?>]" value="<?php echo $record->attendanceid; ?>" />
                    </td>
                    <td>
                        <?php echo $record->firstname . ' ' . $record->lastname; ?>
                    </td>
                    <td>
                        <?php echo $record->email; ?>
                    </td>
                    <td>
                        <span>
                        <?php 
                         echo ($record->status == get_config('local_reportesnavarra', 'days_attended')) ? get_string('days_attended', 'local_reportesnavarra') : ''; 
                         echo ($record->status == get_config('local_reportesnavarra', 'faults')) ? get_string('faults', 'local_reportesnavarra') : ''; 
                         echo ($record->status == get_config('local_reportesnavarra', 'arrears')) ? get_string('arrears', 'local_reportesnavarra') : ''; 
                         echo ($record->status == get_config('local_reportesnavarra', 'justified_absences')) ? get_string('justified_absences', 'local_reportesnavarra') : ''; 
                         echo ($record->status == get_config('local_reportesnavarra', 'justified_delays')) ? get_string('justified_delays', 'local_reportesnavarra') : ''; 
                         ?>  
                        </span>
                        <!-- <select name="status[<?php echo $record->attendanceid; ?>]" disabled>
                            <option value="DA" <?php echo $record->status == get_config('local_reportesnavarra', 'days_attended') ? 'selected' : ''; ?>><?php echo get_string('days_attended', 'local_reportesnavarra')?></option>
                            <option value="F" <?php echo $record->status == get_config('local_reportesnavarra', 'faults') ? 'selected' : ''; ?>><?php echo get_string('faults', 'local_reportesnavarra')?></option>
                            <option value="A" <?php echo $record->status == get_config('local_reportesnavarra', 'arrears') ? 'selected' : ''; ?>><?php echo get_string('arrears', 'local_reportesnavarra')?></option>
                            <option value="FJ" <?php echo $record->status == get_config('local_reportesnavarra', 'justified_absences')? 'selected' : ''; ?>><?php echo get_string('justified_absences', 'local_reportesnavarra')?></option>
                            <option value="AJ" <?php echo $record->status == get_config('local_reportesnavarra', 'justified_delays') ? 'selected' : ''; ?>><?php echo get_string('justified_delays', 'local_reportesnavarra')?></option>
                        </select> -->
                    </td>
                    <td>
                        <span></span><?php echo date('d/m/Y', $record->datestatus); ?></span>
                        <!-- <input type="date"  disabled name="datestatus[<?php echo $record->attendanceid; ?>]" value="<?php echo date('Y-m-d', $record->datestatus); ?>" /> -->
                    </td>
                    <td>
                        <a href="manager_delete.php?type=userattendance&attendanceid=<?php echo $record->attendanceid; ?>&filter_date=<?php echo $filter_date; ?>&categoryid=<?php echo $categoryid; ?>" class="btn btn-danger">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- <button type="submit">Guardar cambios</button> -->
</form>

<?php
echo $OUTPUT->footer();
