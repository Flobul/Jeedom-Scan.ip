<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

require_once dirname(__FILE__) . "/../../../../plugins/scan_ip/core/class/scan_ip.require_once.php";

$ipsReseau = scan_ip_json::getJson(scan_ip::$_jsonMapping);

if (empty($ipsReseau)) {
    if(scan_ip_maj::checkPluginVersionAJour() == TRUE){ scan_ip_scan::syncScanIp(); }
    $ipsReseau = scan_ip_json::getJson(scan_ip::$_jsonMapping);
}

$savingMac = scan_ip_eqLogic::getAlleqLogics();
$commentMac = scan_ip_json::getCommentaires();

$eqLogic = scan_ip_eqLogic::searcheqLogicByType("network");
$orderBy = scan_ip_widget_network::getConfigOrder($eqLogic);

?>

<style>
    .scanTd{
        padding : 3px 0 3px 15px !important;
    }
    .scanHender{
        cursor: pointer !important;
        width: 100%;
    }
    .macPresentActif{
        color: green;
    }
    .macPresentInactif{
        color: #FF4500;
    }
    .macAbsent{
        color: grey;
    }
    .spanScanIp{
        display: block;
        width: 78x !important;
        padding : 2px 5px;
        color : white;
        text-align: center;
    }
    .EnableScanIp{
        color: green;
    }
    .DisableScanIp{
        color: #FF4500;
    }
    .NoneScanIp{
        color: grey;
    }
    
</style>

<div class="col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{Votre Jeedom}}</h3>
        </div>
        <div class="panel-body">
        <?php if($ipsReseau["jeedom"]["name"] != "") { ?>
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Nom}} : </label>
                <div><?php echo $ipsReseau["jeedom"]["name"] ?></div>
            </div>
        <?php } ?>
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{ip}} : </label>
                <div><?php echo $ipsReseau["jeedom"]["ip_v4"] ?></div> 
            </div>
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Adresse MAC}} : </label>
                <div><?php echo $ipsReseau["jeedom"]["mac"]?></div>
            </div>
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Installation}} : </label>
                <div><?php echo scan_ip_tools::printDate($ipsReseau["jeedom"]["record"]) ?></div> 
            </div>
            <?php if(gethostbyaddr($ipsReseau["jeedom"]["ip_v4"]) != $ipsReseau["jeedom"]["ip_v4"]) { ?>            
                <div>
                    <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Host Name}} : </label>
                    <div><?php echo gethostbyaddr($ipsReseau["jeedom"]["ip_v4"]) ?></div>
                </div>
            <?php } ?> 
        </div>
        <br />
    </div>
</div>

<div class="col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{Votre routeur}}</h3>
        </div>
        <div class="panel-body">
            <?php if(!empty($ipsReseau["route"]["equipement"])) { ?>            
                <div>
                    <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Nom}} : </label>
                    <div><?php echo $ipsReseau["route"]["equipement"] ?></div>
                </div>
            <?php } ?> 
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{ip}} : </label>
                <div><?php echo $ipsReseau["route"]["ip_v4"] ?></div>
            </div>
            <div>
                <label style="width:130px; text-align: right; margin: 0px 5px 0 0; float:left;">{{Adresse MAC}} : </label>
                <div><?php echo $ipsReseau["route"]["mac"] ?></div>
            </div>
        </div>
        <br />
    </div>
</div>

<div class="col-md-12">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">{{Les plages ip et adresses MAC du réseau}} (<?php echo $ipsReseau["infos"]["date"] ?>)
            <a id="btSaveCommentaires" class="btn btn-success btn-xs pull-right" style="top: -2px !important; right: -6px !important;"><i class="far fa-check-circle icon-white"></i> {{Sauvegarder les commentaires}}</a>
            </h3>
        </div>
        <div class="panel-body">
            <table class="table-bordered table-condensed" style="width: 100%; margin: -5px -5px 10px 5px;" id="scan_ip_network">
                <thead>
                    <tr style="background-color: grey !important; color: white !important;">
                        <th data-sort="string" class="scanTd" style="text-align: center; width:30px;" class="scanTd"><span class="scanHender"><b class="caret"></b></span></th>
                        <th data-sort="int" style="text-align: center; width:30px;" class="scanTd"><span class="scanHender"><b class="caret"></b></span></th>
                        <th data-sort="string" style="width:130px;" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Adresse MAC}}</span></th>
                        <th data-sort="int" class="scanTd" style="width:110px;"><span class="scanHender"><b class="caret"></b> {{ip}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:375px;"><span class="scanHender"><b class="caret"></b> {{Nom}}</span></th>
                        <th data-sort="string" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Commentaire}}</span></th>
                        <th data-sort="int" class="scanTd" style="width:170px;"><span class="scanHender"><b class="caret"></b> {{Date de mise à jour}}</span></th>
                    </tr>
                </thead>
                <tbody>
<?php         
                    $list = 1;
                    foreach ($ipsReseau["sort"] as $device) {
                        
                        $element = scan_ip_widget_network::getElementVueNetwork($device, $savingMac, $commentMac);

                        echo '<tr>'
                                . '<td class="scanTd" title="' . $element["titleOnLine"] .'"><span style="display:none;">' . $element["lineSortOnline"] . '</span>' . scan_ip_tools::getCycle("15px", $element["colorOnLine"]) . '</td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '" style="style="text-align:center !important;" title="' . $element["titleEquipement"] .'"><span style="display:none;">' . $element["lineSortEquipement"] . '</span><span class="' . $element["classSuivi"] . '">' . $element["textPresent"] . '</span></td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '">' . $device["mac"] . '</td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($device["ip_v4"], "int") . '</span>' . $device["ip_v4"] . '</td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '" style="text-overflow: ellipsis;"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($element["name"], "string") . '</span>' . $element["name"] . '</td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($element["printComment"], "string") . '</span><input type="text" id="input_' . $list++ . '" data-id="' . $device["mac_id"] . '" value="' . $element["printComment"] . '" class="form-control input-sm" style="width:100%;"></td>'
                                . '<td class="scanTd ' . $element["classPresent"] . '"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($device["time"], "date") . '</span>' .  scan_ip_tools::printDate($device["time"]) . '</td>'
                                . '</tr>';
                    }
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    
    $("#btSaveCommentaires").click(function() {
        btSaveCommentaires(<?php echo $list ?>);
    });
    
    $(document).ready(function ($) {
    var $table = $("#scan_ip_network").stupidtable(); 
    var $th_to_sort = $table.find("thead th").eq("<?php echo scan_ip_widget_network::getOrderBy($orderBy) ?>"); 
    $th_to_sort.stupidsort();

});

</script>

<?php include_file('desktop', 'scan_ip_network', 'js', 'scan_ip'); ?>
<?php include_file('3rdparty', 'stupidtable.min', 'js', 'scan_ip'); ?>
