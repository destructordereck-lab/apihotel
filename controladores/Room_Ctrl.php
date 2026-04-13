<?php
class Room_Ctrl
{
    public $TipoHabitacion = null;
    public $Servicio = null;
    public $Reserva = null;
    public $Habitacion = null;

    public function __construct()
    {
        $db = \Base::instance()->get('DB');
        $this->TipoHabitacion = new TipoHabitacion($db);
        $this->Servicio       = new Servicio($db);
        $this->Reserva        = new Reservacion($db);
        $this->Habitacion     = new Habitacion($db);
    }


    public function getTipos($f3)
    {
        $resultado = $this->TipoHabitacion->select('*', 'estado=1');
        $items = array();

        foreach ($resultado as $value) {
            $items[] = $value->cast();
        }

        echo json_encode([
            'estado' => count($items) > 0 ? 1 : 0,
            'mensaje' => count($items) > 0 ? 'Consulta con éxito' : 'No existen datos para mostrar',
            'data' => $items
        ]);
    }

    public function getServicios($f3)
    {
        $resultado = $this->Servicio->select('*', 'estado=1');
        $items = array();

        foreach ($resultado as $value) {
            $items[] = $value->cast();
        }

        echo json_encode([
            'mensaje' => count($items) > 0 ? 'Consulta con éxito' : 'No existen datos para mostrar',
            'estado' => count($items) > 0 ? 1 : 0,
            'data' => $items
        ]);
    }

    public function getPisos($f3)
    {
        $id = $f3->get('PARAMS.id');
        $cadenaSQL = "SELECT DISTINCT(uh.id), uh.ubicacion FROM tb_ubicacion_hab uh
        INNER JOIN tb_habitaciones h ON h.piso=uh.id
        WHERE h.id_estado=1 AND h.id_tipo=$id";
        $items = $f3->DB->exec($cadenaSQL);
        echo json_encode([
            'estado' => count($items) > 0 ? 1 : 0,
            'mensaje' => count($items) > 0 ? 'Consulta con éxito' : 'No hay habitaciones disponibles',
            'data' => $items
        ]);
    }

    public function getNumerosHabitacion($f3)
    {
        $idtipo = $f3->get('PARAMS.idtipo');
        $idpiso = $f3->get('PARAMS.idpiso');

        $cadenaSQL = "SELECT h.id, h.numero, uh.ubicacion FROM tb_ubicacion_hab uh
        INNER JOIN tb_habitaciones h ON h.piso=uh.id
        WHERE h.id_estado=1 AND h.id_tipo=$idtipo AND h.piso=$idpiso";
        $items = $f3->DB->exec($cadenaSQL);
        echo json_encode([
            'estado' => count($items) > 0 ? 1 : 0,
            'mensaje' => count($items) > 0 ? 'Consulta con éxito' : 'No hay habitaciones disponibles',
            'data' => $items
        ]);
    }

    public function getHabitacionById($f3)
    {
        $id = $f3->get('PARAMS.id');

        $cadenaSQL = "SELECT h.id, th.nombre, th.precioxnoche,
        uh.ubicacion, h.numero
        FROM tb_habitaciones h 
        INNER JOIN tb_tipo_habitacion th ON h.id_tipo = th.id
        INNER JOIN tb_ubicacion_hab uh ON h.piso = uh.id
        WHERE h.id=$id";

        $items = $f3->DB->exec($cadenaSQL);
        echo json_encode([
            'estado' => count($items) > 0 ? 1 : 0,
            'mensaje' => count($items) > 0 ? 'Consulta con éxito' : 'No hay información',
            'data' => $items[0]
        ]);
    }

    public function ponerEnMantenimiento($f3) {
        $response = null;
        $idHab = $f3->get('POST.id_habitacion');
        $this->Habitacion->reset();
        $this->Habitacion->load(['id=?',$idHab]);

        if(!$this->Habitacion->dry()) {
            $this->Habitacion->id_estado = 4;
            $this->Habitacion->save();
            $this->Reserva->reset();
            $this->Reserva->load(['id_habitacion=? AND estado_actual=?',$idHab,'O']);
            if(!$this->Reserva->dry()){
                $this->Reserva->estado_actual = 'T';
                $this->Reserva->save();
            }

            $response = [
                'estado' => 1,
                'mensaje' => 'La habitación pasó a mantenimiento'
            ];
        } else {
            $response = [
                'estado' => 0,
                'mensaje' => 'Ocurrió un problema'
            ];
        }

        echo json_encode($response);
    }

    public function estaDisponible($f3) {
        $response = null;
        $idHab = $f3->get('POST.id_habitacion');
        $this->Habitacion->reset();
        $this->Habitacion->load(['id=?',$idHab]);

        if(!$this->Habitacion->dry()) {
            $this->Habitacion->id_estado = 1;
            $this->Habitacion->save();

            $response = [
                'estado' => 1,
                'mensaje' => 'La habitación está disponible'
            ];
        } else {
            $response = [
                'estado' => 0,
                'mensaje' => 'Ocurrió un problema'
            ];
        }
        
        echo json_encode($response);
    }
}