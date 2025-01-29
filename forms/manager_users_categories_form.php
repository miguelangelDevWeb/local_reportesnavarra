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

class local_reportesnavarra_manager_users_categories_form extends moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        global $DB;
        $mform = $this->_form; // Don't forget the underscore!

        $allusers = array();
        $users = local_reportesnavarra_get_all_users();
        foreach ($users as $user) {
            $allusers[$user->id] = $user->firstname .' '.$user->lastname;
        }
        $options = array(
            'multiple' => true,
        );

        $allcategories = array();
        $categories = local_reportesnavarra_get_all_categories();
        foreach ($categories as $category) {
            $allcategories[$category->id] = $category->name ;
        }
  
        $availablefromgroup2 = array();
        $availablefromgroup2[] =&  $mform->createElement('autocomplete', 'users', get_string('searcharea', 'search'), $allusers, $options);

        $mform->addGroup($availablefromgroup2, 'availablefromgroup', get_string('searchusers', 'local_reportesnavarra'), ' ', false);
        $mform->disabledIf('availablefromgroup', 'availablefromenabled');
        $availablefromgroup[] =& $mform->createElement('autocomplete', 'categories', get_string('searcharea', 'search'), $allcategories, $options);
        $mform->addGroup($availablefromgroup, 'availablefromgroup', get_string('searchcategories', 'local_reportesnavarra'), ' ', false);

        $submitlabel = get_string('submit');
        $mform->addElement('submit', 'submitmessage', $submitlabel);

    }


}
