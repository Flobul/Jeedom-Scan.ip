<?php

/**
 * Description of scan_ip
 *
 * @author Ynats
 */

require_once __DIR__ . "/../../../../plugins/scan_ip/core/class/scan_ip.require_once.php";

class scan_ip_json extends eqLogic {
    
    public static function searchByMac($_searchMac, $_mapping = NULL){ 
        log::add('scan_ip', 'debug', 'searchByMac :. ' . __('Lancement', __FILE__));
        
        if($_mapping == NULL){
            $_mapping = self::getJson(scan_ip::$_jsonMapping);
        }
        
        if(!empty($_mapping["byId"][$_searchMac]["ip_v4"])){
            $return["mac"] = $_mapping["byId"][$_searchMac]["mac"];
            $return["ip_v4"] = $_mapping["byId"][$_searchMac]["ip_v4"];
            $return["time"] = $_mapping["byId"][$_searchMac]["time"];
            $return["equipement"] = $_mapping["byId"][$_searchMac]["equipement"];
            return $return;
        } else {
            return NULL;
        }
    }
    
    public static function getMac($_macId){
        $equipement = self::getJson(scan_ip::$_jsonEquipement);
        $return = NULL;
        if(!empty($equipement[$_macId]["mac"])){
            $return = $equipement[$_macId]["mac"];
        }
        return $return;
    }
    
    public static function getCommentaires(){
        
        $arrayCommentMac = scan_ip_json::getJson(scan_ip::$_jsonCommentairesEquipement);
        $commentMac = NULL;
        
        if($arrayCommentMac != NULL){
            foreach ($arrayCommentMac as $comment) {
                $commentMac[$comment[0]["id"]] = $comment[1]["val"];
            }
        }
        
        return $commentMac;
    }
    
    public static function majNetworkCommentaires($_array){
        self::createAndMergeJsonFile(scan_ip::$_jsonCommentairesEquipement, $_array);
    }
    
    public static function recordInJson($_file, $_data) {
        log::add('scan_ip', 'debug', 'recordInJson :. ' . __('Lancement', __FILE__));
        
        self::prepareJsonFolder();
        self::createJsonFile($_file, $_data);

        log::add('scan_ip', 'debug', 'recordInJson :. ' . __('Enregistrement du Json : mapping.json', __FILE__));
    }
    
    public static function getJson($_file) {
        log::add('scan_ip', 'debug', 'getJson :. ' . __('Lancement', __FILE__));
        
        try {
            $return = @json_decode(@file_get_contents($_file.".json"),true);
        } catch (Exception $e) {
            $rerurn = NULL;
        }
        
        log::add('scan_ip', 'debug', 'getJson :. ' . $_file . ".json");
        return $return;
    }
    
    public static function prepareJsonFolder(){
        log::add('scan_ip', 'debug', 'prepareJsonFolder :. ' . __('Lancement', __FILE__));
        if (!is_dir(scan_ip::$_folderJson)) {
            log::add('scan_ip', 'debug', 'miseEnCacheJson :. ' . __('Création du dossier', __FILE__) . ' :' . scan_ip::$_folderJson);
            mkdir(scan_ip::$_folderJson, 0777);
        }
    }
    
    public static function createAndMergeJsonFile($_file, $_data){
        log::add('scan_ip', 'debug', 'createAndMergeJsonFile :. ' . __('Lancement', __FILE__));
        
        if(file_exists($_file.'.json')){
            $oldData = self::getJson($_file);
            $newData = $result = array_merge($oldData, $_data);
            self::createJsonFile($_file, $newData);
        } else {
            self::createJsonFile($_file, $_data);
        }
    }
    
    public static function createJsonFile($_file, $_data){
        log::add('scan_ip', 'debug', 'createJsonFile :. ' . __('Lancement', __FILE__));
        
        $fichier = fopen($_file.'.temp', 'w');
        fputs($fichier, json_encode($_data));
        fclose($fichier);

        @unlink($_file.'.json');
        rename($_file.'.temp', $_file.'.json');
        chmod($_file.'.json', 0777);
    }
    
    public static function printSelectOptionAdressMac($_selected = NULL){
        log::add('scan_ip', 'debug', 'printSelectOptionAdressMac :. ' . __('Lancement', __FILE__));
        $record = scan_ip_eqLogic::getAlleqLogics();
        $list = self::getJson(scan_ip::$_jsonEquipement);
        $print = "";
        foreach ($list as $id => $value) {
            if(empty($record[$id])){
                
                if(!empty($value["equipement"])){
                    $equipement = $value["equipement"];
                } else {
                    $equipement = "???";
                }
                
                $print .= '<option value="'. $id .'" data-mac="'. $value["mac"] .'" ';
                if($_selected != NULL AND $_selected == $id) { $print .= ' selected'; }
                $print .= '>' . $value["mac"] . ' | ' . $value["ip_v4"] . ' | '. $equipement .'</option>';
            }
        }  
        echo $print;
    }
    
    public static function showNoEquipements(){
        $return = NULL;
        
        $commentMac = self::getCommentaires();
        
        $ipsReseau = self::getJson(scan_ip::$_jsonMapping);
        $jsonEquipement = scan_ip_json::getJson(scan_ip::$_jsonEquipement);

        if (empty($ipsReseau)) {
            if(scan_ip_maj::checkPluginVersionAJour() == TRUE){ scan_ip_scan::syncScanIp(); }
            $ipsReseau = self::getJson(scan_ip::$_jsonMapping);
        }
        
        $savingMac = scan_ip_eqLogic::getAlleqLogics();
        $onLineTime = scan_ip::$_defaut_offline_time*60;
        $timeNow = time();
        
        foreach ($ipsReseau["sort"] as $device) {
            if (empty($savingMac[$device["mac_id"]]["name"])) {
                
                if(!empty($commentMac[$device["mac_id"]])){
                    $comment = $commentMac[$device["mac_id"]];
                } else {
                    $comment = NULL;
                }
                
                if($timeNow < ($device["time"] + $onLineTime)){
                    $online = 1;
                } else {
                    $online = 0;
                }
                
                $return[] = array(
                    "name" => $device["equipement"], 
                    "mac" => $device["mac"], 
                    "ip_v4" => $device["ip_v4"], 
                    "comment" => $comment, 
                    "time" => $device["time"],
                    "record" => $jsonEquipement[$device["mac_id"]]["record"],
                    "online" => $online
                );
            }
        }
        
        return $return;
    }
    
    public static function removeEquipementsTab($_array){ 
        
        $jsonEquipement = scan_ip_json::getJson(scan_ip::$_jsonEquipement);
        $jsonMapping = scan_ip_json::getJson(scan_ip::$_jsonMapping);
        
        foreach ($_array as $delete) {
            
            $mac_id = scan_ip_tools::getLastMac($delete[0]["mac"]);
            
            foreach ($jsonMapping["sort"] as $key => $sort) {
                if($sort["mac_id"] == $mac_id){
                    $del_sort = $key;
                    break;
                }
            }
            
            $del_byIpv4 = $jsonMapping["sort"][$del_sort]["ip_v4"];
            $del_byTime = $jsonMapping["sort"][$del_sort]["time"].$jsonEquipement[$mac_id]["record"];
            
            unset($jsonEquipement[$mac_id]);
            unset($jsonMapping["byId"][$mac_id]);
            unset($jsonMapping["sort"][$del_sort]);
            unset($jsonMapping["byIpv4"][$del_byIpv4]);
            unset($jsonMapping["byTime"][$del_byTime]);
            
        }
        
        self::recordInJson(scan_ip::$_jsonEquipement, $jsonEquipement);
        self::recordInJson(scan_ip::$_jsonMapping, $jsonMapping);
    }
    
}
