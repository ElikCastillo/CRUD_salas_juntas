<?php
/*
Gets the html table to manage people.
*/
function get_people_manage_table($people, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$controller_name = strtolower(get_class($CI));
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';

	if ($controller_name == 'customers') {
		$columns_to_display = $CI->Employee->get_customer_columns_to_display();
		$add_customer_record_permission = $CI->Employee->has_module_action_permission('customers', 'add_customer_record', $CI->Employee->get_logged_in_employee_info()->person_id);
		$access_record = $CI->config->item('con_agenda');
	} elseif ($controller_name == 'suppliers') {
		$CI->load->model('Supplier');
		$columns_to_display = $CI->Employee->get_supplier_columns_to_display();
	} elseif ($controller_name == 'employees' or $controller_name == 'paysheets') {
		$CI->load->model('Employee');
		$columns_to_display = $CI->Employee->get_employee_columns_to_display();
	} elseif ($controller_name == 'banks') {
		$columns_to_display = $CI->Employee->get_bank_columns_to_display();
	}

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}

	if ($controller_name == 'employees' && $deleted == 0) {
		$headers[] = array('label' => lang('common_message'), 'sort_column' => '');
	}

	if ($controller_name == 'paysheets' && $deleted == 0) {
		$headers[] = array('label' => lang('common_clone'), 'sort_column' => '');
		$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
	}

	if ($deleted == 0) $headers[] = array('label' => lang('common_acciones'), 'sort_column' => '');

	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');

	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_people_manage_table_data_rows($people, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the people.
*/
function get_people_manage_table_data_rows($people, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$table_data_rows = '';
	$controller_name = strtolower(get_class($CI));

	if ($people) {
		if ($controller_name == 'customers') {
			$CI->load->model('Customer');
		} elseif ($controller_name == 'suppliers') {
			$CI->load->model('Supplier');
		} elseif ($controller_name == 'employees' or $controller_name == 'paysheets') {
			$CI->load->model('Employee');
		}
		foreach ($people->result() as $person) {
			$campos_extra = $CI->Employee->valor_campos_extra($controller_name, $person->person_id);

			foreach ($campos_extra as $key => $value) {
				$nombre_campo = $value['nombre_campo'];
				$valor_campo = $value['valor_campo'];
				$person->$nombre_campo = $valor_campo;
			}
			$table_data_rows .= get_person_data_row($person, $controller, $deleted);
		}

		if ($people->num_rows() == 0 && $controller_name != 'employees' && !$deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span>&nbsp;&nbsp;<a class='btn btn-primary' href='" . site_url($controller_name . '/excel_import') . "'>" . lang($controller_name . '_import_' . $controller_name) . "</a></span></tr>";
		} elseif ($people->num_rows() == 0 && $controller_name == 'employees') {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
		} elseif ($people->num_rows() == 0 && $deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
		}
	} else {
		$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
	}

	return $table_data_rows;
}

function get_person_data_row($person, $controller, $deleted = 0)
{
	static $has_send_message_permission;
	$CI = &get_instance();
	if (!$has_send_message_permission) {
		$has_send_message_permission = $CI->Employee->has_module_action_permission('messages', 'send_message', $CI->Employee->get_logged_in_employee_info()->person_id);
	}

	$CI = &get_instance();
	$CI->load->helper('people');
	$controller_name = strtolower(get_class($CI));
	$avatar_url = $person->image_id ?  site_url('app_files/view_optimized/' . $person->image_id) : base_url('assets/assets/images/avatar-default.jpg');

	$table_data_row = '<tr>';
	if ($controller_name == 'suppliers') {
		$table_data_row = '<tr ondblclick="window.location.href = \'' . site_url('suppliers/details') . '/' . $person->person_id . '/2\'">';
	} else {
		$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url($controller_name . '/details') . '/' . $person->person_id . '/2\'">';
	}

	//	$table_data_row .= "<td><input type='checkbox' id='${controller_name}_$person->person_id' value='" . $person->person_id . "'/><label for='${controller_name}_$person->person_id'><span></span></label></td>";

	$table_data_row.="<td><input type='checkbox' class='form-check-input' id='${controllername}$person->person_id' value='".$person->person_id."'/><label for='${controllername}$person->person_id'><span></span></label></td>";
	
	if ($controller_name == 'customers') {
		$displayable_columns = $CI->Employee->get_customer_columns_to_display();
	} elseif ($controller_name == 'suppliers') {
		$CI->load->model('Supplier');
		$displayable_columns = $CI->Employee->get_supplier_columns_to_display();
	} elseif ($controller_name == 'employees' or $controller_name == 'paysheets') {
		$CI->load->model('Employee');
		$displayable_columns = $CI->Employee->get_employee_columns_to_display();
	} elseif ($controller_name == 'banks') {
		$CI->load->model('Bank');
		$displayable_columns = $CI->Employee->get_bank_columns_to_display();
	}



	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	$balance = 0;
	$person_id = 0;
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $person->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($person);
			}

			$format_function = $column_values['format_function'];
			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}
		if ($column_id == 'balance') {
			$balance = $val;
			$format_function = 'to_currency';
			$val = $format_function($val);
		}
		if ($column_id == 'person_id') {
			$person_id = $val;
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}

		$table_data_row .= '<td>' . $val . '</td>';
		//Unset for next round of the loop
		unset($data);
	}

	if ($controller_name == 'employees' && $deleted == 0) {
		//$table_data_row .= '<td class="rightmost actions"><div class="btn-group table_buttons"><a href="' . site_url($controller_name . "/clone_employee/$person->person_id") . '" role="button" class="btn btn-more edit_action" title="' . lang('common_clone') . '">' . lang('common_clone') . '</a> </div></td>';

		if ($has_send_message_permission) {
			$table_data_row .= '<td class="rightmost actions"><div class="btn-group table_buttons"><a href="' . site_url('messages/send_invidual_message/') . '/' . $person->person_id . '" role="button"  data-toggle="modal" data-target="#myModal" class="btn btn-more edit_action  manage-employees-message" title="' . lang('common_customer_record') . '"><i class="ion-email"></i></a> </div></td>';
		}
	}

	/*if($controller_name == 'paysheets' && $deleted == 0){
		$table_data_row.='<td class="rightmost">'.anchor($controller_name."/clone_employee/$person->person_id	", lang('common_clone'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';

		if ($has_send_message_permission)
		{
			$table_data_row.='<td class="text-center"> <a href="'.site_url('messages/send_invidual_message/').'/'.$person->person_id.'" class="1	 manage-employees-message"  data-toggle="modal" data-target="#myModal" ><i class="ion-email"></i></a> </td>';
		}
	}*/

	// expediente de cliente
	$add_customer_record_permission = $CI->Employee->has_module_action_permission('customers', 'add_customer_record', $CI->Employee->get_logged_in_employee_info()->person_id);
	$access_record = $CI->config->item('con_agenda');
	if ($deleted == 0) {
		$table_data_row .= '<td class="actions"><div class="piluku-dropdown dropdown btn-group table_buttons upordown">
		<a href="' . site_url($controller_name . "/view/$person->person_id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
		<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
			<i class="ion-more"></i>
		</button> <ul class="dropdown-menu dropdown-menu-left dropdown_inventory_actions" role="menu">';
		if ($controller_name == 'suppliers') {
			$table_data_row .= '<li>' . anchor($controller_name . "/receivings_list/$person->person_id", '<i class="ion-cash"></i> ' .  lang('common_payments'), array('class' => '', 'title' => lang('common_payments'))) . '</li>';
			$table_data_row .= '<li>' . anchor($controller_name . "/details/$person->person_id", '<i class="ti-info-alt"></i> ' . lang('common_details'), array('class' => ' ', 'title' => lang('common_details'))) . '</li>';
			$table_data_row .= '<li>' . anchor($controller_name . "/new_receiving/$person->person_id", '<i class="ti-shopping-cart-full"></i> ' . lang('home_receivings_start_new_receiving'), array('class' => ' ', 'title' => lang('home_receivings_start_new_receiving'))) . '</li>';
		}
		if ($controller_name == 'customers' && $add_customer_record_permission && $access_record != '0') {
			$table_data_row .= '<li>' . anchor($controller_name . "/view_record/$person->person_id",'<i class="ti-file"></i> ' . lang('common_customer_record'), array('class' => ' ', 'title' => lang('common_customer_record'))) . '</li>';
			$table_data_row .= '<li>' . anchor($controller_name . "/details/$person->person_id", '<i class="ti-info-alt"></i> ' . lang('common_details'), array('class' => ' ', 'title' => lang('common_details'))) . '</li>';
			$table_data_row .= '<li>' . anchor($controller_name . "/new_sale/$person->person_id", '<i class="ti-shopping-cart-full"></i> ' . lang('sales_new_sale'), array('class' => ' ', 'title' => lang('sales_new_sale'))) . '</li>';
			if ($balance > 0) {
				$table_data_row .= '<li>' . anchor($controller_name . "/pay_now/$person_id", '<i class="ion-cash"></i> ' . lang('common_abono'), array('class' => ' ', 'title' => lang('common_abono'))) . '</li>';
			}
			if ($balance < 0) {
				$table_data_row .= '<li>' . anchor($controller_name . "/pay_now/$person_id", '<i class="ion-cash"></i> ' . lang('common_pay'), array('class' => ' ', 'title' => lang('common_pay'))) . '</li>';
			}
		}
		if ($controller_name == 'employees') {
			$table_data_row .= '<li>' . anchor($controller_name . "/details/$person->person_id", '<i class="ti-info-alt"></i> ' . lang('common_details'), array('class' => ' ', 'title' => lang('common_details'))) . '</li>';
		$table_data_row .= '<li>' . anchor($controller_name . "/clone_employee/$person->person_id", '<i class="ion-clone"></i> ' .  lang('common_clone'), array('class' => '', 'title' => lang('common_clone'))) . '</li>';
		}
		$table_data_row.='</ul></div></td>';
		
	}

	if ($avatar_url) {
		$table_data_row .= "<td><a href='$avatar_url' class='rollover'><img src='" . $avatar_url . "' alt='" . H($person->full_name) . "' class='img-polaroid' width='45' /></a></td>";
	}

	$table_data_row .= '</tr>';

	return $table_data_row;
}

function get_bank_manage_table($banks, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$controller_name = strtolower(get_class($CI));
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';

	$columns_to_display = $CI->Employee->get_bank_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}

	if ($deleted == 0)
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	$headers[] = array('label' => lang('banks_transac'), 'sort_column' => '');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');

	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_bank_manage_table_data_rows($banks, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}


function get_account_manage_table($accounts, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$controller_name = strtolower(get_class($CI));
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';

	$columns_to_display = $CI->Employee->get_account_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}

	if ($deleted == 0)
		$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');

	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_acoount_manage_table_data_rows($accounts, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}


function get_acoount_manage_table_data_rows($accounts, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$table_data_rows = '';
	$controller_name = strtolower(get_class($CI));

	if ($accounts) {
		foreach ($accounts->result() as $account) {
			$table_data_rows .= get_account_data_row($account, $controller, $deleted);
		}

		if ($accounts->num_rows() == 0 && !$deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span>&nbsp;&nbsp;<a class='btn btn-primary' href='" . site_url($controller_name . '/excel_import') . "'>" . lang($controller_name . '_import_' . $controller_name) . "</a></span></tr>";
		} elseif ($accounts->num_rows() == 0 && $deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
		}
	}

	return $table_data_rows;
}


function get_account_data_row($account, $controller, $deleted = 0)
{
	static $has_send_message_permission;
	$CI = &get_instance();
	if (!$has_send_message_permission) {
		$has_send_message_permission = $CI->Employee->has_module_action_permission('messages', 'send_message', $CI->Employee->get_logged_in_employee_info()->person_id);
	}

	$CI = &get_instance();

	$controller_name = strtolower(get_class($CI));

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('accounts/view') . '/' . $account->account_number . '/2\'">';

	$table_data_row .= "<td><input type='checkbox' id='${controller_name}_$account->account_number' value='" . $account->account_number . "'/><label for='${controller_name}_$account->account_number'><span></span></label></td>";

	$CI->load->model('Account');
	$displayable_columns = $CI->Employee->get_account_columns_to_display();


	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $account->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($account);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}

		$table_data_row .= '<td>' . $val . '</td>';
		//Unset for next round of the loop
		//unset($data);
	}

	if ($deleted == 0) {
		//$table_data_row.='<td>'.anchor($controller_name."/view/$bank->id_banco/2", lang('common_edit'),array('class'=>'update-person','title'=>lang($controller_name.'_update'))).'</td>';

		$table_data_row .= '<td class="actions">' .
			'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
							  <a href="' . site_url($controller_name . "/view/$account->account_number/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>';

		//$table_data_row.='<td>'.anchor($controller_name."/transaction/$bank->id_banco/0", lang('banks_transac'),array('class'=>'  update-person','title'=>lang($controller_name.'_transac'))).'</td>';



		$table_data_row .=	'</div>'
			. '</td>';
	}



	$table_data_row .= '</tr>';

	return $table_data_row;
}

function get_bank_manage_table_data_rows($banks, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$table_data_rows = '';
	$controller_name = strtolower(get_class($CI));

	if ($banks) {
		foreach ($banks->result() as $bank) {
			$table_data_rows .= get_bank_data_row($bank, $controller, $deleted);
		}

		if ($banks->num_rows() == 0 && !$deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span>&nbsp;&nbsp;<a class='btn btn-primary' href='" . site_url($controller_name . '/excel_import') . "'>" . lang($controller_name . '_import_' . $controller_name) . "</a></span></tr>";
		} elseif ($banks->num_rows() == 0 && $deleted) {
			$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
		}
	}

	return $table_data_rows;
}


function get_bank_data_row($bank, $controller, $deleted = 0)
{
	static $has_send_message_permission;
	$CI = &get_instance();
	if (!$has_send_message_permission) {
		$has_send_message_permission = $CI->Employee->has_module_action_permission('messages', 'send_message', $CI->Employee->get_logged_in_employee_info()->person_id);
	}

	$CI = &get_instance();

	$controller_name = strtolower(get_class($CI));

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('banks/view') . '/' . $bank->id_banco . '/2\'">';

	$table_data_row .= "<td><input type='checkbox' id='${controller_name}_$bank->id_banco' value='" . $bank->id_banco . "'/><label for='${controller_name}_$bank->id_banco'><span></span></label></td>";

	$CI->load->model('Bank');
	$displayable_columns = $CI->Employee->get_bank_columns_to_display();


	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $bank->{$column_id};

		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($bank);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}

		$table_data_row .= '<td>' . $val . '</td>';
		//Unset for next round of the loop
		//unset($data);
	}

	if ($deleted == 0) {
		//$table_data_row.='<td>'.anchor($controller_name."/view/$bank->id_banco/2", lang('common_edit'),array('class'=>'update-person','title'=>lang($controller_name.'_update'))).'</td>';

		$table_data_row .= '<td class="actions">' .
			'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
							  <a href="' . site_url($controller_name . "/view/$bank->id_banco/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>';

		//$table_data_row.='<td>'.anchor($controller_name."/transaction/$bank->id_banco/0", lang('banks_transac'),array('class'=>'  update-person','title'=>lang($controller_name.'_transac'))).'</td>';

		$table_data_row .= '<td class="actions">' .
			'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
							  <a href="' . site_url($controller_name . "/transaction/$bank->id_banco/1/0") . '" role="button" class="btn btn-more edit_action">' . lang($controller_name . '_transac') . '</a>';

		$table_data_row .=	'</div>'
			. '</td>';
	}



	$table_data_row .= '</tr>';

	return $table_data_row;
}

/*
Gets the html table to manage items.
*/
function get_items_manage_table($items, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_item_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}
	if ($deleted == 0) {
		//$headers[] = array('label' =>('common_actions'), 'sort_column' => '');
		//$headers[] = array('label' => lang('common_clone'), 'sort_column' => '');
		$headers[] = array('label' => lang('common_acciones'), 'sort_column' => '');
	}
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');

	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_items_manage_table_data_rows($items, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_items_manage_table_data_rows($items, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($items->result() as $item) {
		$table_data_rows .= get_item_data_row($item, $controller, $deleted);
	}

	if ($items->num_rows() == 0 && !$deleted) {
		$table_data_rows .= "<tr>
			<td colspan='13'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('items_no_items_to_display') . "</span>&nbsp;&nbsp;<a class='btn btn-primary' href='" . site_url('items/excel_import') . "'>" . lang('items_import_items') . "</a></span></td>
		</tr>";
	} elseif ($items->num_rows() == 0) {
		$table_data_rows .= "<tr><td colspan='10'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('common_no_persons_to_display') . "</span></span></tr>";
	}

	return $table_data_rows;
}

function get_item_data_row($item, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$low_inventory_class = "";

	$reorder_level = $item->location_reorder_level ? $item->location_reorder_level : $item->reorder_level;

	$controller_name = strtolower(get_class($CI));
	$avatar_url = $item->image_id ?  site_url('app_files/view_optimized/' . $item->image_id) : base_url('assets/assets/images/avatar-default.jpg');

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('items/view') . '/' . $item->item_id . '/2\'">';
	$table_data_row .= "<td><input type='checkbox' id='item_$item->item_id' value='" . $item->item_id . "'/><label for='item_$item->item_id'><span></span></label></td>";

	$displayable_columns = $CI->Employee->get_item_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $item->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($item);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}

		$table_data_row .= '<td>' . $val . '</td>';
		//Unset for next round of the loop
		unset($data);
	}
	if ($deleted == 0) {

		$table_data_row .= '<td class="actions">' .
			'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
						  <a href="' . site_url($controller_name . "/view/$item->item_id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
							<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
								<i class="ion-more"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-left dropdown_inventory_actions" role="menu">';
		if ($item->is_service == '0') {
			$table_data_row .= '<li>' . anchor($controller_name . "/inventory/$item->item_id", '<i class="ion-android-clipboard"></i> ' .  lang('common_inv'), array('class' => '', 'title' => lang('common_inv'))) . '</li>';
		}
		$table_data_row .= '<li>' . anchor($controller_name . "/barcodes/$item->item_id", '<i class="ion-android-print"></i> ' .  lang('common_print') . ' ' . lang('common_barcodes'), array('class' => '', 'title' => lang('common_barcodes'))) . '</li>';
		$table_data_row .= '<li>' . anchor($controller_name . "/clone_item/$item->item_id	", '<i class="ion-ios-browsers-outline"></i> ' . lang('common_clone') . ' ' . lang('common_item'), array('class' => ' ', 'title' => lang('common_clone'))) . '</li>';

		$table_data_row .= '</ul>
						</div>'
			. '</td>';

		//$table_data_row.='<td class="rightmost">'.anchor($controller_name."/clone_item/$item->item_id	", lang('common_clone'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';
		//$table_data_row.='<td class="rightmost">'.anchor($controller_name."/view/$item->item_id/2	", lang('common_edit'),array('class'=>'','title'=>lang($controller_name.'_update'), 'onclick'=> 'edit_item();')).'</td>';
	}

	if ($avatar_url) {
		$table_data_row .= "<td><a href='$avatar_url' class='rollover'><img src='" . $avatar_url . "' alt='" . H($item->name) . "' class='img-polaroid' width='45' /></a></td>";
	}

	$table_data_row .= '</tr>';
	return $table_data_row;
}


/*
Gets the html table to manage items.
*/
function get_locations_manage_table($locations, $controller)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_locations_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}
	$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');



	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_locations_manage_table_data_rows($locations, $controller);
	$table .= '</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the items.
*/
function get_locations_manage_table_data_rows($locations, $controller)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($locations->result() as $location) {
		$table_data_rows .= get_location_data_row($location, $controller);
	}

	if ($locations->num_rows() == 0) {
		$table_data_rows .= "<tr><td colspan='7'><span class='col-md-12 text-center text-warning' >" . lang('locations_no_locations_to_display') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_location_data_row($location, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));

	$table_data_row = '<tr>';
	$table_data_row .= "<td><input type='checkbox' id='asset_$location->location_id' value='" . $location->location_id . "'/><label for='asset_$location->location_id'><span></span></label></td>";
	$displayable_columns = $CI->Employee->get_locations_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $location->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($location);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}
		//	if($val == $asset->barcode){
		//		$table_data_row.="<td><a href='$barcode_url' class='rollover'>$val</a></td>";
		//	}else{
		if ($column_id == 'first_name' && $val == '') {
			$table_data_row .= "<td>" . lang($controller_name . '_notemployee') . "</td>";
		} else {
			$table_data_row .= '<td>' . $val . '</td>';
		}
		//	}

		//Unset for next round of the loop
		unset($data);
	}
	$table_data_row .= '<td class="actions">' . '<div class="piluku-dropdown dropdown btn-group table_buttons upordown">' .
		'<a href="' . site_url($controller_name . "/view/$location->location_id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a></div></td>';

	//$table_data_row = '<tr class="btn-default" style="height:50px; cursor:pointer; background-color:transparent; border:none;"  ondblclick="javascript:location.href = \'' . site_url('locations/view') . '/' . $location->location_id . '/2\'" onclick="actualizar_mapa(' . $location->latitude . ', ' . $location->longitude . ')">';
	//$table_data_row .= "<td class='hidden'><input type='checkbox' id='location_$location->location_id' value='" . $location->location_id . "'/><label for='location_$location->location_id'><span></span></label></td>";
	//$table_data_row .= '<td><span style="padding:10px 20px 10px 20px; background-color:#A3A3A3; border-radius:5px;">' . $location->location_id . '</span></td>';
	//$table_data_row .= '<td style="font-size:13pt;">' . H($location->name) . '</td>';
	//$table_data_row.='<td>'.H($location->address).'</td>';
	//$table_data_row.='<td>'.H($location->phone).'</td>';
	//$table_data_row.='<td>'.H($location->email).'</td>';
	//$table_data_row .= '<td class="rightmost">' . anchor($controller_name . "/view/$location->location_id/2", '<i class="ion-edit" style="font-size:17pt; color: #A3A3A3;"></i>' /*lang('common_edit')*/, array('class' => ' ', 'title' => lang($controller_name . '_update'))) . '</td>';

	$table_data_row .= '</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage giftcards.
*/
function get_giftcards_manage_table($giftcards, $controller, $deleted = 0, $ordenado = false)
{
	$CI = &get_instance();

	$table = '<table class="tablesorter table table-hover" id="sortable_table">';

	if (!$ordenado) {
		$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
		$headers[] = array('label' => lang('common_giftcards_giftcard_number'), 'sort_column' => 'giftcard_number');
		$headers[] = array('label' => lang('common_giftcards_card_value'), 'sort_column' => 'value');
		$headers[] = array('label' => lang('common_description'), 'sort_column' => 'description');
		$headers[] = array('label' => lang('common_customer_name'), 'sort_column' => 'last_name');
		$headers[] = array('label' => lang('common_active') . '/' . lang('common_inactive'), 'sort_column' => 'inactive');

		if (!$deleted) {
			$headers[] = array('label' => lang('common_acciones'), 'sort_column' => '');
		}
		$table .= '<thead><tr>';
		$count = 0;
		foreach ($headers as $header) {
			$count++;
			$label = $header['label'];
			$sort_col = $header['sort_column'];
			if ($count == 1) {
				$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
			} elseif ($count == count($headers)) {
				$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
			} else {
				$table .= "<th data-sort-column='$sort_col'>$label</th>";
			}
		}
		$table .= '</tr></thead><tbody>';
	} else {
		$table .= '<tbody>';
	}

	$table .= get_giftcards_manage_table_data_rows($giftcards, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the giftcard.
*/
function get_giftcards_manage_table_data_rows($giftcards, $controller, $deleted)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($giftcards->result() as $giftcard) {
		$table_data_rows .= get_giftcard_data_row($giftcard, $controller, $deleted);
	}

	if ($giftcards->num_rows() == 0) {
		$table_data_rows .= "<tr><td  colspan='8'><span class='col-md-12 text-center text-warning' >" . lang('giftcards_no_giftcards_to_display') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_giftcard_data_row($giftcard, $controller, $deleted)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$link = site_url('reports/detailed_' . $controller_name . '/' . $giftcard->customer_id . '/0');
	$cust_info = $CI->Customer->get_info($giftcard->customer_id);

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('giftcards/view') . '/' . $giftcard->giftcard_id . '/2\'">';
	$table_data_row .= "<td><input type='checkbox' id='giftcard_$giftcard->giftcard_id' value='" . $giftcard->giftcard_id . "'/><label for='giftcard_$giftcard->giftcard_id'><span></span></label></td>";
	$table_data_row .= '<td>' . H($giftcard->giftcard_number) . '</td>';
	$table_data_row .= '<td>' . to_currency(H($giftcard->value), 10) . '</td>';
	$table_data_row .= '<td>' . H($giftcard->description) . '</td>';
	$table_data_row .= '<td><a class="underline" href="' . $link . '">' . H($cust_info->first_name) . ' ' . H($cust_info->last_name) . '</a></td>';
	$table_data_row .= '<td>' . ($giftcard->inactive ? lang('common_inactive') : lang('common_active')) . '</td>';

	if (!$deleted) {
		$table_data_row .= '<td class="actions">
		<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
		<a href="' . site_url($controller_name . "/view/$giftcard->giftcard_id/2") . '"role="button" class="btn btn-more edit_action" title="' . lang($controller_name . '_update') . '">' . lang('common_edit') . '</a>
		<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
			<i class="ion-more"></i>
		</button>';

		$table_data_row.='<ul class="dropdown-menu dropdown-menu-left dropdown_inventory_actions" role="menu">';
		$table_data_row .= '<li>' . anchor($controller_name . "/clone_giftcard/$giftcard->giftcard_id", '' .  lang('common_clone'), array('class' => '', 'title' => lang('common_clone'))) . '</li>';
		$table_data_row .= '<li>' . anchor($controller_name . "/generate_barcode_labels/$giftcard->giftcard_id", '' .  lang('common_print'), array('class' => '', 'title' => lang('common_print'))) . '</li>';
		$table_data_row .= '<li>' . anchor($controller_name . "/recharge_giftcard_access/$giftcard->giftcard_number", '' .  lang('giftcard_recharge_form'), array('class' => '', 'title' => lang('giftcard_recharge_form'))) . '</li>';
		$table_data_row.='</ul></div></td>';
	}

	$table_data_row .= '</tr>';
	return $table_data_row;
}

/*
Gets the html table to manage item kits.
*/
/*function get_item_kits_manage_table( $item_kits, $controller )
{
	$CI =& get_instance();

	$table='<table class="tablesorter table table-hover" id="sortable_table">';

	$has_cost_price_permission = $CI->Employee->has_module_action_permission('item_kits','see_cost_price', $CI->Employee->get_logged_in_employee_info()->person_id);

	if ($has_cost_price_permission)
	{
		$headers = array('<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>',
		lang('item_kits_id'),
		lang('common_item_number_expanded'),
		lang('item_kits_name'),
		lang('item_kits_description'),
		lang('common_cost_price'),
		lang('common_unit_price'),
		lang('common_clone'),
		lang('common_edit'),
		);
	}
	else
	{
		$headers = array('<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>',
		lang('item_kits_id'),
		lang('common_item_number_expanded'),
		lang('item_kits_name'),
		lang('item_kits_description'),
		lang('common_unit_price'),
		lang('common_clone'),
		lang('common_edit'),
		'&nbsp;',
		);
	}
	$table.='<thead><tr>';
	$count = 0;
	foreach($headers as $header)
	{
		$count++;

		if ($count == 1)
		{
			$table.="<th class='leftmost'>$header</th>";
		}
		elseif ($count == count($headers))
		{
			$table.="<th class='rightmost'>$header</th>";
		}
		else
		{
			$table.="<th>$header</th>";
		}
	}
	$table.='</tr></thead><tbody>';
	$table.=get_item_kits_manage_table_data_rows( $item_kits, $controller );
	$table.='</tbody></table>';
	return $table;
}*/

function get_item_kits_manage_table($item_kits, $controller, $deleted = 0)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_item_kit_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}

	if ($deleted == 0) {
		//$headers[] = array('label' => lang('common_clone'), 'sort_column' => '');
		//$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
		$headers[] = array('label' => lang('common_acciones'), 'sort_column' => '');
	}
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_item_kits_manage_table_data_rows($item_kits, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}

/*
Gets the html data rows for the item kits.
*/
function get_item_kits_manage_table_data_rows($item_kits, $controller, $deleted)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($item_kits->result() as $item_kit) {
		$table_data_rows .= get_item_kit_data_row($item_kit, $controller, $deleted);
	}

	if ($item_kits->num_rows() == 0) {
		$table_data_rows .= "<tr><td colspan='9'><span class='col-md-12 text-center text-warning' >" . lang('item_kits_no_item_kits_to_display') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_item_kit_data_row($item_kit, $controller, $deleted)
{

	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$avatar_url = $item_kit->kit_image_id ?  site_url('app_files/view_optimized/' . $item_kit->kit_image_id) : base_url('assets/assets/images/avatar-default.jpg');
	$has_cost_price_permission = $CI->Employee->has_module_action_permission('item_kits', 'see_cost_price', $CI->Employee->get_logged_in_employee_info()->person_id);

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('item_kits/view') . '/' . $item_kit->item_kit_id . '/2\'">';
	$table_data_row .= "<td><input type='checkbox' id='item_kit_$item_kit->item_kit_id' value='" . $item_kit->item_kit_id . "'/><label for='item_kit_$item_kit->item_kit_id'><span></span></label></td>";

	$displayable_columns = $CI->Employee->get_item_kit_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $item_kit->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($item_kit);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}

		$table_data_row .= '<td>' . $val . '</td>';
		//Unset for next round of the loop
		unset($data);
	}
	if ($deleted == 0) {
		$table_data_row .= '<td class="actions">' .
			'<div class="piluku-dropdown dropdown btn-group table_buttons upordown">
						 <a href="' . site_url($controller_name . "/view/$item_kit->item_kit_id?redirect=item_kits") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
						<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
							<span class="ion-more"></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-left dropdown_kit_actions" role="menu">';

		$table_data_row .= '<li>' . anchor($controller_name . "/clone_item_kit/$item_kit->item_kit_id", '<i class="ion-ios-browsers-outline"></i> ' . lang('common_clone'), array('class' => ' ', 'title' => lang('common_clone'))) . '</li>';

		$table_data_row .= '</ul>
					</div>'
			. '</td>';
		//$table_data_row.='<td class="rightmost" >'.anchor($controller_name."/clone_item_kit/$item_kit->item_kit_id	", lang('common_clone'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';
		//$table_data_row.='<td class="rightmost">'.anchor($controller_name."/view/$item_kit->item_kit_id/2	", lang('common_edit'),array('class'=>' ','title'=>lang($controller_name.'_update'))).'</td>';
	}
	if ($avatar_url) {
		$table_data_row .= "<td><a href='$avatar_url' class='rollover'><img src='" . $avatar_url . "' alt='" . H($item_kit->name) . "' class='img-polaroid' width='45' /></a></td>";
	}
	$table_data_row .= '</tr>';
	return $table_data_row;
}


function get_expenses_manage_table($expenses, $controller, $deleted = 0, $ordenenado = false)
{
	$CI = &get_instance();
	$table = '<table class="tablesorter table table-hover" id="sortable_table">';

	if (!$ordenenado) {
		$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

		$headers[] = array('label' => lang('expenses_id'), 'sort_column' => 'id');
		$headers[] = array('label' => lang('expenses_type'), 'sort_column' => 'expense_type');
		$headers[] = array('label' => lang('expenses_description'), 'sort_column' => 'expense_description');
		$headers[] = array('label' => lang('common_category'), 'sort_column' => 'category');
		$headers[] = array('label' => lang('expenses_date'), 'sort_column' => 'expense_date');
		$headers[] = array('label' => lang('expenses_amount'), 'sort_column' => 'expense_amount');
		$headers[] = array('label' => lang('common_tax'), 'sort_column' => 'expense_tax');
		$headers[] = array('label' => lang('common_recipient_name'), 'sort_column' => 'employee_recv');
		$headers[] = array('label' => lang('common_approved_by'), 'sort_column' => 'employee_appr');

		if (!$deleted) $headers[] = array('label' => lang('common_edit'), 'sort_column' => '');

		$table .= '<thead><tr>';
		$count = 0;
		foreach ($headers as $header) {
			$count++;
			$label = $header['label'];
			$sort_col = $header['sort_column'];
			if ($count == 1) {
				$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
			} elseif ($count == count($headers)) {
				$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
			} else {
				$table .= "<th data-sort-column='$sort_col'>$label</th>";
			}
		}
		$table .= '</tr></thead><tbody>';
	} else {
		$table .= '<tbody>';
	}

	$table .= get_expenses_manage_table_data_rows($expenses, $controller, $deleted);
	$table .= '</tbody></table>';
	return $table;
}
/*
Gets the html data rows for the items.
*/
function get_expenses_manage_table_data_rows($expenses, $controller, $deleted)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($expenses->result() as $expense) {
		$table_data_rows .= get_expenses_data_row($expense, $controller, $deleted);
	}

	if ($expenses->num_rows() == 0) {
		$table_data_rows .= "<tr><td colspan='11'><span class='col-md-12 text-center text-warning' >" . lang('expenses_no_expenses_to_display') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_expenses_data_row($expense, $controller, $deleted)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('expenses/view') . '/' . $expense->id . '/2\'">';
	$table_data_row .= "<td><input type='checkbox' id='expenses_$expense->id' value='" . $expense->id . "'/><label for='expenses_$expense->id'><span></span></label></td>";
	$table_data_row .= '<td>' . $expense->id . '</td>';
	$table_data_row .= '<td>' . H($expense->expense_type) . '</td>';
	$table_data_row .= '<td>' . H($expense->expense_description) . '</td>';
	$table_data_row .= '<td>' . H($expense->category) . '</td>';
	$table_data_row .= '<td>' . date(get_date_format(), strtotime($expense->expense_date)) . '</td>';
	$table_data_row .= '<td>' . to_currency($expense->expense_amount) . '</td>';
	$table_data_row .= '<td>' . to_currency($expense->expense_tax) . '</td>';
	$table_data_row .= '<td>' . H($expense->employee_recv) . '</td>';
	$table_data_row .= '<td>' . H($expense->employee_appr) . '</td>';
	if (!$deleted)
		$table_data_row .= '<td class="actions"> <div class="piluku-dropdown dropdown btn-group table_buttons upordown">
		<a href="' . site_url($controller_name . "/view/$expense->id/2") . '" role="button" class="btn btn-more edit_action" title ="' . lang($controller_name . '_update') . '">' . lang('common_edit') . '</a></div></td>';

	$table_data_row .= '</tr>';
	return $table_data_row;
}

function get_appointments_manage_table($appointments, $controller, $deleted = 0, $ordenado = false)
{
	$CI = &get_instance();
	$table = '<table class="tablesorter table table-hover" id="sortable_table">';
	if (!$ordenado) {
		$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');

		$headers[] = array('label' => lang('appointment_id'), 'sort_column' => 'id');
		$headers[] = array('label' => lang('appointment_asunto'), 'sort_column' => 'asunto');
		$headers[] = array('label' => lang('appointment_hora_inicio'), 'sort_column' => 'fecha_hora_inicio');
		// $headers[] = array('label' => lang('appointment_hora_fin') , 'sort_column' => 'fecha_hora_fin' );
		$headers[] = array('label' => lang('appointment_persona'), 'sort_column' => 'person_id');
		$headers[] = array('label' => lang('appointment_descripcion'), 'sort_column' => 'descripcion');
		$headers[] = array('label' => lang('appointments_recurrence'), 'sort_column' => 'recurrente');

		if (!$deleted) $headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
		$table .= '<thead><tr>';
		$count = 0;
		foreach ($headers as $header) {
			$count++;
			$label = $header['label'];
			$sort_col = $header['sort_column'];
			if ($count == 1) {
				$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
			} elseif ($count == count($headers)) {
				$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
			} else {
				$table .= "<th data-sort-column='$sort_col'>$label</th>";
			}
		}
		$table .= '</tr></thead><tbody>';
	} else {
		$table .= '<tbody>';
	}

	$table .= get_appointments_manage_table_data_rows($appointments, $controller, $deleted);
	$table .= '</tbody></table>';

	return $table;
}

function get_appointments_manage_table_data_rows($appointments, $controller, $deleted)
{
	$CI = &get_instance();
	$table_data_rows = '';

	if ($appointments) {
		foreach ($appointments->result() as $appointment) {
			$table_data_rows .= get_appointments_data_row($appointment, $controller, $deleted);
		}
		if ($appointments->num_rows() == 0) {
			$table_data_rows .= "<tr><td colspan='11'><span class='col-md-12 text-center text-warning' >" . lang('appointment_no_appointments_to_display') . "</span></td></tr>";
		}
	} else {
		$table_data_rows .= "<tr><td colspan='11'><span class='col-md-12 text-center text-warning' >" . lang('appointment_no_appointments_to_display') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_appointments_data_row($appointment, $controller, $deleted)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$periodicidad = '';
	if ($appointment->recurrente == 1) {
		if ($appointment->all == 1) {
			$periodicidad = lang('appointments_all_days');
		} else {
			$appointment->domingo == 1 ? $dom = lang('appointments_sun') : $dom = '';
			$appointment->lunes == 1 ? $lun = lang('appointments_mon') : $lun = '';
			$appointment->martes == 1 ? $mar = lang('appointments_tue') : $mar = '';
			$appointment->miercoles == 1 ? $mie = lang('appointments_wed') : $mie = '';
			$appointment->jueves == 1 ? $jue = lang('appointments_thu') : $jue = '';
			$appointment->viernes == 1 ? $vie = lang('appointments_fri') : $vie = '';
			$appointment->sabado == 1 ? $sab = lang('appointments_sat') : $sab = '';
			$periodicidad = $dom . ' ' . $lun . ' ' . $mar . ' ' . $mie . ' ' . $jue . ' ' . $vie . ' ' . $sab;
		}
	} elseif ($appointment->recurrente == 2) {
		switch ($appointment->periodicidad) {
			case 'sema':
				$periodicidad = lang('appointments_semanal');
				break;
			case 'quin':
				$periodicidad = lang('appointments_quincenal');
				break;
			case 'mens':
				$periodicidad = lang('appointments_mensual');
				break;
			case 'bime':
				$periodicidad = lang('appointments_bimestral');
				break;
			case 'trim':
				$periodicidad = lang('appointments_trimestral');
				break;
			case 'cuat':
				$periodicidad = lang('appointments_cuatrimestral');
				break;
			case 'seme':
				$periodicidad = lang('appointments_semestral');
				break;
			case 'anual':
				$periodicidad = lang('appointments_anual');
				break;
			case 'bienal':
				$periodicidad = lang('appointments_bienal');
				break;
			default:
				break;
		}
	}
	strlen($appointment->descripcion) > 0 ? 	$description = substr($appointment->descripcion, 0, 50) . '...' : $description = '';

	$table_data_row = '<tr class="noselect" ondblclick="javascript:location.href = \'' . site_url('appointments/view') . '/' . $appointment->id . '/2\'">';
	$table_data_row .= "<td><input type='checkbox' id='appointments_$appointment->id' value='" . $appointment->id . "'/><label for='appointments_$appointment->id'><span></span></label></td>";
	$table_data_row .= '<td>' . $appointment->id . '</td>';
	$table_data_row .= '<td>' . H($appointment->asunto) . '</td>';
	$table_data_row .= '<td>' . date('d-M-Y h:i a', strtotime($appointment->fecha_hora_inicio)) . '</td>';
	// $table_data_row.='<td>'.date('d-M-Y h:i a', strtotime($appointment->fecha_hora_fin)).'</td>';
	$table_data_row .= '<td>' . H($appointment->first_name . ' ' . $appointment->last_name) . '</td>';
	$table_data_row .= '<td>' . H($description) . '</td>';
	$table_data_row .= '<td>' . H($periodicidad) . '</td>';
	if (!$deleted)
		$table_data_row .= '<td class="actions"><div class="piluku-dropdown dropdown btn-group table_buttons upordown">
		<a href="' . site_url($controller_name . "/view/$appointment->id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
		</div></td>';

	$table_data_row .= '</tr>';
	return $table_data_row;
}
//assets
function get_assets_manage_table($assets, $controller)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_asset_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}
	//$headers[] = array('label' => lang('common_clone'), 'sort_column' => '');
	//$headers[] = array('label' => lang('common_print'), 'sort_column' => '');
	$headers[] = array('label' => lang('common_acciones'), 'sort_column' => '');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');

	$table .= "<thead><tr>";
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_assets_manage_table_data_rows($assets, $controller);
	$table .= "</tbody></table>";
	return $table;
}

function get_assets_manage_table_data_rows($assets, $controller)
{
	$CI = &get_instance();
	$table_data_rows = '';
	foreach ($assets->result() as $asset) {
		$table_data_rows .= get_asset_data_row($asset, $controller);
	}

	if ($assets->num_rows() == 0) {
		$table_data_rows .= "<tr>
			<td colspan='13'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang(strtolower(get_class($CI)) . '_nothingtoshow') . "</span>&nbsp;&nbsp;</span></td>
		</tr>";
	}

	return $table_data_rows;
}

function get_asset_data_row($asset, $controller)
{
	$CI = &get_instance();
	$low_inventory_class = "";

	$controller_name = strtolower(get_class($CI));
	$avatar_url = $asset->picture_id ?  site_url('app_files/view_optimized/' . $asset->picture_id) : base_url('assets/assets/images/avatar-default.jpg');
	$barcode_url = site_url('barcode') . "?barcode=$asset->barcode&text=$asset->barcode";
	$table_data_row = '<tr>';
	$table_data_row .= "<td><input type='checkbox' id='asset_$asset->asset_id' value='" . $asset->asset_id . "'/><label for='asset_$asset->asset_id'><span></span></label></td>";

	$displayable_columns = $CI->Employee->get_asset_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $asset->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($asset);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}
		//	if($val == $asset->barcode){
		//		$table_data_row.="<td><a href='$barcode_url' class='rollover'>$val</a></td>";
		//	}else{
		if ($column_id == 'first_name' && $val == '') {
			$table_data_row .= "<td>" . lang($controller_name . '_notemployee') . "</td>";
		} else {
			$table_data_row .= '<td>' . $val . '</td>';
		}
		//	}

		//Unset for next round of the loop
		unset($data);
	}

	//$table_data_row.='<td class="rightmost">'.anchor($controller_name."/clone_item/$asset->asset_id	", lang('common_clone'),array('class'=>' ','title'=>lang('common_clone'))).'</td>';
	
	$table_data_row .= '<td class="actions">' . '<div class="piluku-dropdown dropdown btn-group table_buttons upordown">' .
		'<a href="' . site_url($controller_name . "/view/$asset->asset_id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
		<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
			<i class="ion-more"></i>
		</button>
		<ul class="dropdown-menu dropdown-menu-left dropdown_inventory_actions" role="menu">
			<li>
			' .anchor($controller_name . "/generate_barcode_labels/$asset->asset_id",''.lang('common_print'),array('class' => '', 'title' => lang('common_print'))) . '
			</div></td>
			</li>
		</ul>
		</div></td>';

	if ($avatar_url) {
		$table_data_row .= "<td><a href='$avatar_url' class='rollover'><img src='" . $avatar_url . "' alt='" . $avatar_url . "' class='img-polaroid' width='45' /></a></td>";
	}

	$table_data_row .= '</tr>';
	return $table_data_row;
}

//Proyectos
function get_projects_manage_table($projects, $controller)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="cursor table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_projects_columns_to_display();

	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}
	$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];
		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_projects_manage_table_data_rows($projects, $controller);
	$table .= "</tbody></table>";

	return $table;
}
function get_projects_manage_table_data_rows($projects, $controller)
{
	$CI = &get_instance();
	$table_data_rows = '';
	foreach ($projects->result() as $project) {
		$table_data_rows .= get_project_data_row($project, $controller);
	}

	if ($projects->num_rows() == 0) {
		$table_data_rows .= "<tr>
			<td colspan='13'><span class='col-md-12 text-center' ><span class='text-warning'>" . lang('projects_nothing') . "</span>&nbsp;&nbsp;</span></td></tr>";
	}
	return $table_data_rows;
}

function get_project_data_row($project, $controller)
{
	$CI = &get_instance();
	$controller_name = strtolower(get_class($CI));
	$table_data_row = '<tr>';
	$table_data_row .= "<td><input type='checkbox' id='asset_$project->project_id' value='" . $project->project_id . "'/><label for='project_$project->project_id'><span></span></label></td>";

	$displayable_columns = $CI->Employee->get_projects_columns_to_display();
	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');
	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $project->{$column_id};
		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($project);
			}
			$format_function = $column_values['format_function'];
			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}
		if ($column_id == 'name') {
			$table_data_row .= '<td><a href="' . site_url("projects/summary/$project->project_id") . '">' . $val . '</a></td>';
		} else {
			$val = $val != '' ? $val : 'Sin asignar';
			$table_data_row .= "<td>$val</td>";
		}
		unset($data);
	}
	$table_data_row .= '<td class="actions"><div class="piluku-dropdown dropdown btn-group table_buttons upordown">
	<a href="' . site_url($controller_name . "/view/$project->project_id/2") . '" role="button" class="btn btn-more edit_action">' . lang('common_edit') . '</a>
	<button type="button" class="btn btn-more dropdown-toggle" style="height:34px;" data-toggle="dropdown" aria-expanded="false">
		<i class="ion-more"></i>
	</button>
	<ul class="dropdown-menu dropdown-menu-left dropdown_inventory_actions" role="menu">
		<li> '
		. anchor($controller_name . "/print_info/$project->project_id", '<i class="ion-clipboard"></i> ' .  lang('projects_ficha'), array('class' => '', 'title' => lang('projects_ficha'))) . 
		'</li>'.
	'</ul> </div></td>';
	
	$table_data_row .= '</tr>';
	return $table_data_row;
}

// Activities
function get_activities_manage_table($activities, $controller)
{
	$CI = &get_instance();
	$CI->load->model('Employee');
	$table = '<table class="cursor table tablesorter table-hover" id="sortable_table">';
	$columns_to_display = $CI->Employee->get_activities_columns_to_display();
	$headers[] = array('label' => '<input type="checkbox" id="select_all" /><label for="select_all"><span></span></label>', 'sort_column' => '');
	foreach (array_values($columns_to_display) as $value) {
		$headers[] = $value;
	}
	$headers[] = array('label' => lang('common_edit'), 'sort_column' => '');
	$headers[] = array('label' => '&nbsp;', 'sort_column' => '');
	$table .= '<thead><tr>';
	$count = 0;
	foreach ($headers as $header) {
		$count++;
		$label = $header['label'];
		$sort_col = $header['sort_column'];

		if ($count == 1) {
			$table .= "<th data-sort-column='$sort_col' class='leftmost'>$label</th>";
		} elseif ($count == count($headers)) {
			$table .= "<th data-sort-column='$sort_col' class='rightmost'>$label</th>";
		} else {
			$table .= "<th data-sort-column='$sort_col'>$label</th>";
		}
	}
	$table .= '</tr></thead><tbody>';
	$table .= get_activities_manage_table_data_rows($activities, $controller);
	$table .= "</tbody></table>";

	return $table;
}

function  get_activities_manage_table_data_rows($activities, $controller)
{
	$CI = &get_instance();
	$table_data_rows = '';

	foreach ($activities->result() as $activity) {
		$table_data_rows .= get_activities_data_row($activity, $controller);
	}

	if ($activities->num_rows() == 0) {
		$table_data_rows .= "<tr><td colspan='11'><span class='col-md-12 text-center text-warning' >" . lang('projects_nothingAc') . "</span></td></tr>";
	}

	return $table_data_rows;
}

function get_activities_data_row($activity, $controller)
{

	$CI = &get_instance();
	$low_inventory_class = "";

	$controller_name = strtolower(get_class($CI));
	$table_data_row = '<tr>';
	$table_data_row .= "<td><input type='checkbox' id='activity_$activity->activitie_id' value='" . $activity->activitie_id . "'/><label for='activity_$activity->activitie_id'><span></span></label></td>";
	$displayable_columns = $CI->Employee->get_activities_columns_to_display();

	$CI->load->helper('text');
	$CI->load->helper('date');
	$CI->load->helper('currency');

	foreach ($displayable_columns as $column_id => $column_values) {
		$val = $activity->{$column_id};

		if (isset($column_values['format_function'])) {
			if (isset($column_values['data_function'])) {
				$data_function = $column_values['data_function'];
				$data = $data_function($activity);
			}

			$format_function = $column_values['format_function'];

			if (isset($data)) {
				$val = $format_function($val, $data);
			} else {
				$val = $format_function($val);
			}
		}

		if (!isset($column_values['html']) || !$column_values['html']) {
			$val = H($val);
		}
		if (($val == null) && ($column_id == 'start_date' || $column_id == 'end_date')) {
			$val = lang('common_none');
		}
		$table_data_row .= "<td>$val</td>";
		unset($data);
	}
	$table_data_row .= '<td class="actions rightmost"> <div class="piluku-dropdown dropdown btn-group table_buttons upordown"> 
	<a href="' . site_url($controller_name . "/activitie/$activity->project_id/$activity->activitie_id/2") . '" role="button" class="btn btn-more edit_action" title="' . lang('common_edit') . '">' . lang('common_edit') . '</a> </div></td>';
	$table_data_row .= '</tr>';
	return $table_data_row;
}
