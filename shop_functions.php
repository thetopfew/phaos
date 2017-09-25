<?php
require_once "class_character.php";

// Execute buy action part of request, used in shop_include.php
function do_buy(&$character, $shop_basics) {

    $ids = @$_REQUEST['buy_id'];
    $types = @$_REQUEST['buy_type'];
    $numbers = @$_REQUEST['buy_number'];

    $bought = 0;
    $sorrys = 0;
    if ($ids && is_array($ids) && $types && is_array($types)) {
        foreach($ids as $key=>$id) {
            $number = @$numbers[$key];
            if (!$number) {
                $number = 1;
            }
            $item = array('id'=>$id,'type'=>$types[$key],'number'=>$number);
            $info = fetch_item_additional_info($item, $character);
        	if ($info['buy_price'] > 0 && $character->pay($info['buy_price'])) {
                $item['number'] = item_pickup($shop_basics['item_location_id'],$item);
                $bought += $character->pickup_item($item);
        	} else {
                $sorrys += $number;
            }
        }
    }
}

// Used in Blacksmith.php
function insert_shop_refill($shop_id, $item_type, $item_value_min, $item_value_growth, $item_value_growth_probability,$item_count_min) {
	global $db;
	
    $query = "REPLACE INTO phaos_shop_refill (shop_id, item_type, item_value_min, item_value_growth, item_value_growth_probability, item_count_min)
			  VALUES ('$shop_id', '$item_type', '$item_value_min', '$item_value_growth', '$item_value_growth_probability','$item_count_min')";

    $req = $db->query($query);
    if (!$req) { showError(__FILE__,__LINE__,__FUNCTION__,$query); exit;}
}