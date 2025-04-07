<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        <?php echo $translateFunction('Cookie consent settings', 'cookies'); ?>
    </div>

    <div>
        <strong><?php echo $translateFunction('When you activate this feature, advertising scripts that use cookies will not automatically run unless consent is granted.', 'cookies'); ?></strong>
        <p><?php echo $translateFunction('Using this feature is at your own risk. The creator of module, the company Mergado technologies, LLC, is not liable for any losses or damages in any form.', 'cookies'); ?></p>
    </div>

    <div class="mmp_singleCookie">
        <?php echo $activationForm ?>
    </div>

    <div>
        <h2><?php echo $translateFunction('Cookie law plugin support', 'cookies');?></h2>
        <p><?php echo sprintf($translateFunction('If you have %s activated %s Cookie law %s module %s, there is %s no need to set up anything%s.', 'cookies'), '<strong>', '<a href="https://prestashop.valasinec.cz/legislativa-ucetnictvi/86-cookie-law-informacni-okno-dle-narizeni-eu-gdpr.html">', '</a>','</strong>', '<strong>', '</strong>'); ?></p>
    </div>

    <div style="background-color: #f2f2f2; padding: 1px 15px 16px; border-radius: 4px; margin-bottom: 30px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%); padding-top: 15px;">
        <p><?php echo $translateFunction('The functions are divided by consent type as follows:', 'cookies'); ?></p>
        <div>
            <strong><?php echo $translateFunction('Advertisement', 'cookies'); ?>:</strong> <?php echo $translateFunction('Google Ads, Facebook Pixel, Heureka conversion tracking, Glami piXel, Sklik retargeting, Sklik conversion tracking, Zboží conversion tracking, Etarget, Najnakup.sk, Pricemania, Kelkoo conversion tracking, Biano Pixel', 'cookies'); ?></div>
        <div>
            <strong><?php echo $translateFunction('Analytics', 'cookies'); ?>:</strong> <?php echo $translateFunction('Google Analytics', 'cookies'); ?></div>
        <div>
            <strong><?php echo $translateFunction('Functional', 'cookies'); ?>:</strong> <?php echo $translateFunction('Google Customer Reviews, Heureka Verified by Customer', 'cookies'); ?></div>

        <hr style="margin-top: 16px; border-top: 1px solid #c3c4c7;">

        <p style="margin-bottom: 0;"><i><?php echo $translateFunction('Google Tag Manager and other unlisted features are not dependent on consent.', 'cookies'); ?></i></p>
    </div>

    <div>
        <h3 style="margin-left: 0; margin-right: 0; padding-left: 0; border-bottom: 0; margin-bottom: 0; margin-top: 15px;"><?php echo $translateFunction('Set cookie values manually', 'cookies'); ?></h3>
        <p><?php echo $translateFunction('Manually type name of the cookie that corresponds to selected category.', 'cookies'); ?></p>

        <p>
            <?php
            $translateFunction('To activate scripts after change of user consent call javascript code [1]window.mmp.cookies.functions.checkAndSetCookies()[/1] or reload the page.', 'cookies');

            echo Translate::getModuleTranslation(
                $module,
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

    <?php echo $inputForm ?>
</div>
