(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log("CER-IS Comuni Checker: Script loaded and document ready.");

        let searchTimeout;
        const $input = $('#cer-comune-input');
        const $searchBtn = $('#cer-comune-search-btn');
        const $suggestions = $('#cer-comune-suggestions');
        const $loading = $('#cer-comune-loading');
        const $result = $('#cer-comune-result');
        const $reportSection = $('#cer-comuni-report-section');
        const $reportForm = $('#cer-report-form');
        const $reportCancel = $('#cer-report-cancel');
        const $reportResult = $('#cer-report-result');
        
        /**
         * Autocomplete on input
         */
        $input.on('input', function() {
            console.log("CER-IS Comuni Checker: Input event fired.");
            const query = $(this).val().trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Hide suggestions if query too short
            if (query.length < 2) {
                $suggestions.removeClass('active').empty();
                return;
            }
            
            // Debounce search
            searchTimeout = setTimeout(function() {
                autocompleteComune(query);
            }, 300);
        });
        
        /**
         * Autocomplete AJAX
         */
        function autocompleteComune(query) {
            $.ajax({
                url: cerComuniData.ajax_url,
                type: 'POST',
                data: {
                    action: 'cer_autocomplete_comune',
                    nonce: cerComuniData.nonce,
                    query: query
                },
                success: function(response) {
                    console.log('CER-IS Comuni Checker: Autocomplete response:', response);
                    if (response.success && response.data && response.data.suggestions.length > 0) {
                        renderSuggestions(response.data.suggestions, query);
                    } else {
                        $suggestions.removeClass('active').empty();
                        if (!response.success && response.data) {
                            console.error('CER-IS Comuni Checker: Autocomplete Error:', response.data.message, response.data.debug_info || '(no debug info)');
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('CER-IS Comuni Checker: Autocomplete AJAX Error:', textStatus, errorThrown);
                    $suggestions.removeClass('active').empty();
                }
            });
        }
        
        /**
         * Render autocomplete suggestions
         */
        function renderSuggestions(suggestions, query) {
            $suggestions.empty();
            
            suggestions.forEach(function(item) {
                // Highlight matching text
                const regex = new RegExp('(' + query + ')', 'gi');
                const highlighted = item.nome.replace(regex, '<strong>$1</strong>');
                
                const $item = $('<div class="cer-suggestion-item">')
                    .html(highlighted)
                    .data('comune', item.nome)
                    .on('click', function() {
                        const comune = $(this).data('comune');
                        $input.val(comune);
                        $suggestions.removeClass('active').empty();
                        searchComune(comune);
                    });
                
                $suggestions.append($item);
            });
            
            $suggestions.addClass('active');
        }
        
        /**
         * Click outside to close suggestions
         */
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.cer-comuni-search-box, .cer-comune-suggestions').length) {
                $suggestions.removeClass('active');
            }
        });
        
        /**
         * Search button click
         */
        $searchBtn.on('click', function() {
            console.log("CER-IS Comuni Checker: Search button clicked.");
            const comune = $input.val().trim();
            if (comune) {
                searchComune(comune);
            }
        });
        
        /**
         * Enter key to search
         */
        $input.on('keypress', function(e) {
            if (e.which === 13) {
                console.log("CER-IS Comuni Checker: Enter key pressed.");
                e.preventDefault();
                $suggestions.removeClass('active');
                const comune = $(this).val().trim();
                if (comune) {
                    searchComune(comune);
                }
            }
        });
        
        /**
         * Search comune AJAX
         */
        function searchComune(comune) {
            // Reset
            $result.hide();
            $reportSection.hide();
            $loading.show();
            $searchBtn.prop('disabled', true);
            
            $.ajax({
                url: cerComuniData.ajax_url,
                type: 'POST',
                data: {
                    action: 'cer_search_comune',
                    nonce: cerComuniData.nonce,
                    comune: comune
                },
                success: function(response) {
                    console.log('CER-IS Comuni Checker: Search response:', response);
                    if (response.success) {
                        if (response.data.covered) {
                            // Comune is covered!
                            showSuccessResult(response.data.message, comune);
                        } else {
                            // Comune NOT covered
                            showWarningResult(response.data.message);
                            // Pre-fill report form
                            $('#report-comune').val(comune);
                        }
                    } else {
                        if (response.data) {
                            console.error('CER-IS Comuni Checker: Search Error:', response.data.message, response.data.debug_info || '(no debug info)');
                        }
                        showErrorResult(response.data ? response.data.message : cerComuniData.strings.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('CER-IS Comuni Checker: Search AJAX Error:', textStatus, errorThrown);
                    showErrorResult(cerComuniData.strings.error);
                },
                complete: function() {
                    $loading.hide();
                    $searchBtn.prop('disabled', false);
                }
            });
        }
        
        /**
         * Show success result (comune covered)
         */
        function showSuccessResult(message, comune) {
            const html = `
                <div class="cer-result-content">
                    ${message}
                </div>
                <div class="cer-result-action">
                    <a href="${cerComuniData.iscrizioni_url}" class="cer-cta-link">
                        🎉 Iscriviti Ora
                    </a>
                </div>
            `;
            
            $result
                .removeClass('error warning')
                .addClass('success')
                .html(html)
                .show();
        }
        
        /**
         * Show warning result (comune NOT covered)
         */
        function showWarningResult(message) {
            const html = `
                <div class="cer-result-content">
                    ${message}
                </div>
            `;
            
            $result
                .removeClass('success error')
                .addClass('warning')
                .html(html)
                .show();
            
            // Show report section
            setTimeout(function() {
                $reportSection.slideDown(400);
                // Scroll to report form
                $('html, body').animate({
                    scrollTop: $reportSection.offset().top - 20
                }, 400);
            }, 300);
        }
        
        /**
         * Show error result
         */
        function showErrorResult(message) {
            $result
                .removeClass('success warning')
                .addClass('error')
                .html(`<div class="cer-result-content">${message}</div>`)
                .show();
        }
        
        /**
         * Cancel report form
         */
        $reportCancel.on('click', function() {
            $reportSection.slideUp(400);
            $reportForm[0].reset();
            $reportResult.hide();
        });
        
        /**
         * Submit report form
         */
        $reportForm.on('submit', function(e) {
            e.preventDefault();
            
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Disable and show loading
            $submitBtn.prop('disabled', true).text(cerComuniData.strings.sending);
            $reportResult.hide();
            
            $.ajax({
                url: cerComuniData.ajax_url,
                type: 'POST',
                data: $(this).serialize() + '&action=cer_report_comune',
                success: function(response) {
                    if (response.success) {
                        $reportResult
                            .removeClass('error')
                            .addClass('success')
                            .html(response.data.message)
                            .slideDown();
                        
                        // Reset form
                        $reportForm[0].reset();
                        
                        // Hide form after 3 seconds
                        setTimeout(function() {
                            $reportSection.slideUp(400);
                            $reportResult.hide();
                        }, 3000);
                    } else {
                        $reportResult
                            .removeClass('success')
                            .addClass('error')
                            .html(response.data.message)
                            .slideDown();
                    }
                },
                error: function() {
                    $reportResult
                        .removeClass('success')
                        .addClass('error')
                        .html(cerComuniData.strings.error)
                        .slideDown();
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
        
    });
    
})(jQuery);
