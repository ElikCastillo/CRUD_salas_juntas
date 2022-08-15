<?php

function is_rtl_lang()
{

}

function lang($line, $for = '', $attributes = array())
{
	$lazy_load = (!defined("LAZY_LOAD") or LAZY_LOAD == TRUE);
	
	$orig_line = $line;
	$line = get_instance()->lang->line($line);
	
	if (!$line)
	{
		$CI =& get_instance();
		
		if ($lazy_load)
		{
			$langfile = substr($orig_line,0,strrpos($orig_line,'_')).'_lang.php';
		
			$langpath = APPPATH.'language/'.$CI->config->item('language').'/'.$langfile;
			if (!file_exists($langpath))
			{
				$log_message = "Couldn't load language file $langfile CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
				log_message('error', $log_message);
				die("Couldn't load language file $langfile");
			}
		
			$CI->lang->load($langfile);
			$log_message = "Lazy Loaded language $langfile for $orig_line CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
			log_message('error', $log_message);
			
			$line = get_instance()->lang->line($orig_line);
		}
		
		if (!$line)
		{
			$log_message = "Couldn't load language key $orig_line CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
			log_message('error', $log_message);
			if (ENVIRONMENT =='development')
			{
				die("Couldn't load language key $orig_line");
			}
		}
	}
	
	
	if ($for !== '')
	{
		$line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
	}

	return $line;
}
