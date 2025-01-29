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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_reportesnavarra', new lang_string('pluginname', 'local_reportesnavarra'));

    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.

        // Configuraciones de los rangos de calificación
        $settings->add(new admin_setting_heading(
            'local_reportesnavarra/general_heading',
            'Configuraciones generales',
            'Configuración general.'
        ));

        // Add a setting field to the settings for this page
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/title',

            // This is the friendly title for the config, which will be displayed
            'Unidad Educativa:',

            // This is helper text for this config field
            'Este es el título del certificado',

            // This is the default value
            'UNIDAD EDUCATIVA PARTICULAR "NAVARRA"',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/address',

            // This is the friendly title for the config, which will be displayed
            'Dirección:',

            // This is helper text for this config field
            'Dirección de la unidad educativa',

            // This is the default value
            'CIUDADELA LA VENECIA CALLE GRACIELA ESCUDERO N°13 Y CALLE S58-B',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/phone',

            // This is the friendly title for the config, which will be displayed
            'Teléfono:',

            // This is helper text for this config field
            'Telefonos de la unidad educativa',

            // This is the default value
            '(02) 3070173 / CEL: 0993063052',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/email',

            // This is the friendly title for the config, which will be displayed
            'unednavarra@hotmail.com / 17h03000@gmail.com',

            // This is helper text for this config field
            'Correo de la unidad educativa:',

            // This is the default value
            'unednavarra@hotmail.com / 17h03000@gmail.com',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/yearSchool',

            // This is the friendly title for the config, which will be displayed
            'Año lectivo:',

            // This is helper text for this config field
            'Año escolar',

            // This is the default value
            '2024 - 2025',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/signature',

            // This is the friendly title for the config, which will be displayed
            'Firma:',

            // This is helper text for this config field
            'Firma del director',

            // This is the default value
            'MSc. JUAN CARLOS SANGUANO',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_heading(
            'local_reportesnavarra/general_date_range_heading',
            'Configuración de rangos de fechas',
            'Configuración dinámica de rangos de fechas.'
        ));

        // Add a setting field to the settings for this page
        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/1erT',

            // This is the friendly title for the config, which will be displayed
            '1er Trimestre:',

            // This is helper text for this config field
            'Rango de fechas del primer 1er Trimestre',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/1erT1P',

            // This is the friendly title for the config, which will be displayed
            '1er Trimestre - 1er Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 1er Trimestre - 1er Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/1erT2P',

            // This is the friendly title for the config, which will be displayed
            '1er Trimestre - 2do Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 1er Trimestre - 2do Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/2doT',

            // This is the friendly title for the config, which will be displayed
            '2do Trimestre:',

            // This is helper text for this config field
            'Rango de fechas del 2do Trimestre',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/2doT3P',

            // This is the friendly title for the config, which will be displayed
            '2do Trimestre - 3er Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 2do Trimestre - 3er Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/2doT4P',

            // This is the friendly title for the config, which will be displayed
            '2do Trimestre - 4to Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 2do Trimestre - 4to Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/3erT',

            // This is the friendly title for the config, which will be displayed
            '3er Trimestre:',

            // This is helper text for this config field
            'Rango de fechas del 3er Trimestre',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/3erT5P',

            // This is the friendly title for the config, which will be displayed
            '3er Trimestre - 5to Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 3er Trimestre - 5to Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtext(
            // This is the reference you will use to your configuration
            'local_reportesnavarra/3erT6P',

            // This is the friendly title for the config, which will be displayed
            '3er Trimestre - 6to Parcial:',

            // This is helper text for this config field
            'Rango de fechas del 3er Trimestre - 6to Parcial',

            // This is the default value
            '-',

            // This is the type of Parameter this config is
            PARAM_TEXT
        ));

        // Configuraciones de asisntencia
        $settings->add(new admin_setting_heading(
            'local_reportesnavarra/attendance_heading',
            'Faltas y atrasos',
            'Configuración de los valores para las faltas y atrasos.'
        ));    
        // A+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/days_attended',
            'Dias asistidos:',
            'Valor para los días asistidos',
            'DA',
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/faults',
            'Faltas:',
            'Valor para las faltas',
            'F',
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/arrears',
            'Atrasos:',
            'Valor para los atrasos',
            'A',
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/justified_absences',
            'Falta justificadas:',
            'Valor para faltas justificadas',
            'FJ',
            PARAM_TEXT
        ));
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/justified_delays',
            'Atrasos justificados:',
            'Valor para atrasos justificados',
            'AJ',
            PARAM_TEXT
        ));
        // Configuraciones de los rangos de calificación
        $settings->add(new admin_setting_heading(
            'local_reportesnavarra/grade_scale_heading',
            'Escala de Calificaciones',
            'Configura los rangos dinámicamente.'
        ));

        // A+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_aplus',
            'A+',
            'Rango para la calificación A+',
            '9.01-10',
            PARAM_TEXT
        ));

        // A-
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_aminus',
            'A-',
            'Rango para la calificación A-',
            '8.01-9',
            PARAM_TEXT
        ));

        // B+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_bplus',
            'B+',
            'Rango para la calificación B+',
            '7.01-8',
            PARAM_TEXT
        ));

        // B
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_b',
            'B-',
            'Rango para la calificación B',
            '6.01-7',
            PARAM_TEXT
        ));

        // C+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_cplus',
            'C+',
            'Rango para la calificación C+',
            '5.01-6',
            PARAM_TEXT
        ));

        // C-
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_cminus',
            'C-',
            'Rango para la calificación C-',
            '4.01-5',
            PARAM_TEXT
        ));

        // D+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_dplus',
            'D+',
            'Rango para la calificación D+',
            '3.01-4',
            PARAM_TEXT
        ));

        // D-
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_dminus',
            'D-',
            'Rango para la calificación D-',
            '2.01-3',
            PARAM_TEXT
        ));

        // E+
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_eplus',
            'E+',
            'Rango para la calificación E+',
            '1.01-2',
            PARAM_TEXT
        ));

        // E-
        $settings->add(new admin_setting_configtext(
            'local_reportesnavarra/grade_eminus',
            'E-',
            'Rango para la calificación E-',
            '0.01-1',
            PARAM_TEXT
        ));
    }
}
