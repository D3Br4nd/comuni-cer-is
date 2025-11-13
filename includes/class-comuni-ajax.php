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
        $where = sprintf("(nome-comune,like,%%%s%%)", $query);
        
        $result = $api->get_records(CER_COMUNI_TABLE_ID, [
            'limit' => 10,
            'where' => $where
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
        
        // Search with LIKE operator for flexible matching
        $where = sprintf("(nome-comune,like,%%%s%%)", $comune_nome);
        
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
            // COMUNE TROVATO - Mostra messaggio positivo
            $comune_data = $result['list'][0];
            $nome_comune = $comune_data['nome-comune'];
            
            $message = sprintf(
                'Ottimo! Il tuo comune <strong>%s</strong> è coperto dalla nostra Comunità Energetica.<br>' .
                'Puoi procedere con l\'iscrizione cliccando sul pulsante qui sotto.',
                esc_html($nome_comune)
            );

            wp_send_json_success([
                'covered' => true,
                'comune' => $comune_data,
                'message' => $message
            ]);
        } else {
            // COMUNE NON TROVATO - Mostra form segnalazione
            $message = sprintf(
                'Al momento il tuo comune <strong>%s</strong> non è ancora parte della nostra rete, ma stiamo lavorando per espanderci!<br><br>' .
                'Lasciaci i tuoi dati e ti contatteremo non appena sarà disponibile nella tua zona.',
                esc_html($comune_nome)
            );
            
            wp_send_json_success([
                'covered' => false,
                'message' => $message
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
