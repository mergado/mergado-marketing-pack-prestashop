{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    www.mergado.cz
*  @copyright 2016 Mergado technologies, s. r. o.
*  @license   LICENSE.txt
*}
{if $seznam_retargeting_id != ''}
    <script src="https://c.seznam.cz/js/rc.js"></script>

    <script>
      if (typeof identities === 'undefined') {
          {literal}
        let identities = {};
          {/literal}

          {if $customerData['email'] !== null }
        identities.eid = "{$customerData['email']}";
          {/if}
          {if $customerData['phone'] !== null}
        identities.tid = "{$customerData['phone']}";
          {/if}

        window.sznIVA.IS.updateIdentities(identities);
      }
    </script>

    <script>
      var sklikRetargetingConf = {
        rtgId: {$seznam_retargeting_id},
        consent: {$seznam_consent_advertisement},
      };

      if (window.rc && window.rc.retargetingHit) {
        window.rc.retargetingHit(sklikRetargetingConf);
      }
    </script>

    <!-- Update consent on accept -->
    <script>
      window.mmp.cookies.sections.advertisement.functions.sklikRetargeting = function () {
        sklikRetargetingConf.consent = 1;
      };
    </script>
{/if}
