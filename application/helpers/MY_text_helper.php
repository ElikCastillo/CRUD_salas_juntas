<?php
function character_limiter($str, $n = 500, $end_char = '&#8230;')
{
	if (strlen($str) < $n)
	{
		return $str;
	}

	if (function_exists('mb_substr'))
	{
		return mb_substr($str,0, $n).$end_char;
	}
	
	return substr($str,0, $n).$end_char;
}

function replace_newline($string) 
{
	return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
}

function number_pad($number,$n) 
{
	return str_pad($number,$n,"0",STR_PAD_LEFT);
	//return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

function H($input)
{
	return htmlentities($input, ENT_QUOTES, 'UTF-8', false);
}

//From http://stackoverflow.com/a/26537463/627473
function escape_full_text_boolean_search($search)
{
	return preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $search);
}

function does_contain_only_digits($string)
{
	return (preg_match('/^[0-9]+$/', $string));
}

function boolean_as_string($val)
{
	if ($val)
	{
		return lang('common_yes');		
	}
	return lang('common_no');
}

function active_or_inactive($val)
{
	if ($val)
	{
		return lang('common_active');		
	}
	return lang('common_inactive');
}

function commission_percent_type_formater($val)
{
	if ($val == 'selling_price')
	{
		return lang('common_unit_price');
	}
	elseif($val == 'profit')
	{
		return lang('common_profit');		
	}
	
	return lang('common_not_set');
}

function item_low_quantity_data_function($item)
{
	$CI =& get_instance();
	$data = array();
	
	$data['item_id'] = $item->item_id;
	$data['low_inventory_class']='';
	if($CI->config->item('highlight_low_inventory_items_in_items_module') && $item->quantity !== NULL && ($item->quantity<=0 || $item->quantity <= $item->reorder_level))
	{
		$data['low_inventory_class'] = "text-danger";
	}
	
	return $data;
}

function item_name_formatter($val,$data)
{	
	$return = '';
	$link = '<a class="'.$data['low_inventory_class'].'" href="'.site_url('home/view_item_modal').'/'.$data['item_id'].'" data-toggle="modal" data-target="#myModal">'.H($val).'</a>';
	$return.=$link;
	
	if ($data['variation_count'])
	{
		$return.='&nbsp;<span class="ion-ios-toggle-outline"></span>';
	}
	
	return $return;
	//return '<a class="'.$data['low_inventory_class'].'" href="'.site_url('home/view_item_modal').'/'.$data['item_id'].'" data-toggle="modal" data-target="#myModal">'.H($val).'</a>';
}

function get_full_category_path($val)
{
	$CI =& get_instance();
	$CI->load->model('Category');
	return $CI->Category->get_full_path($val);
}

function item_inventory_data($item)
{
	$CI =& get_instance();
	$data = array();
	
	$data['item_id'] = $item->item_id;
	$data['is_service'] = $item->is_service;

	return $data;
}

function item_inventory_formatter($val,$data)
{
	if(empty($val))
		//$val = lang('common_inv');
	
	if ($data['is_service'])
	{
		return '';
	}
	return '<a href="'.site_url('items/inventory').'/'.$data['item_id'].'">'.H($val).'</a>';
}

function item_quantity_format($val,$data)
{
	$val = to_quantity($val);
	return '<span class="'.$data['low_inventory_class'].'">'.$val.'</span>';
}

function item_quantity_porcent($val)
{
	$val = (int)$val ? (int)$val : rtrim($val, '0');
	if($val) return $val.'%';
	return lang('common_not_set');
}

function tel($number)
{
	if ($number)
	{
		return '<a href="tel:'.$number.'">'.H($number).'</a>';
	}
	
	return '';
}

function item_quantity_data_function($item)
{
	$CI =& get_instance();
	$data = array();
	
	$data['item_id'] = $item->item_id;
	$data['low_inventory_class']='';
	$data['is_service'] = $item->is_service;
	$data['variation_count'] = $item->variation_count;
	if($CI->config->item('highlight_low_inventory_items_in_items_module') && $item->quantity !== NULL && ($item->quantity<=0 || $item->quantity <= $item->reorder_level))
	{
		$data['low_inventory_class'] = "text-danger";
	}
	
	return $data;
}

function to_quantity_variation($val,$data)
{
	$item_id = $data['item_id'];
	return anchor("items/variations/$item_id?redirect=items&quick_edit=1", to_quantity($val));
}
function item_id_data_function($item)
{
	$CI =& get_instance();
	$data = array();
	
	$data['item_id'] = $item->item_id;
	return $data;
}
function recortar_cadena($string, $max_longitud = 17) {
    if (strlen($string) > $max_longitud) {
        $string = trim(substr($string, 0, $max_longitud)).'...';
    }
    return $string;
}
?>