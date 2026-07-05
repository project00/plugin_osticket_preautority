<?php
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once('config.php');

class ContractIntegrationPlugin extends Plugin {
    var $config_class = "ContractIntegrationConfig";

    function bootstrap() {
        Signal::connect('staff.ticket.view', array($this, 'injectUI'));
        Signal::connect('ajax.staff', array($this, 'handleAjax'));
    }

    function injectUI($ticket) {
        echo "<script src='" . ROOT_PATH . "plugins/ost_contract_integration/js/contract.js'></script>";
        echo "<link rel='stylesheet' href='" . ROOT_PATH . "plugins/ost_contract_integration/css/style.css'>";
    }

    function handleAjax($dispatcher) {
        $dispatcher->append(
            url_get('^/ps-integration', function($matches) {
                require_once('class.AjaxHandler.php');
                $handler = new AjaxHandler($this->getConfig());
                $action = $_REQUEST['a'];
                if (method_exists($handler, $action)) {
                    $handler->$action();
                }
            })
        );
    }
}
