<?php

/*
 * With this override, you can use 255 characters for Configuration
 */

class Configuration extends ConfigurationCore {

    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        parent::__construct($id, $id_lang, $id_shop);
        $definition['fields']['name'] = array('type' => self::TYPE_STRING, 'validate' => 'isConfigName', 'required' => true, 'size' => 255);
    }

}
