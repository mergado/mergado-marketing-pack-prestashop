<?php

if ($_POST['action'] === 'ajax_disable_alert') {
    $alertName = $_POST['name'] ?? '';
    $feedName = $_POST['feed'] ?? '';

    $alertClass = new AlertClass();
    $alertClass->setAlertDisabled($feedName, $alertName);
    exit;
}

if ($_POST['action'] === 'ajax_disable_section') {
    $sectionName = $_POST['section'] ?? '';

    if ($sectionName !== '') {
        $alertClass = new AlertClass();
        $alertClass->setSectionDisabled($sectionName);
        exit;
    } else {
        exit;
    }
}

if ($_POST['action'] === 'ajax_add_alert') {
    $alertName = $_POST['name'] ?? '';
    $feedName = $_POST['feed'] ?? '';

    if ($alertName !== '' && $feedName !== '') {
        $alertClass = new AlertClass();
        $alertClass->setErrorActive($feedName, $alertName);
        exit;
    } else {
        exit;
    }
}
