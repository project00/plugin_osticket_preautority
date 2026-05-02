<?php

require_once(INCLUDE_DIR . 'class.plugin.php');
require_once(INCLUDE_DIR . 'class.forms.php');

class ContractManagerConfig extends PluginConfig {
    function translate() {
        return array();
    }

    function getOptions() {
        return array(
            'api_info' => new SectionBreakField(array(
                'label' => 'External API Settings',
            )),
            'api_url' => new TextboxField(array(
                'label' => 'Base API URL',
                'configuration' => array('size' => 60, 'length' => 255),
            )),
            'api_key' => new PasswordField(array(
                'label' => 'Authorization Header',
                'configuration' => array('size' => 60, 'length' => 255),
            )),
            'api_method' => new ChoiceField(array(
                'label' => 'Default Method',
                'choices' => array('GET' => 'GET', 'POST' => 'POST'),
                'default' => 'GET',
            )),
            'test_conn' => new SectionBreakField(array(
                'label' => 'Test Connection',
                'hint' => 'You can test the connection via the browser console by calling: `$.get("ajax.php/contract-manager?action=test-connection")` while logged in as staff.'
            )),
            'endpoints' => new SectionBreakField(array(
                'label' => 'Endpoints',
            )),
            'search_vin_endpoint' => new TextboxField(array(
                'label' => 'VIN Search',
                'default' => '/search/vin',
            )),
            'search_plate_endpoint' => new TextboxField(array(
                'label' => 'Plate Search',
                'default' => '/search/plate',
            )),
            'impute_cost_endpoint' => new TextboxField(array(
                'label' => 'Impute Cost',
                'default' => '/impute/cost',
            )),
        );
    }
}
?>
