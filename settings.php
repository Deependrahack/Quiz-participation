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
 * @package     block_quiz_participation
 * @category    admin
 * @copyright   2022 Deependra Kumar Singh <deepcs20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        // TODO: Define the plugin settings page - {@link https://docs.moodle.org/dev/Admin_settings}.
//         $settings->add(new admin_setting_heading('blockquiz_participation', get_string('pluginname', 'block_quiz_participation'), ''));
//
//        $daystr = get_string('days');
//        $fromwhenoptions = array('7' => '7 ' . $daystr,
//            '10' => '10 ' . $daystr,            
//            '15' => '15 ' . $daystr,
//            '30' => '30 ' . $daystr,
//            '60' => '60 ' . $daystr,
//            '90' => '90 ' . $daystr,
//        );
//
//        $key = 'block_quiz_participation/activelearner';
//        $label = get_string('configfromwhen', 'block_quiz_participation');
//        $desc = get_string('configfromwhen_desc', 'block_quiz_participation');
//        $settings->add(new admin_setting_configselect($key, $label, $desc, 7, $fromwhenoptions));
    }
}
