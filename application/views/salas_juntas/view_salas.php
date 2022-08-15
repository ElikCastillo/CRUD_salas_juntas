<?php $this->load->view("partial/header");?>
<style type="text/css">
  .opciones {text-align: center;}
</style>
<?php
header("Refresh: 30; URL='salas_juntas'"); //actualizar pagina cada 30 segundos para la verificacion si ya paso su hora final
?>
<div class="container">
	<div class="">
		  <div class="col-12">
		  	<ul class="list-inline pull-right">
				<li>
					<button class="btn btn-primary " id="add_sala" data-toggle="modal" data-target="#exampleModal">Agregar Sala de Junta</button>
				</li>
			</ul>
			<div class="page-header">
				<h1>Listado De Salas de Juntas</h1>
			</div>

			<div class="row">
				<?php foreach ($all_sala as $key => $value) { ?>
					<div class="col-sm-4">
					  <div class="panel <?= $value->status ? 'panel-danger' : 'panel-primary' ?> ">
					    <div class="panel-heading">
					      <h3 class="panel-title"><?=$value->id?> Nombre: <?=$value->nombre?></h3>
					    </div>
					    <div class="panel-body">
					      	Fecha inicio: <?= $value->fecha_inicio ? date(get_date_format()." ".get_time_format(), strtotime($value->fecha_inicio)) : '' ?> </br>
					      	Fecha Fin:  <?= $value->fecha_fin ? date(get_date_format()." ".get_time_format(), strtotime($value->fecha_fin)) : '' ?> </br>
					       </br>
					       <div id="navbar" class="opciones">
					       		<?php if ($value->status != 1) { ?>
					       			<button type="button" class="btn btn-success" onclick="reservacion(<?=$value->id?>)">Reservar</button>
					       		<?php } ?>
					       		<?php if ($value->status == 1) { ?>
					       			<button type="button" class="btn btn-success" onclick="reservacion(<?=$value->id?>)">Editar</button>
					       			<button type="button" class="btn btn-success" onclick="liberar(<?=$value->id?>)">Liberar</button>
					       		<?php } ?>

					           <button type="button" class="btn btn-danger" onclick="deleted(<?=$value->id?>)">Eliminar</button>
					        </div><!--/.nav-collapse -->
					    </div>
					  </div>
					</div>
				<?php }	?>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	      <span aria-hidden="true">&times;</span>
	    </button>
      <div class="modal-header">
        <h2 class="control-label" id="exampleModalLabel">Agregar Sala de Junta</h2>
      </div>
      <div class="modal-body">
      <?php echo form_open('Salas_juntas/add_sala', array('id' => 'save_sala_form', 'class' => '')); ?>
       <div class="form-group">
            <?php echo form_label('Nombre:', 'nombre', array('class' => 'control-label wide required')); ?>
            <?php echo form_input(array('class' => 'form-control', 'name' => 'nombre', 'id' => 'nombre', 'placeholder' => 'Nombre de la Sala de Juntas')); ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" id="submitResource">Guardar</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
	$(document).ready(function(){
		$("#save_sala_form").validate({
            submitHandler: function(form) {
               doSalaSubmit(form);
            },
            errorClass: "text-danger",
            "errorElement": "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.form-group').removeClass('has-success').addClass('has-error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.form-group').removeClass('has-error').addClass('has-success');
            },
            rules: {
                nombre: "required"
            },
            messages: {
                nombre: "Campo Requerido",
            }
        });
        var submitting = false;

        function doSalaSubmit(form) {
            if (submitting) return;
            submitting = true;
            $('#grid-loader').show();
            $(form).ajaxSubmit({
                success: function(response) {
                    $('#grid-loader').hide();
                    submitting = false;
                    $("html, body").animate({
                        scrollTop: 0
                    }, "slow");
                    $('#resourcemodal').modal('hide');
                    alert(response.message);
					window.location.href = '<?=site_url('salas_juntas')?>';
                },

                resetForm: true,
                dataType: 'json'
            });
        }
	});

	function reservacion(id){
    	window.location.href = '<?=site_url('salas_juntas/view')?>/' + id;
    }

    function deleted(id){
    	$.post('<?php echo site_url("salas_juntas/deleted_sala");?>', {
				id: id
		},function(response) {
			alert(response.message);
      		window.location.href = '<?=site_url('salas_juntas')?>';
		}, "json");
    }

    function liberar(id){
    	$.post('<?php echo site_url("salas_juntas/liberar");?>', {
				id: id
		},function(response) {
			alert(response.message);
      		window.location.href = '<?=site_url('salas_juntas')?>';
		}, "json");
    }
</script>

<?php $this->load->view("partial/footer");?>
