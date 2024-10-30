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
class IMUCDataBase {
    
    const WPIMUC_DATABASE_VERSION = '1.0';
    
    private $table_name_request = "";
    
    private $table_name_request_keyvalue = "";
    
    private $wpdb = "";
    
        
    function __construct($wpdb) {
        $this->wpdb = $wpdb;
        $this->table_name_request = $this->wpdb->prefix . "wpimuc_request";
        $this->table_name_request_keyvalue = $this->wpdb->prefix . "wpimuc_request_keyvalue";
    }
    
    public function uninstall() {
        $this->wpdb->query("DROP TABLE IF EXISTS $this->table_name_request");
        $this->wpdb->query("DROP TABLE IF EXISTS $this->table_name_request_keyvalue");
        delete_option( WPIMUC_DB_KEY_VERSION );
    }
    
    public function install() {
        $installedDataBaseVersion = get_option( WPIMUC_DB_KEY_VERSION );
        if(version_compare($installedDataBaseVersion, self::WPIMUC_DATABASE_VERSION) < 0) {
            $sql_request = "
            CREATE TABLE IF NOT EXISTS `$this->table_name_request` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `submit_time` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            );";

            $sql_request_keyvalue = "
            CREATE TABLE IF NOT EXISTS `$this->table_name_request_keyvalue` (
              `request_id` int(11) NOT NULL,
              `field_name` varchar(255) NOT NULL DEFAULT '',
              `field_value` varchar(255) NOT NULL DEFAULT '',
              KEY `request_id` (`request_id`)
            );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql_request);
            dbDelta($sql_request_keyvalue);
            
            update_option( WPIMUC_DB_KEY_VERSION, self::WPIMUC_DATABASE_VERSION );
        }
        
        return true;
    }
    
    public function getCountDailyResults($interval) {
        if(trim($interval) == "") {
            $result = $this->wpdb->get_row('SELECT count(*) as n FROM ' . $this->table_name_request . ';', ARRAY_A);
        } else {            
            $result = $this->wpdb->get_row('SELECT count(*) as n FROM ' . $this->table_name_request . ' WHERE submit_time >= DATE_SUB(NOW(), INTERVAL ' . $interval . ');', ARRAY_A);
        }
        return $result['n'];
    }
    
    public function saveRequest($fields) {
        $this->wpdb->insert( $this->table_name_request , array('submit_time' => date('Y-m-d H:i:s')));
        $insertedID = $this->wpdb->insert_id;
        foreach ($fields as $key => $value) {
            $this->wpdb->query( $this->wpdb->prepare( 'INSERT INTO ' . $this->table_name_request_keyvalue . ' (`request_id` ,`field_name` ,`field_value`) VALUES ( %d, %s, %s )', $insertedID, $key, $value ));
        }
    }
}
?>
