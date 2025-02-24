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
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot. '/local/reportesnavarra/lib.php');

class local_reportesnavarra_view_admin_certificate_form extends moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        global $USER;
        $mform = $this->_form; 

        $dataOptions = local_reportesnavarra_get_grade_categories_config();

        $options = array(
            'multiple' => false,
        );
        $optionsCategory = array(
            'multiple' => false,
        );
        $isadmin = is_siteadmin();

        $allcategories = array('' => 'Seleccione una categoría');
        $categories = [];
        if ($isadmin) {
            $categories = local_reportesnavarra_get_all_categories();
        } else {
            $fields = 'cc.id,cc.name ';
            $conditions = 'uc.userid = ?';
            $params = [$USER->id];
            $categories = local_reportesnavarra_get_users_categories($fields,$conditions,$params);
        }
        foreach ($categories as $category) {
            $allcategories[$category->id] = $category->name ;
        }

        $availablefromgroup2 = array();
        $availablefromgroup2[] =&  $mform->createElement('select', 'period', '', $dataOptions, $options);

        $mform->addGroup($availablefromgroup2, 'availablefromgroup', get_string('selected_period', 'local_reportesnavarra'), ' ', false);
        $availablefromgroup[] =& $mform->createElement('autocomplete', 'category', get_string('searcharea', 'search'), $allcategories, $optionsCategory);
        $mform->addGroup($availablefromgroup, 'availablefromgroup', get_string('searchcategories', 'local_reportesnavarra'), ' ', false);
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitmessage', get_string('submit'));
        $buttonarray[] =& $mform->createElement('button', 'newbutton', 'Nuevo', array('onclick' => 'window.location.reload();'));
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        
    }


}
