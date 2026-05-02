<?php

require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');

class ContractManagerPlugin extends Plugin {
    var $config_class = "ContractManagerConfig";

    function bootstrap() {
        Signal::connect('staff.ticket.view', array($this, 'onTicketView'));
        Signal::connect('client.ticket.view', array($this, 'onTicketView'));
        Signal::connect('ajax.staff', array($this, 'onAjax'));
        Signal::connect('ajax.client', array($this, 'onAjax'));
    }

    function onTicketView($ticket) {
        global $thisstaff, $thisclient;

        $isStaff = isset($thisstaff) && $thisstaff instanceof Staff;

        echo "<style>" . file_get_contents(__DIR__ . '/css/plugin.css') . "</style>";

        $config = $this->getConfig();
        echo "<script>
            var contractManagerConfig = {
                ticket_id: " . $ticket->getId() . ",
                is_staff: " . ($isStaff ? 'true' : 'false') . ",
                ajax_url: '" . ($isStaff ? 'ajax.php' : 'ajax.php') . "/contract-manager'
            };
            " . file_get_contents(__DIR__ . '/js/plugin.js') . "
        </script>";
    }

    function onAjax($dispatcher) {
        $dispatcher->append(
            url_get('^/contract-manager$', function($matches) {
                include_once('class.ContractManagerAjax.php');
                $ajax = new ContractManagerAjax($this);

                $action = $_REQUEST['action'] ?? '';
                switch ($action) {
                    case 'verify-vin': $ajax->verifyVin(); break;
                    case 'verify-plate': $ajax->verifyPlate(); break;
                    case 'save-verification': $ajax->saveVerification(); break;
                    case 'authorize': $ajax->authorize(); break;
                    case 'deny': $ajax->deny(); break;
                    case 'test-connection': $ajax->testConnection(); break;
                }
            })
        );
    }

    function install() {
        parent::install();
        $this->createCustomFields();
    }

    private function createCustomFields() {
        $required_fields = array(
            'vin' => array('label' => 'VIN', 'type' => 'text'),
            'targa' => array('label' => 'Targa', 'type' => 'text'),
            'autorizzazione' => array('label' => 'Autorizzazione', 'type' => 'text'),
            'credito_residuo' => array('label' => 'Credito residuo', 'type' => 'text'),
            'id_contratto' => array('label' => 'ID contratto', 'type' => 'text'),
            'costo' => array('label' => 'Costo', 'type' => 'text'),
            'numero_fattura' => array('label' => 'Numero Fattura', 'type' => 'text'),
        );

        $form = DynamicForm::lookup(array('type' => 'T'));
        if ($form) {
            foreach ($required_fields as $name => $info) {
                if (!$form->getField($name)) {
                    $field = DynamicField::create(array(
                        'form_id' => $form->get('id'),
                        'type' => $info['type'],
                        'name' => $name,
                        'label' => $info['label'],
                        'flags' => DynamicField::FLAG_ENABLED,
                    ));
                    $field->save();
                }
            }
        }
    }
}
?>
