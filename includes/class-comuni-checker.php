<?php
/**
 * Main Comuni Checker Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class CER_Comuni_Checker {
    
    public function __construct() {
        // Register shortcode
        add_shortcode('cer_check_comune', [$this, 'render_shortcode']);
    }
    
    /**
     * Render the shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'title' => 'Verifica se il tuo Comune è coperto dalla Comunità Energetica',
        ], $atts);
        
        ob_start();
        ?>
        <div class="cer-comuni-wrapper">
            <!-- Search Section -->
            <div class="cer-comuni-search-section">
                <h2 class="cer-comuni-title"><?php echo esc_html($atts['title']); ?></h2>
                
                <div class="cer-comuni-search-box">
                    <input 
                        type="text" 
                        id="cer-comune-input" 
                        class="cer-comune-input" 
                        placeholder="Inizia a digitare il nome del tuo comune..."
                        autocomplete="off"
                    />
                    <button type="button" id="cer-comune-search-btn" class="cer-comune-btn cer-comune-btn-primary">
                        <svg class="cer-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        Verifica
                    </button>
                </div>
                
                <!-- Autocomplete suggestions -->
                <div id="cer-comune-suggestions" class="cer-comune-suggestions"></div>
                
                <!-- Loading -->
                <div id="cer-comune-loading" class="cer-comune-loading" style="display: none;">
                    <div class="cer-spinner"></div>
                    <span>Ricerca in corso...</span>
                </div>
                
                <!-- Result -->
                <div id="cer-comune-result" class="cer-comune-result" style="display: none;"></div>
            </div>
            
            <!-- Report Section (shown when comune not covered) -->
            <div id="cer-comuni-report-section" class="cer-comuni-report-section" style="display: none;">
                <div class="cer-report-card">
                    <h3 class="cer-report-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Il tuo comune non è ancora coperto
                    </h3>
                    <p class="cer-report-description">
                        Aiutaci a portare la Comunità Energetica nel tuo territorio! 
                        Segnalaci il tuo comune e ti contatteremo non appena sarà disponibile.
                    </p>
                    
                    <form id="cer-report-form" class="cer-report-form">
                        <?php wp_nonce_field('cer_report_comune', 'nonce'); ?>
                        
                        <div class="cer-form-group">
                            <label for="report-comune">Nome Comune *</label>
                            <input 
                                type="text" 
                                id="report-comune" 
                                name="comune" 
                                class="cer-form-input" 
                                required
                                placeholder="Es. Roma"
                            />
                        </div>
                        
                        <div class="cer-form-group">
                            <label for="report-provincia">Provincia (Sigla) *</label>
                            <input 
                                type="text" 
                                id="report-provincia" 
                                name="provincia" 
                                class="cer-form-input" 
                                required
                                maxlength="2"
                                placeholder="Es. RM"
                                style="text-transform: uppercase;"
                            />
                        </div>
                        
                        <div class="cer-form-group">
                            <label for="report-email">La tua Email *</label>
                            <input 
                                type="email" 
                                id="report-email" 
                                name="email" 
                                class="cer-form-input" 
                                required
                                placeholder="nome@esempio.it"
                            />
                        </div>
                        
                        <div class="cer-form-actions">
                            <button type="submit" class="cer-comune-btn cer-comune-btn-primary">
                                Invia Segnalazione
                            </button>
                            <button type="button" id="cer-report-cancel" class="cer-comune-btn cer-comune-btn-secondary">
                                Annulla
                            </button>
                        </div>
                        
                        <div id="cer-report-result" class="cer-report-result" style="display: none;"></div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
