<?php
class Auth_Ctrl
{
    public $Usuario = null;
    public $Persona = null;
    public $Rol = null;
    public $Menu = null;

    public function __construct()
    {
        $this->Usuario = new Usuario();
        $this->Persona = new Persona();
        $this->Rol = new Rol();
        $this->Menu = new Menu();
    }

    // Función para enviar cabeceras CORS en cada respuesta
    private function setCorsHeaders()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    }

    public function registro($f3)
    {
        $this->setCorsHeaders(); // <-- cabeceras CORS

        $response = ['estado' => 0, 'mensaje' => ''];

        $this->Usuario->load(['correo = ?', $f3->get('POST.usuario')]);
        if ($this->Usuario->dry()) {
            $this->Persona->nombres = $f3->get('POST.nombres');
            $this->Persona->apellidos = $f3->get('POST.apellidos');
            $this->Persona->celular = $f3->get('POST.celular');
            $this->Persona->fechaNaci = $f3->get('POST.fechaNaci');
            $this->Persona->estado = true;

            $this->Persona->save();
            $id_per = $this->Persona->get('id');

            $this->Usuario->reset();
            $this->Usuario->id_persona = $id_per;
            $this->Usuario->id_rol = 2;
            $this->Usuario->correo = $f3->get('POST.usuario');
            $this->Usuario->clave = md5($f3->get('POST.clave'));
            $this->Usuario->estado = true;
            $this->Usuario->save();
            $response['estado'] = 1;
            $response['mensaje'] = 'Se registró con éxito';
        } else {
            $response['mensaje'] = 'El usuario se encuentra en uso';
        }

        echo json_encode($response);
    }

    public function login($f3)
    {
        $this->setCorsHeaders(); // <-- cabeceras CORS

        $response = ['estado' => 0, 'mensaje' => ''];
        $usuario = $f3->get('POST.usuario');
        $clave = md5($f3->get('POST.clave'));
        $this->Usuario->load(['correo = ? AND clave = ?', $usuario, $clave]);

        if ($this->Usuario->loaded() > 0) {
            $id_per = $this->Usuario->id_persona;
            $this->Persona->load(['id = ?', $id_per]);

            $persona = $this->Persona->cast();
            $persona['id_usr'] = $this->Usuario->id;
            $persona['usuario'] = $this->Usuario->correo;
            $persona['id_rol'] = $this->Usuario->id_rol;
            unset($persona['id'], $persona['estado']);

            $response['estado'] = 1;
            $response['mensaje'] = 'Información Encontrada';
            $response['info'] = $persona;
        } else {
            $response['mensaje'] = 'Credenciales incorrectas';
        }

        echo json_encode($response);
    }

    public function getMenu($f3)
    {
        $this->setCorsHeaders(); // <-- cabeceras CORS

        $rol = $f3->get('PARAMS.id');

        $query = "SELECT m.* FROM tb_accesos ac 
        LEFT JOIN tb_menu m ON ac.id_menu=m.id
        LEFT JOIN tb_roles r ON ac.id_rol=r.id
        WHERE r.id =" . $rol;

        $respuesta = $f3->DB->exec($query);

        echo json_encode([
            'estado' => count($respuesta) > 0 ? 1 : 0,
            'mensaje' => count($respuesta) > 0 ? 'Existe información' : 'No existe información',
            'data' => $respuesta
        ]);
    }
}
