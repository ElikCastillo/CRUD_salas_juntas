<?php

class Salas_juntas extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Sala_junta');
	}

	//listado de salas de juntas
	public function index()
	{
		$data = array();
		$sala_junta = new Sala_junta();
		$sala_junta->comprobar_fechas(); // verificacion de salas de juntas cada 30 segundos para liberarlas
		$data['all_sala'] = $sala_junta->get_all_salas(); //Obtener todas las salas que no esten eliminadas
		$this->load->view('salas_juntas/view_salas', $data); //carga de vista
	}

	//función para reservar y editar las salas de juntas
	function view($id=-1)
	{
		$data = array();
		$sala_junta = new Sala_junta();
		$data['sala'] = $sala_junta->get_info($id); // obtencion de la informacion en bd de la sala de juntas seleccionada
		$data['id'] = $id;  // guardado de id
		$this->load->view("salas_juntas/form",$data); //carga de la vista
	}

	//función para agregar salas de juntas al listado
	function add_sala(){
		//obtencion del nombre de la nueva sala a guardar
		$sala_data = array(
	         'nombre' => $this->input->post('nombre') ? $this->input->post('nombre') : NULL,
	     );

		$sala_junta = new Sala_junta();
		//comparacion si guardo la sala de juntas correctamente en la función del modelo
		if ($sala_junta->save_add_sala($sala_data)) {
			//Regreso de datos que se guardo correctamente
			 echo json_encode(array('success' => true, 'message' => 'Sala de Juntas Agregada'));
		}else{
			//Regreso de datos que no guardo correctamente
			echo json_encode(array('success' => false, 'message' => "Error", 'id' => -1));
		}
	}

	//Función para el guardado de los datos de la reservacion y/o actualización
	function save_sala($id = -1){
	  //Obtencion y cracion de arreglo de los datos mandados por el formulario
      $sala_data = array(
         'fecha_inicio' => $this->input->post('fecha_inicio') ? date('Y-m-d H:i:s', strtotime($this->input->post('fecha_inicio').''.$this->input->post('hora_inicio'))) : NULL,
         'fecha_fin' => $this->input->post('fecha_fin') ? date('Y-m-d H:i:s', strtotime($this->input->post('fecha_fin').''.$this->input->post('hora_fin'))) : NULL,
         'status' => 1,
      );

      $sala_junta = new Sala_junta();

      //comparacion si guardo la sala de juntas correctamente en la función del modelo
      if ($sala_junta->save_add_sala($sala_data, $id)) {
         $success_message = '';

         if ($id == -1) {
         	//Regreso de datos que se guardo correctamente
            $success_message = "Se ha Guardado Correctamente";
            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
         } else {
         	//Regreso de datos que se guardo correctamente
            $success_message = "Se ha Guardado Correctamente";
            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
         }
      } else {
      	//Regreso de datos que no guardo correctamente
		echo json_encode(array('success' => false, 'message' => "Error", 'id' => -1));
      }
	}

	//Función para eliminar Slas de juntass del listado
	function deleted_sala(){
		//obtención del id de la sala a eliminar
		$id = $this->input->post('id');
		$sala_junta = new Sala_junta();

		 //comparacion si eliminó la sala de juntas correctamente en la función del modelo
		if ($sala_junta->deleted_sala($id)) {
	         $success_message = '';

	         if ($id == -1) {
	         	//Regreso de datos que se guardo correctamente
	            $success_message = "Se ha Eliminado Correctamente";
	            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
	         } else {
	         	//Regreso de datos que se guardo correctamente
	            $success_message = "Se ha Eliminado Correctamente";
	            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
	         }
	      } else {
	      	//Regreso de datos que no guardo correctamente
			echo json_encode(array('success' => false, 'message' => "Error", 'id' => -1));
	    }
	}

	function liberar(){
		//obtención del id de la sala a liberar
		$id = $this->input->post('id');
		$sala_junta = new Sala_junta();

		//comparacion si cambio el estatus la sala de juntas correctamente en la función del modelo
		if ($sala_junta->liberar_sala($id)) {
	         $success_message = '';

	         if ($id == -1) {
	         	//Regreso de datos que se eliminó correctamente
	            $success_message = "Se ha Liberado la Sala de Juntas Correctamente";
	            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
	         } else {
	         	//Regreso de datos que se eliminó correctamente
	            $success_message = "Se ha Liberado la Sala de Juntas Correctamente";
	            echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id));
	         }
	      } else {
	      	//Regreso de datos que no guardo correctamente
			echo json_encode(array('success' => false, 'message' => "Error", 'id' => -1));
	      }
	}

	//funcion para sumarle dos horas a la hora inicial seleccionada
	function horas(){
		$mifecha= date('Y-m-d H:i:s', strtotime($this->input->post('fecha').''.$this->input->post('hora')));
		$NuevaFecha = strtotime ( '+2 hour' , strtotime ($mifecha) ) ;
		$fecha = date ('Y-m-d', $NuevaFecha);
		$hora = date ('H:i', $NuevaFecha);

		echo json_encode(array('fecha' => $fecha,'hora' => $hora));
	}
}
