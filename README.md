# CER-IS Comuni Checker Plugin

Plugin WordPress per verificare la copertura dei comuni nella Comunità Energetica e raccogliere segnalazioni.

## Funzionalità

✅ Form interattivo con autocomplete  
✅ Verifica copertura comuni in tempo reale  
✅ Form segnalazione per comuni non coperti  
✅ Invio email automatico  
✅ Design responsive  

## Requisiti

- WordPress 5.0+
- PHP 7.4+
- Plugin **NocoDB Connector** attivo e configurato
- Plugin SMTP per invio email

## Installazione

1. Copia la cartella in `wp-content/plugins/`
2. Attiva il plugin
3. Configura NocoDB Connector (Settings → NocoDB Connector)
4. Inserisci lo shortcode `[cer_check_comune]` in una pagina

## Utilizzo

Inserisci in una pagina WordPress:

```
[cer_check_comune]
```

Con titolo personalizzato:

```
[cer_check_comune title="Verifica il tuo Comune"]
```

## Configurazione

Vai su **Settings → Comuni Checker** per:

- Verificare lo stato del sistema
- Copiare lo shortcode
- Vedere le istruzioni complete
- Troubleshooting

## Personalizzazione

### Email Destinatario
Modifica `includes/class-comuni-ajax.php` riga ~103

### URL Iscrizioni
Modifica file principale riga ~41

### Colori Brand
Modifica `assets/style.css` variabili CSS

## Supporto

Per assistenza: Settings → Comuni Checker (pagina completa con guida e troubleshooting)

## Licenza

GPL v2 or later
