<?php
/*
  IMU Calculator WordPress Plugin
  Copyright 2012 Cristian Porta (email : cristian@crx.it)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class IMUCInit {

    const STATE_OF_ORIGIN = false;

    function __construct($case = false) {
        global $wpdb;
        if (!$case)
            wp_die('Busted! You should not call this class directly', 'Doing it wrong!');

        switch ($case) {
            case 'activate' :
                // init database
                $imucDB = new IMUCDataBase($wpdb);
                $installed = $imucDB->install();
                $arr = array(
                    "wpimuc_options_register_request" => "1"
                );
                update_option('wpimuc_options', $arr);

                if (version_compare(get_option(WPIMUC_APP_KEY_VERSION), WPIMUC_VERSION) < 0) {
                    update_option(WPIMUC_APP_KEY_VERSION, WPIMUC_VERSION);
                }
                break;

            case 'deactivate' :
                // none
                break;
            case 'uninstall' :
                delete_option('wpimuc_options');
                delete_option(WPIMUC_APP_KEY_VERSION);
                $imucDB = new IMUCDataBase($wpdb);
                $installed = $imucDB->uninstall();
                break;
        }
    }

    function on_activate() {
        new IMUCInit('activate');
    }

    function on_deactivate() {
        $case = 'deactivate';
        if (self::STATE_OF_ORIGIN == true) {
            $case = 'uninstall';
        }

        new IMUCInit($case);
    }

    function on_uninstall() {
//        if (__FILE__ != IMUCInit::STATE_OF_ORIGIN)
//            return;

        new IMUCInit('uninstall');
    }

    function activate_plugin() {
        //
    }

    function deactivate_plugin() {
        //
    }

    function uninstall_plugin() {
        //
    }

    /**
     * trigger_error()
     * 
     * @param (string) $error_msg
     * @param (boolean) $fatal_error | catched a fatal error - when we exit, then we can't go further than this point
     * @param unknown_type $error_type
     * @return void
     */
    function error($error_msg, $fatal_error = false, $error_type = E_USER_ERROR) {
        if (isset($_GET['action']) && 'error_scrape' == $_GET['action']) {
            echo "{$error_msg}\n";
            if ($fatal_error)
                exit;
        }
        else {
            trigger_error($error_msg, $error_type);
        }
    }
}
?>
