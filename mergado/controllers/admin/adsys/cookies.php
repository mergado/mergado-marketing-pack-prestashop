<div class="panel">
<div class="panel-heading">
    <i class="icon-cogs"></i>
    <?php echo $this->module->l('Cookie consent settings', 'cookies'); ?>
</div>

<div>
    <strong><?php echo $this->module->l('When you activate this feature, advertising scripts that use cookies will not automatically run unless consent is granted.', 'cookies'); ?></strong>
    <p><?php echo $this->module->l('Using this feature is at your own risk. The creator of module, the company Mergado technologies, LLC, is not liable for any losses or damages in any form.', 'cookies'); ?></p>
</div>

<?php

use Mergado\includes\tools\CookieService;

$fields_form[0]['form'] = [
    'input' => [
        [
            'type' => 'hidden',
            'name' => 'page'
        ],
        [
            'type' => 'hidden',
            'name' => 'id_shop'
        ],
        [
            'name' => CookieService::FIELD_COOKIES_ENABLE,
            'label' => $this->module->l('Activate cookie consent settings', 'cookies'),
            'validation' => 'isBool',
            'cast' => 'intval',
            'type' => (version_compare(_PS_VERSION_, Mergado::PS_V_16) < 0) ? 'radio' : 'switch',
            'class' => 'switch15',
            'values' => [
                [
                    'id' => 'mergado_cookies_on',
                    'value' => 1,
                    'label' => $this->module->l('Yes')
                ],
                [
                    'id' => 'mergado_cookies_off',
                    'value' => 0,
                    'label' => $this->module->l('No')
                ]
            ],
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
?>
<div class="mmp_singleCookie">
    <?php
    echo @$helper->generateForm($fields_form);
    ?>
</div>

<div>
    <h2><?php echo $this->module->l('Cookie law plugin support', 'cookies');?></h2>
    <p><?php echo sprintf($this->module->l('If you have %s activated %s Cookie law %s module %s, there is %s no need to set up anything%s.', 'cookies'), '<strong>', '<a href="https://prestashop.valasinec.cz/legislativa-ucetnictvi/86-cookie-law-informacni-okno-dle-narizeni-eu-gdpr.html">', '</a>','</strong>', '<strong>', '</strong>'); ?></p>
</div>

<div style="background-color: #f2f2f2; padding: 1px 15px 16px; border-radius: 4px; margin-bottom: 30px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%); padding-top: 15px;">
    <p><?php echo $this->module->l('The functions are divided by consent type as follows:', 'cookies'); ?></p>
    <div>
        <strong><?php echo $this->module->l('Advertisement', 'cookies'); ?>:</strong> <?php echo $this->module->l('Google Ads, Facebook Pixel, Heureka conversion tracking, Glami piXel, Sklik retargeting, Sklik conversion tracking, Zboží conversion tracking, Etarget, Najnakup.sk, Pricemania, Kelkoo conversion tracking, Biano Pixel', 'cookies'); ?></div>
    <div>
        <strong><?php echo $this->module->l('Analytics', 'cookies'); ?>:</strong> <?php echo $this->module->l('Google Analytics', 'cookies'); ?></div>
    <div>
        <strong><?php echo $this->module->l('Functional', 'cookies'); ?>:</strong> <?php echo $this->module->l('Google Customer Reviews, Heureka Verified by Customer', 'cookies'); ?></div>

    <hr style="margin-top: 16px; border-top: 1px solid #c3c4c7;">

    <p style="margin-bottom: 0;"><i><?php echo $this->module->l('Google Tag Manager and other unlisted features are not dependent on consent.', 'cookies'); ?></i></p>
</div>

<div>
    <h3 style="margin-left: 0; margin-right: 0; padding-left: 0; border-bottom: 0; margin-bottom: 0; margin-top: 15px;"><?php echo $this->module->l('Set cookie values manually', 'cookies'); ?></h3>
    <p><?php echo $this->module->l('Manually type name of the cookie that corresponds to selected category.', 'cookies'); ?></p>

    <p>
        <?php
        $this->module->l('To activate scripts after change of user consent call javascript code [1]window.mmp.cookies.functions.checkAndSetCookies()[/1] or reload the page.', 'cookies');

        echo TranslateCore::getModuleTranslation(
            $this->module,
            'To activate scripts after change of user consent call javascript code [1]window.mmp.cookies.functions.checkAndSetCookies()[/1] or reload the page.',
            'cookies',
            ['[1]' => '<code>', '[/1]' => '</code>'],
            false,
            null,
            true,
            false
        );
        ?>
    </p>
</div>

<?php
$fields_form1[0]['form'] = [
    'input' => [
        [
            'type' => 'hidden',
            'name' => 'page'
        ],
        [
            'type' => 'hidden',
            'name' => 'id_shop'
        ],
        [
            'name' => CookieService::FIELD_ANALYTICAL_USER,
            'label' => $this->module->l('Analytics', 'cookies'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => CookieService::FIELD_ADVERTISEMENT_USER,
            'label' => $this->module->l('Advertisement', 'cookies'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
        [
            'name' => CookieService::FIELD_FUNCTIONAL_USER,
            'label' => $this->module->l('Functional', 'cookies'),
            'type' => 'text',
            'visibility' => Shop::CONTEXT_ALL,
        ],
    ],
    'submit' => [
        'title' => $this->module->l('Save'),
        'name' => 'submit' . $this->name
    ]
];

include __MERGADO_FORMS_DIR__ . 'helpers/helperForm.php';
echo @$helper->generateForm($fields_form1);

?>
</div>
