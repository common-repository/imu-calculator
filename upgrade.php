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
function prefix_upgrade_plugin() {
    global $wpdb;
    $v = WPIMUC_APP_KEY_VERSION;
    $update_option = null;
   
    if( version_compare(get_option($v), WPIMUC_VERSION) != 0 ) {
        
        if (version_compare(get_option($v), WPIMUC_VERSION) < 0) {
            
            if(version_compare(get_option($v), '1.1.0') < 0) {
                $imucDB = new IMUCDataBase($wpdb);
                $update_option = $imucDB->install();
            }
            
            if ($update_option)
                update_option($v, WPIMUC_VERSION);
        }
    }
    if ($update_option)
        return $update_option;

    return false;
}

add_action('admin_init', 'prefix_upgrade_plugin');
?>
