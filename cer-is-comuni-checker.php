<?php
/**
 * Plugin Name: CER-IS Comuni Checker
 * Plugin URI: https://www.cer-is.com
 * Description: Form interattivo per verificare copertura comuni e segnalazioni
 * Version: 1.1.7
 * Author: CER-IS
 * Author URI: https://www.cer-is.com
 * Requires Plugins: wp-nocodb-connector
 * License: GPL v2 or later
 * Text Domain: cer-is-comuni
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CER_COMUNI_VERSION', '1.0.7-debug1');
define('CER_COMUNI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CER_COMUNI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Table ID in NocoDB
define('CER_COMUNI_TABLE_ID', 'mvkndkg59oghv00');

// Require NocoDB Connector
add_action('plugins_loaded', function() {
    if (!class_exists('NocoDB_Connector\NocoDB_Connector')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('Il plugin CER-IS Comuni Checker richiede il plugin "NocoDB Connector" attivo.', 'cer-is-comuni');
            echo '</p></div>';
        });
        return;
    }
    
    // Load plugin files
    require_once CER_COMUNI_PLUGIN_DIR . 'includes/class-comuni-checker.php';
    require_once CER_COMUNI_PLUGIN_DIR . 'includes/class-comuni-ajax.php';
    require_once CER_COMUNI_PLUGIN_DIR . 'includes/class-comuni-settings.php';
    
    // Initialize
    new CER_Comuni_Checker();
    new CER_Comuni_Ajax();
    new CER_Comuni_Settings();
});

// Enqueue assets
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'cer-comuni-style',
        CER_COMUNI_PLUGIN_URL . 'assets/style.css',
        [],
        CER_COMUNI_VERSION
    );
    
    wp_enqueue_script(
        'cer-comuni-script',
        CER_COMUNI_PLUGIN_URL . 'assets/script.js',
        ['jquery'],
        CER_COMUNI_VERSION,
        true
    );
    
    wp_localize_script('cer-comuni-script', 'cerComuniData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cer_comuni_nonce'),
        'iscrizioni_url' => 'https://www.cer-is.com/iscrizioni/',
        'strings' => [
            'searching' => __('Ricerca in corso...', 'cer-is-comuni'),
            'error' => __('Si è verificato un errore. Riprova.', 'cer-is-comuni'),
            'sending' => __('Invio in corso...', 'cer-is-comuni'),
            'success' => __('Segnalazione inviata con successo!', 'cer-is-comuni'),
        ]
    ]);
});
