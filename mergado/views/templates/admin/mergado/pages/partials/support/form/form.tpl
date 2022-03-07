<form method="post" class="mmp_contactForm">
    <div class="mmp_contactForm__line">
        <div class="mmp_contactForm__col">
            <label for="email">{l s='Email for reply' mod='mergado'}</label>
            <input type="email" id="email" name="email" required />
        </div>
        <div class="mmp_contactForm__col">
            <label for="subject">{l s='Subject' mod='mergado'}</label>
            <input type="text" id="subject" name="subject" required />
        </div>
    </div>
    <div class="mmp_contactForm__issue">
        <label for="issue">{l s='Issue description' mod='mergado'}</label>
        <textarea name="issue" id="issue" cols="20" rows="7" required></textarea>
    </div>

    <p class="mmp_contactForm__notice">{l s='By sending this ticket you also agree to submit diagnostic information of your website. This data includes your PrestaShop version, PHP version, Mergado Pack plugin settings, export and cron URLs, list of modules, themes and logs. All this information will help us to process your request faster.' mod='mergado'}</p>

    <div class="mmp_btnHolder mmp_btnHolder--right" style="margin-top: 10px;">
        <button type="submit" name="submit-ticket-form" class="mmp_btn__blue mmp_btn__blue--small">
            <svg class="mmp_icon">
                <use xlink:href="{$mmp['images']['baseImageUrl']}email"></use>
            </svg>
            {l s='Send ticket' mod='mergado'}
        </button>
    </div>

    {if $mmp['pageContent']['support']['form']['submitted']}
        <div class="mmp_contactForm__formSent">
            {l s='Your ticket has been sent. We will answer you as soon as possible' mod='mergado'}
        </div>
    {/if}
</form>
