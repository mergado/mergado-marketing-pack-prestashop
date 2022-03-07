<?php

use Mergado\Tools\NewsClass;

if($_POST['action'] === 'mmp-get-news') {
    JsonResponse::send_json_success(NewsClass::getNewsByStatusAndLanguageAndCategory(false, $this->context->language->iso_code));
} elseif($_POST['action'] === 'mmp-set-readed') {
    NewsClass::setArticlesShown($_POST['ids']);
    exit;
}