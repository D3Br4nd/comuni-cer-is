<?php
/**
 * Admin Settings Page for Comuni Checker
 */

if (!defined('ABSPATH')) {
    exit;
}

class CER_Comuni_Settings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Add settings page to WordPress menu
     */
    public function add_settings_page() {
        add_options_page(
            __('CER-IS Comuni Checker', 'cer-is-comuni'),
            __('Comuni Checker', 'cer-is-comuni'),
            'manage_options',
            'cer-comuni-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_assets($hook) {
        if ('settings_page_cer-comuni-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'cer-comuni-admin',
            CER_COMUNI_PLUGIN_URL . 'assets/admin-style.css',
            [],
            CER_COMUNI_VERSION
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check NocoDB Connector status
        $nocodb_active = class_exists('NocoDB_Connector\NocoDB_Connector');
        $nocodb_configured = false;
        
        if ($nocodb_active) {
            $api = \NocoDB_Connector\NocoDB_Connector::get_api();
            $nocodb_configured = $api->is_configured();
        }
        ?>
        <div class="wrap cer-comuni-admin-wrap">
            <h1>
                <span class="cer-admin-icon">🌱</span>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            
            <div class="cer-admin-grid">
                
                <!-- Status Card -->
                <div class="cer-admin-card cer-status-card">
                    <h2>📊 Stato Sistema</h2>
                    
                    <div class="cer-status-item">
                        <span class="cer-status-label">Plugin Comuni Checker:</span>
                        <span class="cer-status-badge cer-status-active">✓ Attivo</span>
                    </div>
                    
                    <div class="cer-status-item">
                        <span class="cer-status-label">NocoDB Connector:</span>
                        <?php if ($nocodb_active): ?>
                            <span class="cer-status-badge cer-status-active">✓ Installato</span>
                        <?php else: ?>
                            <span class="cer-status-badge cer-status-error">✗ Non trovato</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cer-status-item">
                        <span class="cer-status-label">Connessione NocoDB:</span>
                        <?php if ($nocodb_configured): ?>
                            <span class="cer-status-badge cer-status-active">✓ Configurato</span>
                        <?php else: ?>
                            <span class="cer-status-badge cer-status-warning">⚠ Da configurare</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cer-status-item">
                        <span class="cer-status-label">Table ID NocoDB:</span>
                        <code><?php echo esc_html(CER_COMUNI_TABLE_ID); ?></code>
                    </div>
                    
                    <?php if (!$nocodb_configured): ?>
                        <div class="cer-admin-notice cer-notice-warning">
                            <strong>⚠️ Attenzione:</strong> Configura il plugin NocoDB Connector per utilizzare il form comuni.
                            <br><br>
                            <a href="<?php echo admin_url('options-general.php?page=nocodb-connector'); ?>" class="button button-primary">
                                Configura NocoDB
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Shortcode Card -->
                <div class="cer-admin-card">
                    <h2>📝 Shortcode</h2>
                    <p>Inserisci questo shortcode in qualsiasi pagina o post WordPress:</p>
                    
                    <div class="cer-shortcode-box">
                        <code>[cer_check_comune]</code>
                        <button class="button button-secondary cer-copy-btn" data-copy="[cer_check_comune]">
                            📋 Copia
                        </button>
                    </div>
                    
                    <h3>Con titolo personalizzato:</h3>
                    <div class="cer-shortcode-box">
                        <code>[cer_check_comune title="Il tuo titolo qui"]</code>
                        <button class="button button-secondary cer-copy-btn" data-copy='[cer_check_comune title="Il tuo titolo qui"]'>
                            📋 Copia
                        </button>
                    </div>
                    
                    <div class="cer-admin-notice cer-notice-info">
                        <strong>💡 Suggerimento:</strong> Crea una nuova pagina chiamata "Verifica Comune" e inserisci lo shortcode per avere una pagina dedicata.
                    </div>
                </div>
                
                <!-- Configuration Card -->
                <div class="cer-admin-card">
                    <h2>⚙️ Configurazione</h2>
                    
                    <div class="cer-config-item">
                        <h3>📧 Email Destinatario</h3>
                        <p>Le segnalazioni vengono inviate a:</p>
                        <div class="cer-config-value">
                            <code>iscrizioni@cer-is.com</code>
                        </div>
                        <p class="description">
                            Per modificare, edita il file: 
                            <code>includes/class-comuni-ajax.php</code> (riga ~103)
                        </p>
                    </div>
                    
                    <div class="cer-config-item">
                        <h3>🔗 URL Iscrizioni</h3>
                        <p>Il pulsante "Iscriviti Ora" rimanda a:</p>
                        <div class="cer-config-value">
                            <code>https://www.cer-is.com/iscrizioni/</code>
                        </div>
                        <p class="description">
                            Per modificare, edita il file principale del plugin (riga ~41)
                        </p>
                    </div>
                    
                    <div class="cer-config-item">
                        <h3>🗄️ Database NocoDB</h3>
                        <p>Tabella comuni configurata:</p>
                        <div class="cer-config-value">
                            <strong>Base:</strong> CER-IS (pao03kzkiw95cao)<br>
                            <strong>Table:</strong> lista-comuni-cer-is<br>
                            <strong>Table ID:</strong> <code><?php echo esc_html(CER_COMUNI_TABLE_ID); ?></code>
                        </div>
                    </div>
                </div>
                
                <!-- Usage Instructions Card -->
                <div class="cer-admin-card cer-card-full">
                    <h2>📖 Guida Rapida</h2>
                    
                    <div class="cer-steps">
                        <div class="cer-step">
                            <div class="cer-step-number">1</div>
                            <div class="cer-step-content">
                                <h3>Configura NocoDB Connector</h3>
                                <p>Vai su <a href="<?php echo admin_url('options-general.php?page=nocodb-connector'); ?>">Settings → NocoDB Connector</a> e inserisci:</p>
                                <ul>
                                    <li><strong>URL:</strong> https://data.cer-is.com</li>
                                    <li><strong>Token:</strong> Il tuo API token</li>
                                </ul>
                                <p>Clicca "Testa Connessione" per verificare.</p>
                            </div>
                        </div>
                        
                        <div class="cer-step">
                            <div class="cer-step-number">2</div>
                            <div class="cer-step-content">
                                <h3>Crea una Pagina</h3>
                                <p>Vai su <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">Pagine → Aggiungi nuova</a></p>
                                <ul>
                                    <li>Titolo: "Verifica Copertura Comune"</li>
                                    <li>Inserisci nel contenuto: <code>[cer_check_comune]</code></li>
                                    <li>Pubblica la pagina</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="cer-step">
                            <div class="cer-step-number">3</div>
                            <div class="cer-step-content">
                                <h3>Verifica Funzionamento</h3>
                                <p>Apri la pagina creata e testa:</p>
                                <ul>
                                    <li>✓ Autocomplete durante la digitazione</li>
                                    <li>✓ Ricerca comune esistente → Messaggio successo</li>
                                    <li>✓ Ricerca comune NON esistente → Form segnalazione</li>
                                    <li>✓ Invio segnalazione → Email ricevuta</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="cer-step">
                            <div class="cer-step-number">4</div>
                            <div class="cer-step-content">
                                <h3>Configura SMTP (opzionale)</h3>
                                <p>Per garantire l'invio delle email, installa un plugin SMTP:</p>
                                <ul>
                                    <li><a href="<?php echo admin_url('plugin-install.php?s=WP+Mail+SMTP&tab=search&type=term'); ?>" target="_blank">WP Mail SMTP</a></li>
                                    <li><a href="<?php echo admin_url('plugin-install.php?s=Easy+WP+SMTP&tab=search&type=term'); ?>" target="_blank">Easy WP SMTP</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Troubleshooting Card -->
                <div class="cer-admin-card cer-card-full">
                    <h2>🔧 Risoluzione Problemi</h2>
                    
                    <div class="cer-troubleshooting">
                        <details>
                            <summary><strong>❌ Autocomplete non funziona</strong></summary>
                            <div class="cer-trouble-content">
                                <p><strong>Possibili cause:</strong></p>
                                <ul>
                                    <li>NocoDB Connector non configurato</li>
                                    <li>Table ID errato</li>
                                    <li>Problemi di connessione al server NocoDB</li>
                                </ul>
                                <p><strong>Soluzione:</strong></p>
                                <ol>
                                    <li>Verifica Settings → NocoDB Connector → Test Connessione</li>
                                    <li>Apri console browser (F12) e cerca errori JavaScript</li>
                                    <li>Verifica che la tabella <code><?php echo esc_html(CER_COMUNI_TABLE_ID); ?></code> esista su NocoDB</li>
                                </ol>
                            </div>
                        </details>
                        
                        <details>
                            <summary><strong>📧 Email non arrivano</strong></summary>
                            <div class="cer-trouble-content">
                                <p><strong>Possibili cause:</strong></p>
                                <ul>
                                    <li>Plugin SMTP non configurato</li>
                                    <li>Server email bloccato</li>
                                    <li>Email finisce nello spam</li>
                                </ul>
                                <p><strong>Soluzione:</strong></p>
                                <ol>
                                    <li>Installa e configura un plugin SMTP</li>
                                    <li>Testa l'invio email dal plugin SMTP</li>
                                    <li>Controlla la cartella spam di iscrizioni@cer-is.com</li>
                                </ol>
                            </div>
                        </details>
                        
                        <details>
                            <summary><strong>🎨 Stile non applicato</strong></summary>
                            <div class="cer-trouble-content">
                                <p><strong>Possibili cause:</strong></p>
                                <ul>
                                    <li>Cache browser attiva</li>
                                    <li>Cache WordPress attiva</li>
                                    <li>Conflitti CSS con il tema</li>
                                </ul>
                                <p><strong>Soluzione:</strong></p>
                                <ol>
                                    <li>Pulisci cache browser (Ctrl+F5)</li>
                                    <li>Pulisci cache WordPress/plugin cache</li>
                                    <li>Verifica che il file CSS sia caricato (Inspector → Network)</li>
                                </ol>
                            </div>
                        </details>
                        
                        <details>
                            <summary><strong>⚠️ Comuni non trovati</strong></summary>
                            <div class="cer-trouble-content">
                                <p><strong>Possibili cause:</strong></p>
                                <ul>
                                    <li>Comune non presente nel database NocoDB</li>
                                    <li>Nome scritto in modo diverso</li>
                                    <li>Problemi case sensitivity</li>
                                </ul>
                                <p><strong>Soluzione:</strong></p>
                                <ol>
                                    <li>Verifica che il comune sia effettivamente su NocoDB</li>
                                    <li>Controlla il campo <code>nome-comune</code> nella tabella</li>
                                    <li>Assicurati che la ricerca sia case-insensitive (lowercase)</li>
                                </ol>
                            </div>
                        </details>
                    </div>
                </div>
                
                <!-- Resources Card -->
                <div class="cer-admin-card">
                    <h2>📚 Risorse</h2>
                    
                    <ul class="cer-resources-list">
                        <li>
                            <strong>🌐 Sito CER-IS:</strong>
                            <a href="https://www.cer-is.com" target="_blank">www.cer-is.com</a>
                        </li>
                        <li>
                            <strong>📧 Email supporto:</strong>
                            <a href="mailto:iscrizioni@cer-is.com">iscrizioni@cer-is.com</a>
                        </li>
                        <li>
                            <strong>🗄️ NocoDB Dashboard:</strong>
                            <a href="https://data.cer-is.com" target="_blank">data.cer-is.com</a>
                        </li>
                        <li>
                            <strong>📖 Documentazione completa:</strong>
                            README.md nella cartella del plugin
                        </li>
                    </ul>
                </div>
                
                <!-- Version Card -->
                <div class="cer-admin-card">
                    <h2>ℹ️ Informazioni Plugin</h2>
                    <p>
                        <strong>Versione:</strong> <?php echo CER_COMUNI_VERSION; ?><br>
                        <strong>Autore:</strong> CER-IS<br>
                        <strong>Licenza:</strong> GPL v2 or later
                    </p>
                </div>
                
            </div>
            
        </div>
        
        <script>
        // Copy to clipboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            const copyButtons = document.querySelectorAll('.cer-copy-btn');
            
            copyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const textToCopy = this.getAttribute('data-copy');
                    
                    // Create temporary textarea
                    const textarea = document.createElement('textarea');
                    textarea.value = textToCopy;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    
                    // Select and copy
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    
                    // Visual feedback
                    const originalText = this.textContent;
                    this.textContent = '✓ Copiato!';
                    this.style.background = '#00a651';
                    this.style.color = 'white';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                        this.style.color = '';
                    }, 2000);
                });
            });
        });
        </script>
        <?php
    }
}
