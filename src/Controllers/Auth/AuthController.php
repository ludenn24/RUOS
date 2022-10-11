<?php

namespace App\Controllers\Auth;

use App\Models\Modelos\Usuarios;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

Class AuthController extends Controller
{

    //VARIANTE DE ALERTAS
    function show($type,$string)
    {
        $div='';
        if($type==1){
            $div='<div class="alert alert-danger" role="alert">
                  <a href="#" class="alert-link">'.$string.'</a>
                </div>';
        }
        if($type==2){
            $div='<div id="login-status" class="warn-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><i class="fa fa-exclamation-triangle"></i></div>
                    <div id="login-status-message">'.$string.'</div>
                </div>
            </div>
        </div>';
        }

        if($type==3){
            $div='<div id="login-status" class="info-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><span class="login-status-icon"></span></div>
                    <div id="login-status-message">'.$string.'</div>
                </div>
            </div>
        </div>';
        }

        if($type==4){
            $div='<div class="alert alert-success"><i class="fa fa-check"></i> '.$string.'</div>';
        }
        return $div;
    }

    public function getAll($limit = -1, $page = -1)
    {
        if ($limit < 0 && $page < 0) {
            $data = $this->db->from($this->table)
                ->orderBy('codigo DESC')
                ->fetchAll();
        } else {
            $data = $this->db->from($this->table)
                ->limit($limit)
                ->offset($page)
                ->orderBy('codigo DESC')
                ->fetchAll();
        }

        $total = $this->db->from($this->table)
            ->select('COUNT(*) Total')
            ->fetch()
            ->Total;

        return [
            'total' => $total,
            'data'  => $data

        ];
    }

    public function usuario()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $usuario = Usuarios::where('codigo', $ses_codigo)->first();
        return $usuario;
    }

    public function check()
    {
        return isset($_SESSION['dni']);
    }

    public function attempt($dni, $clave)
    {
        $usuario = Usuarios::where('dni', $dni)->first();
        if (!$usuario) {
            return false;
        }
        if (password_verify($clave, $usuario->clave)) {
            $_SESSION['codigo'] = $usuario->codigo;
            $_SESSION['dni'] = $usuario->dni;
			$_SESSION['tipo_user'] = $usuario->tipo_user;
            $_SESSION['distrito'] = $usuario->distrito;
            return true;
        }
        return false;
    }

    public function logout()
    {
        unset($_SESSION['dni']);
    }

    public function getSignOut($request, $response)
    {
        $this->AuthController->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    }
    
    public function getSignOutAdm($request, $response)
    {
        $this->AuthController->logout();
        return $response->withRedirect($this->router->pathFor('admin.signin'));
    }

    public function getSignIn($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function getSignUp($request, $response)
    {

        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignIn($request, $response)
    {
        $auth = $this->AuthController->attempt(
            $request->getParam('dni'),
            $request->getParam('clave')
        );

        if (!$auth) {
            $this->flash->addMessage('error', 'El DNI o contraseña son incorrectos');
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }
        $this->flash->addMessage('info', 'Haz iniciado sesion correctamente');
        return $response->withRedirect($this->router->pathFor('auth.documento'));

    }

    public function postSignUp($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'dni' => v::noWhitespace()->notEmpty()->dniDisponible(),
            'nombres' => v::notEmpty(),
            'apellidopaterno' => v::notEmpty(),
            'apellidomaterno' => v::notEmpty(),
            'email' => v::noWhitespace()->notEmpty()->email(),
            'tel' => v::notEmpty(),
            'casa' => v::notEmpty(),
            'distrito' => v::notEmpty(),
            'direccion' => v::notEmpty(),
            'naci' => v::noWhitespace()->notEmpty(),
            'clave' => v::noWhitespace()->notEmpty(),
        ]);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $usuario = Usuarios::create([
            'usuario' => $request->getParam('dni'),
            'tipo_user' => 1,
            'nombres' => $request->getParam('nombres'),
            'apellidopaterno' => $request->getParam('apellidopaterno'),
            'apellidomaterno' => $request->getParam('apellidomaterno'),
            'correo' => $request->getParam('email'),
            'telefono' => $request->getParam('tel'),
            'casa' => $request->getParam('casa'),
            'dni' => $request->getParam('dni'),
            'distrito' => $request->getParam('distrito'),
            'direccion' => $request->getParam('direccion'),
            'fecha_nacimiento' => $request->getParam('naci'),
            'clave' => password_hash($request->getParam('clave'), PASSWORD_DEFAULT),
        ]);

        $this->flash->addMessage('info', 'Tu te haz registrado exitosamente');
        $this->AuthController->attempt($usuario->dni, $request->getParam('clave'));
        return $response->withRedirect($this->router->pathFor('auth.solicitud'));
    }

    //ACTUALIZAR SOLICITANTE
    public function ActualizarSolicitante($request, $response, $args){
        $codigo = $request->getParam('cod_solicitante');
        Usuarios::where('codigo', '=', $codigo)->update(['casa' => $request->getParam('sol_casa')]);
        $mensaje = $codigo . '?' . $this->show('4', 'Lo datos del solicitante se han guardado correctamnente');
        echo json_encode($mensaje);
    }
}
