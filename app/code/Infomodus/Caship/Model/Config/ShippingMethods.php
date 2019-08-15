<?php
namespace Infomodus\Caship\Model\Config;
class ShippingMethods extends \Infomodus\Caship\Helper\Config
{
    public function getMethods($company)
    {
        if ($company == 'ups') {
            return [
                ['label' => 'UPS Next Day Air', 'value' => '01'],
                ['label' => 'UPS Second Day Air', 'value' => '02'],
                ['label' => 'UPS Ground', 'value' => '03'],
                ['label' => 'UPS Three-Day Select', 'value' => '12'],
                ['label' => 'UPS Next Day Air Saver', 'value' => '13'],
                ['label' => 'UPS Next Day Air Early A.M. SM', 'value' => '14'],
                ['label' => 'UPS Second Day Air A.M.', 'value' => '59'],
                ['label' => 'UPS Saver', 'value' => '65'],
                ['label' => 'UPS Worldwide ExpressSM', 'value' => '07'],
                ['label' => 'UPS Worldwide ExpeditedSM', 'value' => '08'],
                ['label' => 'UPS Standard', 'value' => '11'],
                ['label' => 'UPS Worldwide Express PlusSM', 'value' => '54'],
                ['label' => 'UPS Today StandardSM', 'value' => '82'],
                ['label' => 'UPS Today Dedicated CourrierSM', 'value' => '83'],
                ['label' => 'UPS Today Express', 'value' => '85'],
                ['label' => 'UPS Today Express Saver', 'value' => '86'],
            ];
        } else if ($company == 'dhl') {
            return [
                ['label' => __('Easy shop') . " " . __('(BTC, DOC)'), 'value' => '2'],
                ['label' => __('Sprintline') . " " . __('(SPL, DOC)'), 'value' => '5'],
                ['label' => __('Express easy') . " " . __('(XED, DOC)'), 'value' => '7'],
                ['label' => __('Europack') . " " . __('(EPA, DOC)'), 'value' => '9'],
                ['label' => __('Break bulk express') . " " . __('(BBX, DOC)'), 'value' => 'B'],
                ['label' => __('Medical express') . " " . __('(CMX, DOC)'), 'value' => 'C'],
                ['label' => __('Express worldwide') . " " . __('(DOX, DOC)'), 'value' => 'D'],
                ['label' => __('Express worldwide') . " " . __('(ECX, DOC)'), 'value' => 'U'],
                ['label' => __('Express 9:00') . " " . __('(TDK, DOC)'), 'value' => 'K'],
                ['label' => __('Express 10:30') . " " . __('(TDL, DOC)'), 'value' => 'L'],
                ['label' => __('Domestic economy select') . " " . __('(DES, DOC)'), 'value' => 'G'],
                ['label' => __('Economy select') . " " . __('(ESU, DOC)'), 'value' => 'W'],
                ['label' => __('Break bulk economy') . " " . __('(DOK, DOC)'), 'value' => 'I'],
                ['label' => __('Domestic express') . " " . __('(DOM, DOC)'), 'value' => 'N'],
                ['label' => __('Domestic express 10:30') . " " . __('(DOL, DOC)'), 'value' => 'O'],
                ['label' => __('Globalmail business') . " " . __('(GMB, DOC)'), 'value' => 'R'],
                ['label' => __('Same day') . " " . __('(SDX, DOC)'), 'value' => 'S'],
                ['label' => __('Express 12:00') . " " . __('(TDT, DOC)'), 'value' => 'T'],
                ['label' => __('Express envelope') . " " . __('(XPD, DOC)'), 'value' => 'X'],

                ['value' => '1', 'label' => __('Domestic express 12:00') . " " . __('(DOT, NON DOC)')],
                ['value' => '3', 'label' => __('Easy shop') . " " . __('(B2C, NON DOC)')],
                ['value' => '4', 'label' => __('Jetline') . " (" . __('(NFO, NON DOC)')],
                ['value' => '8', 'label' => __('Express easy') . " " . __('(XEP, NON DOC)')],
                ['value' => 'P', 'label' => __('Express worldwide') . " " . __('(WPX, NON DOC)')],
                ['value' => 'Q', 'label' => __('Medical express') . " " . __('(WMX, NON DOC)')],
                ['value' => 'E', 'label' => __('Express 9:00') . " " . __('(TDE, NON DOC)')],
                ['value' => 'F', 'label' => __('Freight worldwide') . " " . __('(FRT, NON DOC)')],
                ['value' => 'H', 'label' => __('Economy select') . " " . __('(ESI, NON DOC)')],
                ['value' => 'J', 'label' => __('Jumbo box') . " " . __('(JBX, NON DOC)')],
                ['value' => 'M', 'label' => __('Express 10:30') . " " . __('(TDM, NON DOC)')],
                ['value' => 'V', 'label' => __('Europack') . " " . __('(EPP, NON DOC)')],
                ['value' => 'Y', 'label' => __('Express 12:00') . " " . __('(TDY, NON DOC)')],
            ];
        } else if ($company == 'fedex') {
            return [
                ['label' => __('Europe First Priority'), 'value' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY'],
                ['label' => __('1 Day Freight'), 'value' => 'FEDEX_1_DAY_FREIGHT'],
                ['label' => __('2 Day Freight'), 'value' => 'FEDEX_2_DAY_FREIGHT'],
                ['label' => __('2 Day'), 'value' => 'FEDEX_2_DAY'],
                ['label' => __('2 Day AM'), 'value' => 'FEDEX_2_DAY_AM'],
                ['label' => __('3 Day Freight'), 'value' => 'FEDEX_3_DAY_FREIGHT'],
                ['label' => __('Express Saver'), 'value' => 'FEDEX_EXPRESS_SAVER'],
                ['label' => __('Ground'), 'value' => 'FEDEX_GROUND'],
                ['label' => __('First Overnight'), 'value' => 'FIRST_OVERNIGHT'],
                ['label' => __('Home Delivery'), 'value' => 'GROUND_HOME_DELIVERY'],
                ['label' => __('International Economy'), 'value' => 'INTERNATIONAL_ECONOMY'],
                ['label' => __('Intl Economy Freight'), 'value' => 'INTERNATIONAL_ECONOMY_FREIGHT'],
                ['label' => __('International First'), 'value' => 'INTERNATIONAL_FIRST'],
                ['label' => __('International Ground'), 'value' => 'INTERNATIONAL_GROUND'],
                ['label' => __('International Priority'), 'value' => 'INTERNATIONAL_PRIORITY'],
                ['label' => __('Intl Priority Freight'), 'value' => 'INTERNATIONAL_PRIORITY_FREIGHT'],
                ['label' => __('Priority Overnight'), 'value' => 'PRIORITY_OVERNIGHT'],
                ['label' => __('Smart Post'), 'value' => 'SMART_POST'],
                ['label' => __('Standard Overnight'), 'value' => 'STANDARD_OVERNIGHT'],
                ['label' => __('Freight'), 'value' => 'FEDEX_FREIGHT'],
                ['label' => __('National Freight'), 'value' => 'FEDEX_NATIONAL_FREIGHT'],
                /* for intra UK only*/
                ['label' => __('Distance Deferred (for intra-UK only)'), 'value' => 'FEDEX_DISTANCE_DEFERRED'],
                ['label' => __('Next Day Afternoon (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_AFTERNOON'],
                ['label' => __('Next Day Early Morning (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_EARLY_MORNING'],
                ['label' => __('Next Day End of Day (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_END_OF_DAY'],
                ['label' => __('Next Day Freight (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_FREIGHT'],
                ['label' => __('Next Day Mid Morning (for intra-UK only)'), 'value' => 'FEDEX_NEXT_DAY_MID_MORNING'],
            ];
        }
        
        return [];
    }
}