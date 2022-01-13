<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Accessed from a a link in settings.php Attempts to get a token
 * to confirm that the credentials are valid. Mainly for use during
 * configuration and so only accessible by admin users.
 *
 * @package local_tlconnect
 * @author Marcus Green
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Titus Learning
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

use local_tlconnect\api;

global $PAGE, $OUTPUT;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/tlconnect/checkconnection.php');
admin_externalpage_setup('local_tlconnect_checkconnection');

echo $OUTPUT->header();

if (!is_siteadmin()) {
    echo get_string('adminonly', 'local_tlconnect');
    echo $OUTPUT->footer();
    return;
}

$api = api::instance();
$errorstate = $api->has_error();
if ($errorstate) {
    echo ($errorstate);
} else {
    echo (get_string('connectionworks', 'local_tlconnect'));
}
echo $OUTPUT->footer();
