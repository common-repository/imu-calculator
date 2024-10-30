<?php
/*
  Plugin Name: IMU Calculator
  Plugin URI: http://www.crx.it/imu-calculator/
  Description: IMU Calculator è un plugin wordpress che permette il calcolo IMU, l' imposta municipale propria.
  Version: 1.1.5
  Author: Cristian 'crx' Porta
  Author URI: http://www.crx.it/imu-calculator/
  License: GPL

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
define('WPIMUC_VERSION', '1.1.5');
define('WPIMUC_BASE_URL', plugins_url("", __FILE__));

define('WPIMUC_APP_KEY_VERSION', 'wpimuc_version');
define('WPIMUC_DB_KEY_VERSION', 'wpimuc_db_version');

include_once plugin_dir_path(__FILE__) . 'IMUCInit.php';
include_once plugin_dir_path(__FILE__) . 'IMUCDataBase.php';
include_once plugin_dir_path(__FILE__) . 'IMUCalculator.php';
include_once plugin_dir_path(__FILE__) . 'upgrade.php';

register_activation_hook(__FILE__, array('IMUCInit', 'on_activate'));
register_deactivation_hook(__FILE__, array('IMUCInit', 'on_deactivate'));
register_uninstall_hook(__FILE__, array('IMUCInit', 'on_uninstall'));


if (!defined('WP_CONTENT_URL')) {
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}
if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
}

add_action('admin_menu', 'wpimuc_plugin_menu');

function wpimuc_style() {
    wp_deregister_style('wpimuc-style');
    wp_register_style('wpimuc-style', plugins_url('/includes/stylesheet/imu_calculator.css', __FILE__), array(), WPIMUC_VERSION);
    wp_enqueue_style('wpimuc-style');
}

function wpimuc_scritp() {
    wp_enqueue_script('jquery');
    wp_deregister_script('wpimuc-script');
    wp_register_script('wpimuc-script', plugins_url('/includes/js/imu_calculator.js', __FILE__), array('jquery'), WPIMUC_VERSION);
    wp_enqueue_script('wpimuc-script');
}

add_action('wp_enqueue_scripts', 'wpimuc_style');
add_action('wp_enqueue_scripts', 'wpimuc_scritp');

function wpimuc_plugin_menu() {
    add_options_page('IMU Calculator Options', 'IMU Calculator', 'manage_options', 'wpimuc', 'wpimuc_plugin_options');
}

add_action('admin_init', 'plugin_admin_init');

function plugin_admin_init() {
    register_setting('wpimuc_options', 'wpimuc_options', 'wpimuc_options_validate');
    add_settings_section('wpimuc_main', '<h3>Impostazioni</h3>', 'wpimuc_options_section_text', 'plugin');
    add_settings_field('wpimuc_option_register_request', 'Registrazione Richieste', 'wpimuc_options_register_request', 'plugin', 'wpimuc_main');
}

function wpimuc_options_section_text() {
    echo '<p>Configurazione.</p>';
}

function wpimuc_options_register_request() {
    $options = get_option('wpimuc_options');
    $items = array("1" => "Si", "0" => "No");
    echo "<select id='wpimuc_options_register_request' name='wpimuc_options[wpimuc_options_register_request]'>";
    foreach ($items as $key => $value) {
        $selected = ($options['wpimuc_options_register_request'] == $key) ? 'selected="selected"' : '';
        echo "<option value='$key' $selected>$value</option>";
    }
    echo "</select>";
    echo "Permette di registrare su database le richieste di calcolo IMU";
}

function wpimuc_options_validate($input) {

    $options = get_option('wpimuc_options');

    switch ($input['wpimuc_options_register_request']) {
        case 0:
        case 1:
            break;
        default:
            // do nothing
            break;
    }
    return $input;
}

function wpimuc_plugin_options() {
    global $wpdb;
    echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"></div>';
    echo '<h2>IMU Calculator</h2>';
    ?>
    <p>(en) IMU Calculator is a wordpress plugin for owners of italian real estate that allows to calculate the new Italian Local Council Property Tax.(IMU).</p>
    <p>(it) IMU Calculator &egrave; un plugin wordpress che permette il calcolo IMU, l' imposta municipale propria.</p>

    <br />

    <div>
        <form action="options.php" method="post">
            <?php settings_fields('wpimuc_options'); ?>
            <?php do_settings_sections('plugin'); ?>
            <input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>
    </div>

    <br />
    <h3>Usage</h3>
    <p>(en) Add the following shortcode to any post or page to start using the IMU Calculator.</p>
    <p>(it) Aggiungere il codice seguente nelle pagine o nei post dove si intende utilizzare IMU Calculator.</p>
    <p><code>[imu_calculator]</code></p>

    <h3>Utilizzo del modulo</h3>

    <?php
    $options = get_option('wpimuc_options');
    if ($options['wpimuc_options_register_request'] == "1") {
        $imucDB = new IMUCDataBase($wpdb);
        ?>
        <table width="250" border="1"> 
            <tr>
                <td><strong>Giorno</strong></td>
                <td><strong># Calcoli IMU</strong></td>
            </tr>
            <tr>
                <td>Ultimo giorno</td>
                <td><strong><?php echo $imucDB->getCountDailyResults("1 DAY"); ?></strong></td>
            </tr>
            <tr>
                <td>Ultima settimana</td>
                <td><strong><?php echo $imucDB->getCountDailyResults("1 WEEK"); ?></strong></td>
            </tr>
            <tr>
                <td>Ultimo mese</td>
                <td><strong><?php echo $imucDB->getCountDailyResults("1 MONTH"); ?></strong></td>
            </tr>
            <tr>
                <td>Ultimi 6 mesi</td>
                <td><strong><?php echo $imucDB->getCountDailyResults("6 MONTH"); ?></strong></td>
            </tr>
            <tr>
                <td>Da Sempre</td>
                <td><strong><?php echo $imucDB->getCountDailyResults(""); ?></strong></td>
            </tr>
        </table>
    <?php } else { ?>
        <p>Registrazione statistiche non attiva</p>
    <?php } ?>


    <br />
    <br />
    <h3>Notice</h3>
    <p style="font-size: small; font-style: italic">(en) This is free software, data may not be updated, please always verify the results with the help of a professional or by the use of a formal instrument.The developer is not responsible for any kind of damages caused by the use of IMU Calculator which is available free of charge.The user with the use of the application raises the developer from any liability, expressed or implied, arising out of the application itself.</p>
    <p style="font-size: small; font-style: italic">(it) Questo &egrave; un software libero, i dati per il calcolo potrebbero non essere aggiornati, si invita sempre alla verifica dei calcoli con l'ausilio di un professionista o di uno strumento ufficiale. Lo sviluppatore non &egrave; responsabile di eventuali danni causati dall'uso di IMU Calculator che viene fornito gratuitamente. L'utente con l'utilizzo dell'applicazione solleva lo sviluppatore da ogni responsabilit&agrave;, implicita ed esplicita, derivante dall'uso dell'applicazione stessa.</p>

    <?php
    echo '</div>';
}

function display_imu_calculator($atts, $content = null) {
    $nonce = wp_nonce_field('imu-calculator-nonce', 'imu-calculator-nonce', true, false);
    $uuid = uniqid();
    $display = ' 
         <form id="imuc-calcoloimu"  action="/" method="post" onsubmit="return imucSubmitForm(\'' . site_url() . '\');">
                ' . $nonce . '
                <input type="hidden" name="action" id="form_uuid" value="imu_ajax_call" />
                
                <table class="imu_calculator_form">
                    <tr class="first">
                        <td class="title" for="form_coefficiente" id="label_form_coefficiente">Destinazione d\'uso</td>
                        <td>
                            <select name="coefficiente" id="form_coefficiente">
                            <option value="A" selected="selected">A (escl.A/10) - Abitazioni</option>
                            <option value="A10" >A/10 - Uffici e studi privati</option>
                            <option value="B" >B - Edifici pubblici</option>
                            <option value="C1" >C/1 - Negozi/Botteghe</option>
                            <option value="C2" >C/2 - Magazzini e locali di deposito</option>
                            <option value="C3" >C/3 - Laboratori per arti e mestieri</option>
                            <option value="C4" >C/4 - Fabbricati/locali per esercizi sportivi senza fini di lucro</option>
                            <option value="C5" >C/5 - Stabilimenti balneari senza fini di lucro</option>
                            <option value="C6" >C/6 - Stalle, scuderie e simili, rimesse, Box o posti auto</option>
                            <option value="C7" >C/7 - Tettoie chiuse o aperte</option>
                            <option value="D" >D (escl.D/5) - Alberghi, pensioni e residences, fabbricati ind./comm.</option>
                            <option value="D5" >D/5 - Istituto di credito, cambio e assicurazione (con fine di lucro)</option>
                            <option value="FAC267" >Fabbricati agricoli uso strumentale Cat. A/6,C/2,C/6,C/7</option>
                            <option value="FAD10" >Fabbricati agricoli uso strumentale Cat.D/10</option>
                            <option value="TA" >Terreni agricoli</option>
                            <option value="AE" >Area Edificabile</option>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="title" for="form_rendita" id="label_rendita">Rendita Catastale</td>
                        <td><input type="text" size="10" name="rendita" id="form_rendita" onBlur="this.value=formatCurrency(this.value);" class="rendita" ><span id="trigger_rendita"></span></td>
                    </tr>
                    <tr>
                        <td class="title" for="form_quota_possesso">Quota di Possesso</td>
                        <td><input type="text" size="2" name="quota_possesso" id="form_quota_possesso" class="quota_possesso aliquota" value="100" ></td>
                    </tr>
                    <tr class="form_row detrazione_base_imponibile">
                        <td class="title" id="label_detrazione_base_imponibile">Fabbricato dichiarato</td>
                        <td class="add" for="form_detrazione_bi"><input type="checkbox" name="detrazione_bi" id="form_detrazione_bi" value="1" /><span>Interesse storico o inagibile</span></td>
                    </tr>
                    <tr class="form_row prima_casa">
                        <td class="title" id="label_prima_casa">Abitazione principale</td>
                        <td >
                            <label class="add" for="form_prima_casa_si"><input name="prima_casa" id="form_prima_casa_si" value="1" type="radio" checked="checked" /><span>Si</span></label>
                            <label class="add" for="form_prima_casa_no"><input name="prima_casa" id="form_prima_casa_no" value="0" type="radio" /><span>No</span></label>
                        </td>
                    </tr>
                    <tr class="form_row coltivatore_diretto" style="display:none;">
                        <td class="title" style="line-height:normal;">Coltivatore diretto</td>
                        <td >
                            <label class="add" for="form_coltivatore_diretto_si"><input name="coltivatore_diretto" id="form_coltivatore_diretto_si" value="1" type="radio" checked="checked" /><span>Si</span></label>
                            <label class="add" for="form_coltivatore_diretto_no"><input name="coltivatore_diretto" id="form_coltivatore_diretto_no" value="0" type="radio"  /><span>No</span></label>
                        </td>
                    </tr>
                    <tr class="form_row figli">
                        <td class="title" for="form_n_figli">Figli conviventi</td>
                        <td ><input type="text" size="2"  name="n_figli" id="form_n_figli" class="figli">&nbsp;<img class="imc_tip_figli_conviventi" src="' . WPIMUC_BASE_URL . '/includes/images/icon-info.png" /></td>
                    </tr>
                     <tr class="eta_figli" id="imuc_eta_figli"  style="display:none;">
                        <td class="title" for="form_n_figli">Mesi in detrazione per ogni figlio</td>
                        <td>
                          <span id="imuc_eta_figlio_1" class="imuc_eta_figli" rel="eta_figli"><input type="text" name="eta_figlio[0]" class="input_eta_figlio" id="form_eta_figlio_1" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_2" class="imuc_eta_figli"><input type="text" name="eta_figlio[1]" class="input_eta_figlio" id="form_eta_figlio_2" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_3" class="imuc_eta_figli"><input type="text" name="eta_figlio[2]" class="input_eta_figlio" id="form_eta_figlio_3" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_4" class="imuc_eta_figli"><input type="text" name="eta_figlio[3]" class="input_eta_figlio" id="form_eta_figlio_4" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_5" class="imuc_eta_figli"><input type="text" name="eta_figlio[4]" class="input_eta_figlio" id="form_eta_figlio_5" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_6" class="imuc_eta_figli"><input type="text" name="eta_figlio[5]" class="input_eta_figlio" id="form_eta_figlio_6" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_7" class="imuc_eta_figli"><input type="text" name="eta_figlio[6]" class="input_eta_figlio" id="form_eta_figlio_7" size="2" value="0" /></span>
                          <span id="imuc_eta_figlio_8" class="imuc_eta_figli"><input type="text" name="eta_figlio[7]" class="input_eta_figlio" id="form_eta_figlio_8" size="2" value="0" /></span>
                        </td>
                    </tr>
                    <tr >
                        <td class="title" for="form_aliquota"  title="Verificare l\'aliquota applicata dal proprio comune">Aliquota</td>
                        <td ><input type="text" size="5" name="aliquota" id="form_aliquota" class="aliquota" value="0.40">&nbsp;<img class="imc_tip_aliquota" src="' . WPIMUC_BASE_URL . '/includes/images/icon-info.png" /></td>
                    </tr>
                    <tr class=" contitolari_row" style="display:none;" title="Possessori che utilizzano immobile come abitazione principale">
                        <td class="title" for="form_contitolari">Contitolari Conviventi</td>
                        <td > <input type="text" size="2" name="contitolari" id="form_contitolari" class="piccolo contitolari" ></td>
                    </tr>
                    <tr  class=" last">
                        <td  class="title" for="form_mesi_possesso">Mesi di Possesso</td>
                        <td ><input type="text" size="2" name="mesi_possesso" id="form_mesi_possesso" class="piccolo mesi_possesso" value="12"></td>
                    </tr>
                    <tr  class=" last">
                        <td>&nbsp;</td>
                        <td ><input type="submit" name="submit" value="Calcola" /><span class="result" id="totale">RISULTATO</span>&nbsp;<img id="spinner" src="' . WPIMUC_BASE_URL . '/includes/images/loader.png" width="16" height="16" /></td>
                    </tr>
                </table>
            </form>
            
        <script type="text/javascript">
        <!--
            jQuery().ready(function(){
                jQuery("img.imc_tip_default").tipbox("Test");
                jQuery("img.imc_tip_aliquota").tipbox("Verificare l\'aliquota applicata dal proprio comune");
                jQuery("img.imc_tip_figli_conviventi").tipbox("Figli conviventi di eta\' fino a 26 anni");
            });
         // -->
        </script>
    ';
    return $display;
}

function imu_ajax_call() {
    global $wpdb;
    if(!wp_verify_nonce($_REQUEST['imu-calculator-nonce'],'imu-calculator-nonce')) {
        trigger_error("__NO_VALID_NONCE__");
        exit();
    }
    $imu = new IMUCalulator();
    $imu->setCoefficiente($_REQUEST['coefficiente']);
    $imu->setRendita(str_replace(",", ".", str_replace(".", "", $_REQUEST['rendita'])));
    if (isset($_REQUEST['detrazione_bi']) && $_REQUEST['detrazione_bi'] == 1) {
        $imu->setDetrazione_bi(true);
    }
    if (isset($_REQUEST['prima_casa']) && $_REQUEST['prima_casa'] == 1) {
        $imu->setPrima_casa(true);
    }
    if (isset($_REQUEST['coltivatore_diretto']) && $_REQUEST['coltivatore_diretto'] == 1) {
        $imu->setColtivatore_diretto(true);
    }
    $imu->setN_figli($_REQUEST['n_figli']);
    $imu->setEtaFigli($_REQUEST['eta_figlio']);
    $imu->setAliquota($_REQUEST['aliquota']);
    $imu->setQuota_possesso($_REQUEST['quota_possesso']);
    $imu->setContitolari($_REQUEST['contitolari']);
    $imu->setMesi_possesso($_REQUEST['mesi_possesso']);
    $totaleIMU = $imu->getIMU();
    foreach ($_REQUEST as $key => $value) {
        if ($imu->existsField($key)) {
            $imu->setField($key, $value);
        }
    }
    $imu->setField('totale', $totaleIMU);
    $options = get_option('wpimuc_options');
    if ($options['wpimuc_options_register_request'] == "1") {
        $imuDB = new IMUCDataBase($wpdb);
        $imuDB->saveRequest($imu->getFields());
    }
    echo $totaleIMU;
    die();
}


add_action('wp_ajax_nopriv_imu_ajax_call', 'imu_ajax_call');
add_action('wp_ajax_imu_ajax_call', 'imu_ajax_call');

add_shortcode('imu_calculator', 'display_imu_calculator');
?>