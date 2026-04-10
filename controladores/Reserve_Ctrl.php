<?php
class Reserve_Ctrl
{
    public $Reserva = null;
    public $DetalleReserva = null;
    public $Habitacion = null;

    public function __construct()
    {
        $this->Reserva = new Reservacion();
        $this->DetalleReserva = new DetalleReserva();
        $this->Habitacion = new Habitacion();
    }

    public function nuevaReserva($f3)
    {
        $estado_actual = 'P';
        $this->Reserva->reset();
        $this->Reserva->set('id_usuario', $f3->get('POST.id_usuario'));
        $this->Reserva->set('id_habitacion', $f3->get('POST.id_habitacion'));
        $this->Reserva->set('codigo', $f3->get('POST.codigo'));
        $this->Reserva->set('fecha_reserva', date('Y-m-d'));
        $this->Reserva->set('fecha_ingreso', $f3->get('POST.fecha_ingreso'));
        $this->Reserva->set('fecha_salida', $f3->get('POST.fecha_salida'));
        $this->Reserva->set('comentarios', $f3->get('POST.comentarios'));
        $this->Reserva->set('total', $f3->get('POST.total'));
        $this->Reserva->set('estado_actual', $estado_actual);
        $this->Reserva->set('estado', true);
        $this->Reserva->save();

        $id = $this->Reserva->get('id_habitacion');

        $this->Habitacion->reset();
        $this->Habitacion->load(['id = ?', $id]);
        if (!$this->Habitacion->dry()) {
            $this->Habitacion->id_estado = 2;
            $this->Habitacion->save();
        }

        $servicios = json_decode($f3->get('POST.servicios'));
        if (count($servicios) > 0 && $id) {
            foreach ($servicios as $value) {
                $this->DetalleReserva->reset();
                $this->DetalleReserva->id_reservacion = $id;
                $this->DetalleReserva->id_servicio = $value;
                $this->DetalleReserva->estado = true;
                $this->DetalleReserva->save();
            }
        }
        echo json_encode([
            'estado' => $id != null ? 1 : 0,
            'mensaje' => $id != null ? 'Se registró con éxito' : 'No se pudo regitrar'
        ]);
    }

    public function ocuparHabitacion($f3)
    {
        $response = null;
        $codigo = $f3->get('POST.codigo');

        $this->Reserva->reset();
        $this->Reserva->load(['codigo=?', $codigo]);
        if (!$this->Reserva->dry()) {
            $idhab = $this->Reserva->id_habitacion;
            $std = $this->Reserva->estado_actual;
            if ($std == 'P') {
                $this->Reserva->estado_actual = 'O';
                $this->Habitacion->reset();
                $this->Habitacion->load(['id=?', $idhab]);
                $this->Habitacion->id_estado = 3;
                $this->Habitacion->save();
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
        $response = null;
        $id = $f3->get('POST.id');

        $this->Reserva->reset();
        $this->Reserva->load(['id=?', $id]);
        if (!$this->Reserva->dry()) {
            $this->Reserva->estado_actual = 'C';
            $this->Reserva->estado = false;
            $this->Reserva->save();
            $idhab = $this->Reserva->id_habitacion;
            $this->Habitacion->reset();
            $this->Habitacion->load(['id=?', $idhab]);
            $this->Habitacion->id_estado = 1;
            $this->Habitacion->save();

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
