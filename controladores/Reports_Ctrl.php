<?php
class Reports_Ctrl
{
    public $EstadoHabitacion = null;
    public $Habitacion = null;

    public function __construct()
    {
        $this->EstadoHabitacion = new EstadoHabitacion();
        $this->Habitacion = new Habitacion();
    }


    public function estadoDeHabitaciones($f3)
    {
        $estados = $this->EstadoHabitacion->select('*');
        $items = array();

        foreach ($estados as $value) {
            $this->Habitacion->reset();
            $cant = $this->Habitacion->count(['id_estado=?', $value->id]);
            $items[] = [
                'detalle' => $value->detalle,
                'cantidad' => $cant
            ];
        }

        echo json_encode($items);
    }

    public function habitacionesXUser($f3)
    {
        $id = $f3->get('PARAMS.id');

        $query = "SELECT r.*, th.nombre, uh.ubicacion, h.numero  FROM tb_reservaciones r
        INNER JOIN tb_habitaciones h ON r.id_habitacion=h.id
        INNER JOIN tb_ubicacion_hab uh ON h.id_tipo=uh.id
        INNER JOIN tb_tipo_habitacion th ON h.id_tipo=th.id
        WHERE r.estado_actual='P' AND r.id_usuario=$id;";

        $respuesta = $f3->DB->exec($query);

        echo json_encode([
            'estado' => count($respuesta) > 0 ? 1 : 0,
            'mensaje' => count($respuesta) > 0 ? 'Existe información' : 'No existe información',
            'data' => $respuesta
        ]);
    }

    public function infohabitaciones($f3)
    {
        $id = $f3->get('PARAMS.id');
        
        $query = "SELECT h.id, th.nombre, uh.ubicacion, h.numero,
        h.id_estado, eh.detalle
        FROM tb_habitaciones h 
        INNER JOIN tb_tipo_habitacion th ON h.id_tipo=th.id
        INNER JOIN tb_ubicacion_hab uh ON h.piso=uh.id
        INNER JOIN tb_estado_hab eh ON h.id_estado=eh.id
        WHERE eh.id=$id;";

        $respuesta = $f3->DB->exec($query);

        echo json_encode([
            'estado' => count($respuesta) > 0 ? 1 : 0,
            'mensaje' => count($respuesta) > 0 ? 'Existe información' : 'No existe información',
            'data' => $respuesta
        ]);
    }
}
