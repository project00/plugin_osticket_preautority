$(function() {
    // Logic for VIN validation and dynamic contract loading
    $('input[name="vin"]').on('change', function() {
        var vin = $(this).val();
        if (vin.length === 17) {
            $.get('ajax.php/ps-integration', {a: 'searchContracts', vin: vin}, function(res) {
                // Populate contract dropdown
                console.log('Contracts found:', res);
            });
        }
    });

    $('#authorize-btn').on('click', function() {
        var data = {
            a: 'authorizeIntervention',
            contract_id: $('#contract-id').val(),
            value: $('#intervention-value').val(),
            description: $('#intervention-desc').val()
        };
        $.post('ajax.php/ps-integration', data, function(res) {
            if (res.success) {
                alert('Authorized!');
                location.reload();
            } else {
                alert('Error: ' + res.error);
            }
        });
    });
});
