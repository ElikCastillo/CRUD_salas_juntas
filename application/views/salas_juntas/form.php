<?php $this->load->view("partial/header"); ?>
<style type="text/css">
  .pull-right1 {margin-top: 30px; float: right;}
</style>
<?php echo form_open('Salas_juntas/save_sala/'.$id, array('id' => 'save_sala_form', 'class' => '')); ?>
<div class="container">
  <div class="row">
      <div class="col-12">
      <div class="page-header">
        <h1>Reservar Sala de Junta </h1>
      </div>
      <div class="panel panel-piluku">
        <div class="panel-body">
          <div class="panel-body">

            <?php foreach ($sala as $key => $value) { ?>

             <div class="form-group inputs_fechas date">
                <label for="fecha_fin" class="col-sm-3 col-md-3 col-lg-3 control-label texto_formulario" required>Fecha Inicio:</label>
                <div class="col-sm-3 col-md-3 col-lg-3">
                  <input class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" placeholder="Fecha Inicio" value="<?= $value->fecha_inicio ? date('Y-m-d', strtotime($value->fecha_inicio)) : '' ?>">
                </div>
                <label for="fecha_fin" class="col-sm-3 col-md-3 col-lg-3 control-label texto_formulario" required>Hora Inicio:</label>
                <div class="col-sm-3 col-md-3 col-lg-3">
                  <input class="form-control" type="time" id="hora_inicio" name="hora_inicio" min="09:00" max="17:00" placeholder="Hora Inicio" value="<?= $value->fecha_inicio ? date('H:i:s', strtotime($value->fecha_inicio)) : '' ?>">
                </div>
             </div>

             <div class="form-group inputs_fechas date">
                <label for="fecha_fin" class="col-sm-3 col-md-3 col-lg-3 control-label texto_formulario" required>Fecha Fin:</label>
                <div class="col-sm-3 col-md-3 col-lg-3">
                  <input class="form-control" type="date" id="fecha_fin" name="fecha_fin" readonly placeholder="Fecha Final" value="<?= $value->fecha_fin ? date('Y-m-d', strtotime($value->fecha_fin)) : '' ?>">
                </div>
                <label for="fecha_fin" class="col-sm-3 col-md-3 col-lg-3 control-label texto_formulario" required>Hora Fin:</label>
                <div class="col-sm-3 col-md-3 col-lg-3">
                  <input class="form-control" type="time" id="hora_fin" name="hora_fin" placeholder="Hora Final" value="<?= $value->fecha_fin ? date('H:i:s', strtotime($value->fecha_fin)) : '' ?>">
                </div>
             </div>

            <?php } ?>
           <div class="form-actions pull-right1">
              <?php
              echo form_submit(array(
                'name'=>'submitf',
                'id'=>'submitf',
                'value'=>'Guardar' ,
                'class'=>' submit_button btn btn-primary float_right boton_flotante')
              );
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo form_close(); ?>

<?php $this->load->view("partial/footer");?>

<script type="text/javascript">
var submitting = false;

$(document).ready(function()
{
   $("#fecha_inicio").change(function(){
      $("#fecha_fin").val($("#fecha_inicio").val())
       document.getElementById("fecha_fin").setAttribute("min", $("#fecha_inicio").val());
       document.getElementById("fecha_fin").setAttribute("max", $("#fecha_inicio").val());
       document.getElementById("fecha_fin").setAttribute("readonly", "readonly");
    });

   $("#hora_inicio").change(function(){
    $.post('<?php echo site_url("salas_juntas/horas");?>', {
        hora: $("#hora_inicio").val(),
        fecha: $("#fecha_inicio").val(),
    },function(response) {
        $('#hora_fin').val(response.hora);

        if (response.hora >= '17:00') {
          document.getElementById("hora_fin").setAttribute("min", $("#hora_inicio").val());
          document.getElementById("hora_fin").setAttribute("max", '17:00');
          $('#hora_fin').val('17:00');
          $('#fecha_fin').val(response.fecha);

        }else{
          document.getElementById("hora_fin").setAttribute("min", $("#hora_inicio").val());
          document.getElementById("hora_fin").setAttribute("max", response.hora);
          document.getElementById("fecha_fin").setAttribute("max", response.fecha);
          $('#hora_fin').val(response.hora);
          $('#fecha_fin').val(response.fecha);
          $('#fecha_inicio').val(response.fecha);
        }
    }, "json");
    });

  //Validaciones del formulario
   $('#save_sala_form').validate({
  submitHandler:function(form)
  {
     doSave_SalaSubmit(form);
  },
  rules:
  {
    fecha_inicio: "required",
    fecha_fin: "required"
  },
  errorClass: "text-danger",
  errorElement: "span",
    highlight:function(element, errorClass, validClass) {
      $(element).parents('.form-group').removeClass('has-success').addClass('has-error');
    },
    unhighlight: function(element, errorClass, validClass) {
      $(element).parents('.form-group').removeClass('has-error').addClass('has-success');
    },
  messages:
  {
    fecha_inicio: <?php echo json_encode('Revise correctamente las fechas, no debe ser mayor a 2 horas'); ?>,
    hora_inicio: <?php echo json_encode('Horario Permitido de 9:00 am a 5:00 pm y no mayor a 2 horas'); ?>,
    hora_fin: <?php echo json_encode('Horario Permitido de 9:00 am a 5:00 pm y no mayor a 2 horas'); ?>,
  }
  });
});

function doSave_SalaSubmit(form)
{
  $("#grid-loader").show();
  if (submitting) return;
  submitting = true;

  $(form).ajaxSubmit({
    success:function(response)
    {
      submitting = false;
      alert(response.message);
      window.location.href = '<?=site_url('salas_juntas')?>';
    },
      resetForm: true,
      dataType:'json'
    });
}
</script>

