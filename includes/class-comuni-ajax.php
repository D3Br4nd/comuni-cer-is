<?php
/**
 * AJAX Handler for Comuni Checker
 */

if (!defined('ABSPATH')) {
    exit;
}

class CER_Comuni_Ajax {
    
    public function __construct() {
        // Search comune
        add_action('wp_ajax_cer_search_comune', [$this, 'search_comune']);
        add_action('wp_ajax_nopriv_cer_search_comune', [$this, 'search_comune']);
        
        // Autocomplete
        add_action('wp_ajax_cer_autocomplete_comune', [$this, 'autocomplete_comune']);
        add_action('wp_ajax_nopriv_cer_autocomplete_comune', [$this, 'autocomplete_comune']);
        
        // Report comune
        add_action('wp_ajax_cer_report_comune', [$this, 'report_comune']);
        add_action('wp_ajax_nopriv_cer_report_comune', [$this, 'report_comune']);
    }
    
    /**
     * Autocomplete comuni
     */
    public function autocomplete_comune() {
        check_ajax_referer('cer_comuni_nonce', 'nonce');
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (strlen($query) < 2) {
            wp_send_json_success(['suggestions' => []]);
        }
        
        // Get NocoDB API
        $api = \NocoDB_Connector\NocoDB_Connector::get_api();
        
        // Search comuni (case insensitive with LIKE)
        // Using where filter: (nome-comune,like,%query%)
        $where = sprintf("(nome-comune,like,'%%%s%%')", $query);
        
        $result = $api->get_records(CER_COMUNI_TABLE_ID, [
            'limit' => 10,
            'where' => $where,
            'fields' => 'nome-comune,codice-istat'
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => 'Errore API NocoDB: ' . $result->get_error_message()
            ]);
        }
        
        $suggestions = [];
        if (isset($result['list'])) {
            foreach ($result['list'] as $comune) {
                $suggestions[] = [
                    'nome' => $comune['nome-comune'],
                    'codice' => isset($comune['codice-istat']) ? $comune['codice-istat'] : ''
                ];
            }
        }
        
        wp_send_json_success(['suggestions' => $suggestions]);
    }
    
    /**
     * Check if comune is covered
     */
    public function search_comune() {
        check_ajax_referer('cer_comuni_nonce', 'nonce');
        
        $comune_nome = isset($_POST['comune']) ? sanitize_text_field($_POST['comune']) : '';
        
        if (empty($comune_nome)) {
            wp_send_json_error(['message' => 'Nome comune mancante']);
        }
        
        // Get NocoDB API
        $api = \NocoDB_Connector\NocoDB_Connector::get_api();
        
        // Case-insensitive search using LIKE operator
        $where = sprintf("(nome-comune,like,'%s')", $comune_nome);
        
        $result = $api->get_records(CER_COMUNI_TABLE_ID, [
            'limit' => 1,
            'where' => $where
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => 'Errore API NocoDB: ' . $result->get_error_message()
            ]);
        }
        
        // Check if found
        if (isset($result['list']) && count($result['list']) > 0) {
            $comune_data = $result['list'][0];
            
            // Get fields with proper defaults
            $comune_status = isset($comune_data['status']) ? trim(strtolower($comune_data['status'])) : '';
            $comune_provincia = isset($comune_data['provincia']) ? strtoupper(trim($comune_data['provincia'])) : '';
            $comune_regione = isset($comune_data['regione']) ? trim($comune_data['regione']) : '';

            // DEBUG: Log what we received (you can check WordPress debug.log)
            error_log('DEBUG Comune: ' . $comune_nome);
            error_log('DEBUG Status: "' . $comune_status . '"');
            error_log('DEBUG Provincia: "' . $comune_provincia . '"');
            error_log('DEBUG Regione: "' . $comune_regione . '"');
            error_log('DEBUG Full Data: ' . print_r($comune_data, true));

            $message = '';
            $covered = true;

            switch ($comune_status) {
                case 'aperto':
                    $message = sprintf(
                        'Ottimo! Per <strong>%s</strong> sono aperte le iscrizioni alla Comunità Energetica.',
                        esc_html($comune_nome)
                    );
                    break;
                    
                case 'raccolta':
                    if (in_array($comune_provincia, ['AV', 'BN'])) {
                        $message = sprintf(
                            'Per <strong>%s</strong> stiamo raccogliendo iscrizioni di produttori e consumatori per completare la configurazione di autoconsumo per quella cabina.',
                            esc_html($comune_nome)
                        );
                    } else {
                        $message = sprintf(
                            'Per <strong>%s</strong> si raccolgono segnalazioni per la Comunità Energetica.',
                            esc_html($comune_nome)
                        );
                    }
                    break;
                    
                case 'segnalazione':
                    if ($comune_regione === 'Campania') {
                        $message = sprintf(
                            'Per <strong>%s</strong> si raccolgono segnalazioni per la Comunità Energetica.',
                            esc_html($comune_nome)
                        );
                    } else {
                        $message = sprintf(
                            'Al momento non operiamo nella zona di <strong>%s</strong>. Puoi contattarci per valutarne l\'eventuale possibilità.',
                            esc_html($comune_nome)
                        );
                        $covered = false;
                    }
                    break;
                    
                case 'non_operiamo':
                    $message = sprintf(
                        'Al momento non operiamo nella zona di <strong>%s</strong>. Puoi contattarci per valutarne l\'eventuale possibilità.',
                        esc_html($comune_nome)
                    );
                    $covered = false;
                    break;
                    
                default:
                    // Se lo status non è riconosciuto, log it
                    error_log('DEBUG: Status non riconosciuto: "' . $comune_status . '"');
                    $message = sprintf(
                        'Al momento non operiamo nella zona di <strong>%s</strong>. Puoi contattarci per valutarne l\'eventuale possibilità.',
                        esc_html($comune_nome)
                    );
                    $covered = false;
                    break;
            }

            wp_send_json_success([
                'covered' => $covered,
                'comune' => $comune_data,
                'message' => $message,
                'debug' => [ // Temporary debug info
                    'status' => $comune_status,
                    'provincia' => $comune_provincia,
                    'regione' => $comune_regione
                ]
            ]);
        } else {
            // Comune NOT found in list
            wp_send_json_success([
                'covered' => false,
                'message' => sprintf(
                    'Al momento non operiamo nella zona di <strong>%s</strong>. Puoi contattarci per valutarne l\'eventuale possibilità.',
                    esc_html($comune_nome)
                )
            ]);
        }
    }
    
    /**
     * Report a new comune
     */
    public function report_comune() {
        check_ajax_referer('cer_report_comune', 'nonce');
        
        // Validate inputs
        $comune = isset($_POST['comune']) ? sanitize_text_field($_POST['comune']) : '';
        $provincia = isset($_POST['provincia']) ? strtoupper(sanitize_text_field($_POST['provincia'])) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (empty($comune) || empty($provincia) || empty($email)) {
            wp_send_json_error([
                'message' => 'Tutti i campi sono obbligatori.'
            ]);
        }
        
        if (!is_email($email)) {
            wp_send_json_error([
                'message' => 'Email non valida.'
            ]);
        }
        
        // Prepare email
        $to = 'iscrizioni@cer-is.com';
        $subject = sprintf('Segnalazione nuovo comune - %s (%s)', $comune, $provincia);
        
        $message = sprintf(
            "Nuova segnalazione comune non coperto:\n\n" .
            "Comune: %s\n" .
            "Provincia: %s\n" .
            "Email richiedente: %s\n" .
            "Data: %s\n" .
            "IP: %s\n" .
            "User Agent: %s",
            $comune,
            $provincia,
            $email,
            current_time('mysql'),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: CER-IS Website <noreply@cer-is.com>',
            'Reply-To: ' . $email
        ];
        
        // Send email
        $sent = wp_mail($to, $subject, $message, $headers);
        
        if ($sent) {
            wp_send_json_success([
                'message' => 'Grazie per la segnalazione! Ti contatteremo non appena il tuo comune sarà disponibile.'
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Errore nell\'invio della segnalazione. Riprova più tardi.'
            ]);
        }
    }
}
