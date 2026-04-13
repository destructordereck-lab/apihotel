<?php
class Reserve_Ctrl
{
    public $Reserva = null;
    public $DetalleReserva = null;
    public $Habitacion = null;

    public function __construct()
    {
        $db = \Base::instance()->get('DB');

        $this->Reserva        = new Reservacion($db);
        $this->DetalleReserva = new DetalleReserva($db);
        $this->Habitacion     = new Habitacion($db);
    }

    private function setCorsHeaders()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    }

    public function nuevaReserva($f3)
    {
        $this->setCorsHeaders();

        // Aceptar JSON o form-urlencoded
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && is_array($input)) {
            foreach ($input as $k => $v) $f3->set("POST.$k", $v);
        }

        // Validaciones mínimas
        $id_usuario    = $f3->get('POST.id_usuario');
        $id_habitacion = $f3->get('POST.id_habitacion');
        $fecha_ingreso = $f3->get('POST.fecha_ingreso');
        $fecha_salida  = $f3->get('POST.fecha_salida');

        if (!$id_usuario || !$id_habitacion || !$fecha_ingreso || !$fecha_salida) {
            http_response_code(400);
            echo json_encode(['estado' => 0, 'mensaje' => 'Faltan datos requeridos']);
            return;
        }

        $this->Reserva->reset();
        $this->Reserva->id_usuario    = $id_usuario;
        $this->Reserva->id_habitacion = $id_habitacion;
        $this->Reserva->codigo        = $f3->get('POST.codigo') ?? uniqid('R-');
        $this->Reserva->fecha_reserva = date('Y-m-d');
        $this->Reserva->fecha_ingreso = $fecha_ingreso;
        $this->Reserva->fecha_salida  = $fecha_salida;
        $this->Reserva->comentarios   = $f3->get('POST.comentarios');
        $this->Reserva->total         = $f3->get('POST.total') ?? 0;
        $this->Reserva->estado_actual = 'P';
        $this->Reserva->estado        = true;
        $this->Reserva->save();

        $id_reservacion = $this->Reserva->get('id');

        // Actualizar estado de la habitación
        $this->Habitacion->reset();
        $this->Habitacion->load(['id = ?', $id_habitacion]);
        if (!$this->Habitacion->dry()) {
            $this->Habitacion->id_estado = 2;
            $this->Habitacion->save();
        }

        // Procesar servicios
        $servicios_raw = $f3->get('POST.servicios');
        $servicios = [];
        if (is_string($servicios_raw)) {
            $decoded = json_decode($servicios_raw, true);
            if (is_array($decoded)) $servicios = $decoded;
            else $servicios = array_filter(array_map('trim', explode(',', $servicios_raw)));
        } elseif (is_array($servicios_raw)) {
            $servicios = $servicios_raw;
        }

        if (!empty($servicios) && $id_reservacion) {
            foreach ($servicios as $value) {
                $this->DetalleReserva->reset();
                $this->DetalleReserva->id_reservacion = $id_reservacion;
                $this->DetalleReserva->id_servicio    = $value;
                $this->DetalleReserva->estado         = true;
                $this->DetalleReserva->save();
            }
        }

        echo json_encode([
            'estado' => $id_reservacion ? 1 : 0,
            'mensaje' => $id_reservacion ? 'Se registró con éxito' : 'No se pudo registrar',
            'id_reservacion' => $id_reservacion
        ]);
    }

    public function ocuparHabitacion($f3)
    {
        $this->setCorsHeaders();

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && is_array($input)) {
            foreach ($input as $k => $v) $f3->set("POST.$k", $v);
        }

        $codigo = $f3->get('POST.codigo');
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['estado' => 0, 'mensaje' => 'Código requerido']);
            return;
        }

        $this->Reserva->reset();
        $this->Reserva->load(['codigo=?', $codigo]);
        if (!$this->Reserva->dry()) {
            $idhab = $this->Reserva->id_habitacion;
            $std = $this->Reserva->estado_actual;
            if ($std == 'P') {
                $this->Reserva->estado_actual = 'O';
                $this->Habitacion->reset();
                $this->Habitacion->load(['id=?', $idhab]);
                if (!$this->Habitacion->dry()) {
                    $this->Habitacion->id_estado = 3;
                    $this->Habitacion->save();
                }
                $this->Reserva->save();
                $response = [
                    'estado' => 1,
                    'mensaje' => 'El estado de la reservación se actualizó'
                ];
            } else {
                $response = [
                    'estado' => 0,
                    'mensaje' => 'La reservación ya fue actualizada'
                ];
            }
        } else {
            $response = [
                'estado' => 0,
                'mensaje' => 'El código de reserva es incorrecto'
            ];
        }

        echo json_encode($response);
    }

    public function cancelarReservacion($f3)
    {
        $this->setCorsHeaders();

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && is_array($input)) {
            foreach ($input as $k => $v) $f3->set("POST.$k", $v);
        }

        $id = $f3->get('POST.id');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['estado' => 0, 'mensaje' => 'Id requerido']);
            return;
        }

        $this->Reserva->reset();
        $this->Reserva->load(['id=?', $id]);
        if (!$this->Reserva->dry()) {
            $this->Reserva->estado_actual = 'C';
            $this->Reserva->estado = false;
            $this->Reserva->save();
            $idhab = $this->Reserva->id_habitacion;
            $this->Habitacion->reset();
            $this->Habitacion->load(['id=?', $idhab]);
            if (!$this->Habitacion->dry()) {
                $this->Habitacion->id_estado = 1;
                $this->Habitacion->save();
            }

            $response = [
                'estado' => 1,
                'mensaje' => 'Reservación cancelada'
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
