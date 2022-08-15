<?php
class Sala_junta extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }

    //función para obtener todas las salas de juntas que no estan eliminadas revisando la hora final, en caso la fecha actual sea mayor a la hora final de la sala, llama mandar otra funcion para liverarla
    function comprobar_fechas()
    {
        $this->db->select('*');
        $this->db->from('salas_juntas');
        $this->db->where('deleted', 0);
        $salas = $this->db->get()->result();
        $mDate = new DateTime();
        $mDate->setTimezone(new DateTimeZone('Mexico/General'));  //
        $fecha_actual=$mDate->format("Y-m-d H:i:s");
            foreach ($salas as $key => $value){
                $fecha = date('Y-m-d H:i:s', strtotime($value->fecha_fin));
                if ($fecha_actual > $fecha ) {
                    $this->liberar_sala($value->id);
                }
        }
    }

    //funcióon para obtener todas las salas de reunion que no esten eliminadas
    function get_all_salas()
    {
        $this->db->select('*');
        $this->db->from('salas_juntas');
        $this->db->where('deleted', 0);
        return $this->db->get()->result();
    }

    //función para obtener la informacion de una sala de juntas en específico
    function get_info($id)
    {
        $this->db->select('*');
        $this->db->from('salas_juntas');
        $this->db->where('id', $id);
        $this->db->where('deleted', 0);
        return $this->db->get()->result();
    }

    //función para el guardado de la reservación o edicion de la sala de juntas
    function save_add_sala($sala_data, $id = -1)
    {
        if ($id == -1) {
            $id = FALSE;
        }
        if (!$id) {

            if ($this->db->insert('salas_juntas', $sala_data)) {
                return true;
            }
            return false;
        }
        $this->db->where('id', $id);
        if ($this->db->update('salas_juntas', $sala_data)) {
            return true;
        }
         return false;
    }

     //función para el eliminado de la sala de juntas
    function deleted_sala($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('salas_juntas', array('deleted' => 1))) {
            return true;
        }
    }

    //función para liberar y cambiar el estatus de la sala de juntas
    function liberar_sala($id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('salas_juntas', array('status' => 0, 'fecha_fin' => NULL, 'fecha_inicio' => NULL))) {
            return true;
        }
    }
}
