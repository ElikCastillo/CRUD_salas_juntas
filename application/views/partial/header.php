
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Ejercicio Perfil Development">
    <meta name="author" content="Elik Castillo">
    <title>Ejercicio Perfil Development</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <script src="<?php echo base_url() . 'assets/listado/js/jquery-1.11.2.min.js?'; ?>"></script>
    <script src="<?php echo base_url() . 'assets/listado/js/bootstrap.min.js?'; ?>"></script>
    <script src="<?php echo base_url() . 'assets/listado/js/modernizr.custom.js?'; ?>"></script>
    <script src="<?php echo base_url() . 'assets/js/all.js?'; ?>" type="text/javascript" charset="UTF-8"></script>

    <script type="text/javascript">
    var SITE_URL = "<?php echo site_url(); ?>";
    var BASE_URL = "<?php echo base_url(); ?>";
    var CURRENT_URL_RELATIVE = "<?php echo uri_string() . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''); ?>";
    var ENABLE_SOUNDS = <?php echo $this->config->item('enable_sounds') ? 'true' : 'false'; ?>;
    var JS_DATE_FORMAT = <?php echo json_encode(get_js_date_format()); ?>;
    var JS_TIME_FORMAT = <?php echo json_encode(get_js_time_format()); ?>;
    var LOCALE = <?php echo json_encode(get_js_locale()); ?>;
    var IS_MOBILE = <?php echo $this->agent->is_mobile() ? 'true' : 'false'; ?>;
  </script>
  </head>

  <body>
<div class="main-content">