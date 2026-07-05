<?php
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once(INCLUDE_DIR . 'class.forms.php');

class ContractIntegrationConfig extends PluginConfig {
    function getOptions() {
        return array(
            'ps_api' => new SectionBreakField(array(
                'label' => 'PrestaShop API Settings',
            )),
            'ps_api_url' => new TextboxField(array(
                'label' => 'Base API URL',
                'configuration' => array('size' => 60, 'length' => 255),
            )),
            'ps_api_key' => new PasswordField(array(
                'label' => 'API Key (X-API-Key)',
                'configuration' => array('size' => 60, 'length' => 255),
            )),
        );
    }
}
