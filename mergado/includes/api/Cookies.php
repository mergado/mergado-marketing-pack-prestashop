<?php

use Mergado\Tools\NewsClass;
use Mergado\Tools\SettingsClass;

if ($_POST['action'] === 'mmp-cookie-news') {
    $now = new DateTime();
    $date = $now->modify('+14 days')->format(NewsClass::DATE_FORMAT);
    SettingsClass::saveSetting(SettingsClass::COOKIE_NEWS, $date, 0);
    exit;
}