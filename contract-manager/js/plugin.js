$(function() {
    if (typeof contractManagerConfig === 'undefined') return;

    function getCsrfToken() {
        return $('meta[name="csrf_token"]').attr('content') || '';
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': getCsrfToken()
        }
    });

    function showNotify(msg, type) {
        if (typeof $.sys_notify === 'function') {
            $.sys_notify(msg, type);
        } else {
            alert(msg);
        }
    }

    function addVerifyButtons() {
        var fields = ['vin', 'targa'];
        fields.forEach(function(fieldName) {
            $('input[name^="field_"]').each(function() {
                var $input = $(this);
                var labelText = $input.closest('tr').find('th').text().toLowerCase();

                if (labelText.includes(fieldName)) {
                    if ($input.next('.verify-btn').length === 0) {
                        var $btn = $('<button type="button" class="btn btn-info btn-xs verify-btn" style="margin-left: 5px;">Verifica contratti</button>');
                        $input.after($btn);

                        $btn.on('click', function() {
                            verifyContract($input.val(), fieldName);
                        });
                    }
                }
            });
        });
    }

    function addActionButtons() {
        if (contractManagerConfig.is_staff !== 'true') return;

        var $actions = $('.ticket_info .flush-right, #ticket-view .flush-right').first();
        if ($actions.length === 0 || $('.auth-btn').length > 0) return;

        // Check if already authorized
        var isAuth = false;
        $('input[name^="field_"]').each(function() {
            if ($(this).closest('tr').find('th').text().toLowerCase().includes('autorizzazione')) {
                if ($(this).val() && $(this).val() !== 'NEGATA') isAuth = true;
            }
        });

        var $authBtn = $('<a class="btn btn-success action-button auth-btn" href="#"><i class="icon-ok-sign"></i> Autorizza</a>');
        var $denyBtn = $('<a class="btn btn-danger action-button deny-btn" href="#"><i class="icon-remove-sign"></i> Nega</a>');

        if (isAuth) {
            $authBtn.addClass('disabled').attr('title', 'Già autorizzato');
        }

        $actions.prepend($denyBtn).prepend($authBtn);

        $authBtn.on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) return;
            showAuthForm();
        });

        $denyBtn.on('click', function(e) {
            e.preventDefault();
            if (confirm('Sei sicuro di voler negare l\'autorizzazione?')) {
                denyAuthorization();
            }
        });
    }

    function verifyContract(value, type) {
        if (!value) {
            showNotify('Inserire un valore.', 'warning');
            return;
        }

        var action = type === 'vin' ? 'verify-vin' : 'verify-plate';
        $.ajax({
            url: contractManagerConfig.ajax_url,
            data: { action: action, [type]: value },
            type: 'GET',
            beforeSend: function() {
                $('.verify-btn').prop('disabled', true).text('...');
            },
            success: function(res) {
                if (res.success) {
                    var data = res.data;
                    var contractsStr = data.contracts ? data.contracts.map(c => c.id).join(', ') : 'Nessuno';
                    var msg = "Contratti: " + contractsStr;
                    if (data.residual_credit !== undefined) {
                        msg += "\nCredito residuo: " + data.residual_credit + "€";
                        updateFieldValue('credito residuo', data.residual_credit);
                    }
                    updateFieldValue('id contratto', contractsStr);
                    showNotify(msg, 'success');

                    // AZIONI: Salvare su ticket
                    saveVerification(data.residual_credit, contractsStr);
                } else {
                    showNotify('Errore API: ' + (res.error || 'Sconosciuto'), 'error');
                }
            },
            complete: function() {
                $('.verify-btn').prop('disabled', false).text('Verifica contratti');
            }
        });
    }

    function saveVerification(credit, contracts) {
        $.ajax({
            url: contractManagerConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'save-verification',
                ticket_id: contractManagerConfig.ticket_id,
                credit: credit,
                contracts: contracts,
                __CSRFToken__: getCsrfToken()
            }
        });
    }

    function showAuthForm() {
        var $form = $('<div id="auth-modal" style="display:none; position:fixed; z-index:1000; left:50%; top:50%; transform:translate(-50%, -50%); background:white; padding:20px; border:1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.5);">' +
            '<h3>Autorizza Intervento</h3>' +
            '<p><label>Costo: <input type="number" id="auth-cost" step="0.01"></label></p>' +
            '<p><label>Numero Fattura: <input type="text" id="auth-invoice"></label></p>' +
            '<button id="confirm-auth" class="btn btn-success">Conferma</button> ' +
            '<button id="cancel-auth" class="btn">Annulla</button>' +
            '</div>');

        $('body').append($form);
        $form.fadeIn();
        $('#cancel-auth').on('click', function() { $form.remove(); });
        $('#confirm-auth').on('click', function() {
            var cost = $('#auth-cost').val();
            var invoice = $('#auth-invoice').val();
            if (!cost || !invoice) {
                showNotify('Tutti i campi sono obbligatori.', 'error');
                return;
            }
            submitAuthorization(cost, invoice, $form);
        });
    }

    function submitAuthorization(cost, invoice, $modal) {
        $.ajax({
            url: contractManagerConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'authorize',
                ticket_id: contractManagerConfig.ticket_id,
                cost: cost,
                invoice_number: invoice,
                __CSRFToken__: getCsrfToken()
            },
            success: function(res) {
                if (res.success) {
                    showNotify('Autorizzazione completata!', 'success');
                    $modal.remove();
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showNotify('Errore: ' + res.error, 'error');
                }
            }
        });
    }

    function denyAuthorization() {
        $.ajax({
            url: contractManagerConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'deny',
                ticket_id: contractManagerConfig.ticket_id,
                __CSRFToken__: getCsrfToken()
            },
            success: function(res) {
                showNotify('Autorizzazione negata.', 'info');
                setTimeout(function() { location.reload(); }, 1000);
            }
        });
    }

    function updateFieldValue(label, value) {
        $('input[name^="field_"]').each(function() {
            if ($(this).closest('tr').find('th').text().toLowerCase().includes(label)) {
                $(this).val(value);
            }
        });
    }

    addVerifyButtons();
    addActionButtons();

    $(document).on('pjax:success', function() {
        addVerifyButtons();
        addActionButtons();
    });
});
