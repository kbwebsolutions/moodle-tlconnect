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

defined('MOODLE_INTERNAL') || die;


if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_tlconnect_category', get_string('pluginname', 'local_tlconnect')));
    $settings = new admin_settingpage('local_tlconnect', get_string('settings', 'local_tlconnect'));
    $ADMIN->add('local_tlconnect_category', $settings);

    if ($ADMIN->fulltree) {
        // Default display settings.
        $settings->add(
            new admin_setting_heading(
                'local_tlconnect/connectsettings',
                get_string('connect', 'local_tlconnect'),
                ''
            )
        );

        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/oauthurl',
                get_string('oauthurl', 'local_tlconnect'),
                get_string('oauthurl_desc', 'local_tlconnect'),
                ''
            )
        );
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/apiclientid',
                get_string('apiclientid', 'local_tlconnect'),
                get_string('apiclientid_desc', 'local_tlconnect'),
                ''
            )
        );
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/apiclientsecret',
                get_string('apiclientsecret', 'local_tlconnect'),
                get_string('apiclientsecret_desc', 'local_tlconnect'),
                ''
            )
        );
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/accesstokenurl',
                get_string('accesstokenurl', 'local_tlconnect'),
                get_string('accesstokenurl_desc', 'local_tlconnect'),
                ''
            )
        );
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/endpointurl',
                get_string('endpointurl', 'local_tlconnect'),
                get_string('endpointurl_desc', 'local_tlconnect'),
                ''
            )
        );
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/apiusername',
                get_string('apiusername', 'local_tlconnect'),
                get_string('apiusername_desc', 'local_tlconnect'),
                ''
            )
        );

        // Not sure about the items that follow this.
        $settings->add(
            new admin_setting_configtext(
                'local_tlconnect/apiuserpwd',
                get_string('apiuserpwd', 'local_tlconnect'),
                get_string('apiuserpwd_desc', 'local_tlconnect'),
                ''
            )
        );

    }
    $ADMIN->add('local_tlconnect_category',
    new admin_externalpage(
        'local_tlconnect_checkconnection',
        get_string('checkconnection', 'local_tlconnect'),
        new moodle_url('/local/tlconnect/checkconnection.php'),
            'moodle/site:config'
    )
 );
}
