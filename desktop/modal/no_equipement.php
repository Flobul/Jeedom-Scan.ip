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

?>

<style>
    .scanTd{
        padding : 3px 0 !important;
    }
    .scanHender{
        cursor: pointer !important;
        width: 100%;
    }
</style>
<div>
    <div class="col-md-12" id="show_scan_ip_add" style="padding: 15px !important; display:none;">
        <div class="form-group">
            <a class="btn btn-success pull-right" id="add_scan_ip_equipement"><i class="fas fa-check-circle"></i> {{Ajouter les équipements sélectionnés}}</a>
            <a class="btn btn-danger pull-right" id="remove_scan_ip_equipement"><i class="fas fa-trash"></i> {{Supprimer les équipements sélectionnés}}</a>
        </div>
    </div>
    
    <div class="panel panel-primary" id="div_scan_ip_equipement_no">
        <div class="panel-body">
            <table class="table-bordered table-condensed" style="width: 100%; margin: -5px -5px 10px 5px;" id="scan_ip_no_equipement">
                <thead>
                    <tr style="background-color: grey !important; color: white !important;">
                        <th style="text-align: center; width:30px;"></th>
                        <th style="text-align: center; width:30px;"></th>
                        <th data-sort="string" style="width:375px;;" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Nom}}</span></th>
                        <th data-sort="string" style="width:375px;"><span class="scanHender"><b class="caret"></b> {{Commentaire}}</span></th>
                        <th data-sort="string" style="width:130px;" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Adresse MAC}}</span></th>
                        <th data-sort="int" style="width:110px;"><span class="scanHender"><b class="caret"></b> {{ip}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:170px;"><span class="scanHender"><b class="caret"></b> {{Enregistrement}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:170px;"><span class="scanHender"><b class="caret"></b> {{Date de mise à jour}}</span></th>
                    </tr>
                </thead>
                <tbody>
<?php
                    $list = 1;
                    $allNoEquipement = scan_ip_json::showNoEquipements();
                    
                    if(!empty($allNoEquipement)){
                    
                        foreach ($allNoEquipement as $equipement) { 
                            
                            if($equipement["online"] == 0){ 
                                $color = "red"; 
                            } else { 
                                $color = "#50aa50"; 
                            }
                            
                            echo '<tr>'
                                . '<td style="text-align:center; height:34px !important;"><input type="checkbox" onclick="is_checked_scan_ip()" id="checked_input_' . $list++  . '" data-mac="' . $equipement["mac"] . '" style="border: 1px solid var(--link-color) !important;" class="form-control add_element_scan_ip"></td>'
                                . '<td class="scanTd">' . scan_ip_tools::getCycle("15px", $color) . '</td>'
                                . '<td class="scanTd">' . $equipement["name"] . '</td>'
                                . '<td class="scanTd""><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($equipement["comment"]) . '</span>' . $equipement["comment"] . '</td>'
                                . '<td class="scanTd">' . $equipement["mac"] . '</td>'
                                . '<td class="scanTd"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($equipement["ip_v4"]) . '</span>' . $equipement["ip_v4"] . '</td>'
                                . '<td class="scanTd"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($equipement["record"]) . '</span>' . scan_ip_tools::printDate($equipement["record"]) . '</td>'
                                . '<td class="scanTd"><span style="display:none;">' . scan_ip_tools::getCleanForSortTable($equipement["time"]) . '</span>' . scan_ip_tools::printDate($equipement["time"]) . '</td>'
                                . '</tr>';
                        }
                    } else {
                        echo "<script>$('#div_scan_ip_equipement_no').hide();$('#div_alert_scan_ip').show();</script>";
                    }    
?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="div_alert_scan_ip" class="jqAlert alert-success" style="display:none;"><span class="displayError">{{Vous n'avez aucun équipement non enregistré sur le réseau.}}</span></div>
</div>

<script>
    $("#add_scan_ip_equipement").click(function() {
        addEquipement(<?php echo $list ?>);
    });
    
    $("#remove_scan_ip_equipement").click(function() {
        removeEquipement(<?php echo $list ?>);
    });
</script>  

<?php include_file('3rdparty', 'stupidtable.min', 'js', 'scan_ip'); ?>
<?php include_file('desktop', 'scan_ip_no_equipements', 'js', 'scan_ip'); ?>
