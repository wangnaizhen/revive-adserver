<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

require_once MAX_PATH . '/lib/OA/Admin/Statistics/Delivery/CommonEntity.php';

/**
 * The class to display the delivery statistcs for the page:
 *
 * Statistics -> Publishers & Zones -> Zones
 *
 * @package    OpenXAdmin
 * @subpackage StatisticsDelivery
 */
class OA_Admin_Statistics_Delivery_Controller_AffiliateZones extends OA_Admin_Statistics_Delivery_CommonEntity
{
    /**
     * The final "child" implementation of the PHP5-style constructor.
     *
     * @param array $aParams An array of parameters. The array should
     *                       be indexed by the name of object variables,
     *                       with the values that those variables should
     *                       be set to. For example, the parameter:
     *                       $aParams = array('foo' => 'bar')
     *                       would result in $this->foo = bar.
     */
    public function __construct($aParams)
    {
        // Set this page's entity/breakdown values
        $this->entity = 'affiliate';
        $this->breakdown = 'zones';

        // This page uses the day span selector element
        $this->showDaySpanSelector = true;

        parent::__construct($aParams);
    }

    /**
     * The final "child" implementation of the parental abstract method.
     *
     * @see OA_Admin_Statistics_Common::start()
     */
    public function start()
    {
        // Get the preferences
        $aPref = $GLOBALS['_MAX']['PREF'];

        // Get parameters
        $publisherId = $this->_getId('publisher');

        // Security check
        OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN, OA_ACCOUNT_MANAGER, OA_ACCOUNT_TRAFFICKER);
        $this->_checkAccess(['publisher' => $publisherId]);

        // Add standard page parameters
        $this->aPageParams = [
            'affiliateid' => $publisherId,
        ];

        // Load the period preset and stats breakdown parameters
        $this->_loadPeriodPresetParam();
        $this->_loadStatsBreakdownParam();

        // Load $_GET parameters
        $this->_loadParams();

        // HTML Framework
        if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN) || OA_Permission::isAccount(OA_ACCOUNT_MANAGER)) {
            $this->pageId = '2.4.2';
            $this->aPageSections = ['2.4.1', '2.4.2', '2.4.3'];
        } elseif (OA_Permission::isAccount(OA_ACCOUNT_TRAFFICKER)) {
            $this->pageId = '1.2';
            $this->aPageSections = ['1.1', '1.2', '1.3'];
        }

        // Add breadcrumbs
        $this->_addBreadcrumbs('publisher', $publisherId);

        // Add shortcuts
        if (!OA_Permission::isAccount(OA_ACCOUNT_TRAFFICKER)) {
            $this->_addShortcut(
                $GLOBALS['strAffiliateProperties'],
                'affiliate-edit.php?affiliateid=' . $publisherId,
                'iconAffiliate',
            );
        }




        $this->hideInactive = MAX_getStoredValue('hideinactive', ($aPref['ui_hide_inactive'] == true), null, true);
        $this->showHideInactive = true;

        $this->startLevel = 0;

        // Init nodes
        $this->aNodes = MAX_getStoredArray('nodes', []);
        $expand = MAX_getValue('expand', '');
        $collapse = MAX_getValue('collapse');

        // Adjust which nodes are opened closed...
        MAX_adjustNodes($this->aNodes, $expand, $collapse);

        $aParams = $this->coreParams;
        $aParams['publisher_id'] = $publisherId;

        // Limit by advertiser
        $advertiserId = (int) MAX_getValue('clientid', '');
        if (!empty($advertiserId)) {
            $aParams['advertiser_id'] = $advertiserId;
        }

        // Limit by advertiser
        $advertiserId = (int) MAX_getValue('clientid', '');
        if (!empty($advertiserId)) {
            $aParams['advertiser_id'] = $advertiserId;
        }

        $this->aEntitiesData = $this->getZones($aParams, $this->startLevel, $expand);

        // Summarise the values into a the totals array, & format
        $this->_summariseTotalsAndFormat($this->aEntitiesData);

        $this->showHideLevels = [];
        $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveZonesHidden']}";

        // Save prefs
        $this->aPagePrefs['startlevel'] = $this->startLevel;
        $this->aPagePrefs['nodes'] = implode(",", $this->aNodes);
        $this->aPagePrefs['hideinactive'] = $this->hideInactive;
    }
}
