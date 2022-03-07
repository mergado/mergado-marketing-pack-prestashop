<?php

//Fill in fields that was not already set
foreach($fields_form as $form) {
    foreach($form['form']['input'] as $input) {
        if(!isset($fields_value[$input['name']])) {
            switch($input['type']) {
                case 'hidden':
                    break;
                case 'text':
                    //not working
                    $fields_value[$input['name']] = '';
                    break;
                case 'switch':
                    //not working
                    $field_value[$input['name'] . '_on'] = 0;
                    $field_value[$input['name'] . '_off'] = 1;
                    break;
                case 'checkbox':
                    //working
                    $fields_value[$input['name']] = 0;
                    break;
                case 'select':
                    //who knows
                    $fields_value[$input['name']] = 0;
                    break;
            }
        }
    }
}
?>