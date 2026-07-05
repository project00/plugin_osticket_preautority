$(function() {
    var $vinField = $('input[name="vin"]');
    var $contractSelect = $('<select id="contract-selector" class="form-control" style="margin-top:10px;"><option value="">Seleziona Contratto</option></select>');

    $vinField.after($contractSelect);
    $contractSelect.hide();

    // UI for additional intervention fields
    var $interventionFields = $('<div id="intervention-details" style="display:none; margin-top:10px;">' +
        '<input type="number" id="intervention-amount" class="form-control" placeholder="Valore Intervento (€)" style="margin-bottom:5px;">' +
        '<input type="text" id="invoice-number" class="form-control" placeholder="Numero Fattura" style="margin-bottom:5px;">' +
        '<input type="date" id="invoice-date" class="form-control" placeholder="Data Fattura" style="margin-bottom:5px;">' +
        '<textarea id="intervention-desc" class="form-control" placeholder="Descrizione Intervento" style="margin-bottom:5px;"></textarea>' +
        '<button type="button" id="authorize-intervention" class="btn btn-primary">Autorizza Intervento</button>' +
        '</div>');

    $contractSelect.after($interventionFields);

    $vinField.on('input', function() {
        var vin = $(this).val();
        if (vin.length === 17) {
            fetchContracts(vin);
        } else {
            $contractSelect.hide();
            $interventionFields.hide();
        }
    });

    function fetchContracts(vin) {
        $.get('ajax.php/ps-integration', {
            a: 'searchContracts',
            vin: vin,
            customer_id: $('input[name="user_id"]').val()
        }, function(res) {
            if (res && res.length > 0) {
                $contractSelect.empty().append('<option value="">Seleziona Contratto</option>');
                res.forEach(function(c) {
                    $contractSelect.append('<option value="'+c.id_contract+'" data-residual="'+c.residual_value+'">' +
                        '#' + c.id_contract + ' - Residuo: ' + c.residual_value + '€' +
                        '</option>');
                });
                $contractSelect.show();
                $interventionFields.show();
            } else {
                $contractSelect.hide();
                $interventionFields.hide();
            }
        });
    }

    $(document).on('click', '#authorize-intervention', function() {
        var contractId = $('#contract-selector').val();
        var amount = $('#intervention-amount').val();

        if (!contractId || !amount) {
            alert('Seleziona un contratto e inserisci un importo.');
            return;
        }

        var residual = parseFloat($('#contract-selector option:selected').data('residual'));
        if (parseFloat(amount) > residual) {
            alert('Errore: Importo superiore al residuo disponibile (' + residual + '€).');
            return;
        }

        $.post('ajax.php/ps-integration', {
            a: 'authorizeIntervention',
            contract_id: contractId,
            amount: amount,
            invoice_number: $('#invoice-number').val(),
            invoice_date: $('#invoice-date').val(),
            description: $('#intervention-desc').val(),
            ticket_id: $('#ticket-id').val()
        }, function(res) {
            if (res.success) {
                alert('Intervento autorizzato con successo!');
                location.reload();
            } else {
                alert('Errore: ' + (res.error || 'Verifica il saldo residuo.'));
            }
        });
    });
});
