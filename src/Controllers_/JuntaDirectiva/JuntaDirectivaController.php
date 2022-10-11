<?php

namespace App\Controllers\JuntaDirectiva;

use App\Models\Modelos\JuntasDirectivas;
use App\Models\Modelos\Representantes;
use App\Models\Modelos\Puestos;
use App\Controllers\Controller;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\Solicitudes;
use Respect\Validation\Validator as v;

Class JuntaDirectivaController extends Controller {

    public function listapuestos() {
        return Puestos::where('estado', '1')->get();
    }

    //VARIANTE DE ALERTAS
    function show($type, $string) {
        $div = '';
        if ($type == 1) {
            $div = '<div class="alert alert-danger" role="alert">
                  <a href="#" class="alert-link">' . $string . '</a>
                </div>';
        }
        if ($type == 2) {
            $div = '<div id="login-status" class="warn-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><i class="fa fa-exclamation-triangle"></i></div>
                    <div id="login-status-message">' . $string . '</div>
                </div>
            </div>
        </div>';
        }

        if ($type == 3) {
            $div = '<div id="login-status" class="info-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><span class="login-status-icon"></span></div>
                    <div id="login-status-message">' . $string . '</div>
                </div>
            </div>
        </div>';
        }

        if ($type == 4) {
            $div = '<div class="alert alert-success"><i class="fa fa-check"></i> ' . $string . '</div>';
        }
        return $div;
    }

    //REGISTRAR MIEMBRO DE JUNTA DIRECTIVA
    public function Registrar($request, $response, $args) {
        $buscaexistente = JuntasDirectivas::where('dni', '=', $request->getParam('dni'))->where('cod_org', '=', $request->getParam('cod_org'))->where('estado', '=', '1')->get();
        if (count($buscaexistente) == 0) {
            $miembro = JuntasDirectivas::create([
                        'cod_org' => $request->getParam('cod_org'),
                        'cod_puesto' => $request->getParam('cod_puesto'),
                        'descripcion_cargo' => $request->getParam('descripcion_cargo'),
                        'dni' => $request->getParam('dni'),
                        'nombre' => $request->getParam('nombre'),
                        'apellido_pat' => $request->getParam('apellido_pat'),
                        'apellido_mat' => $request->getParam('apellido_mat'),
                        'fecha_nacimiento' => $request->getParam('fecha_nacimiento'),
                        'sexo' => $request->getParam('sexo'),
                        'departamento' => $request->getParam('departamento'),
                        'provincia' => $request->getParam('provincia'),
                        'distrito' => $request->getParam('distrito'),
                        'foto' => $request->getParam('foto'),
            ]);
            $dato = $miembro->id;
            $org = $request->getParam('cod_org');
            $msm['org'] = $org;
            $msm['message'] = $dato . '?' . $this->show('4', 'Miembro registrado correctamente.');
            echo json_encode($msm);
        } else {
            $msm['response'] = 'si';
            $msm['message'] = $this->show('1', 'El dni del miembro ya se encuentra registrado.');
            echo json_encode($msm);
        }
    }

    //OBTENER JUNTADIRECTIVA POR ID
    public function getById($request, $response, $args) {
        try {
            $codigo = $request->getParam('cod_org');
            $data = JuntasDirectivas::select('tb_junta_directiva.*', 'tb_puestos.puesto')
                            ->join('tb_puestos', 'tb_junta_directiva.cod_puesto', '=', 'tb_puestos.codigo')
                            ->where('tb_junta_directiva.cod_org', $codigo)
                            ->where('tb_junta_directiva.estado', '1')
                            ->orderBy('tb_junta_directiva.codigo', 'DESC')->get();
            $arreglo["data"] = $data;

            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //ELIMINAR MIEMBRO DE JUNTA DIRECTIVA
    public function Eliminar($request, $response, $args) {
        try {
            $codigo = $request->getParam('codigo');
            Representantes::where('codigo', '=', $codigo)->update([
                'estado' => '2',
            ]);
            $msm['response'] = 'si';
            return json_encode($msm);
        } catch (ErrorException $e) {
            $data = "Hubo un error.";
            return $this->response->withJson($data, 200);
        }
    }

    public function juntadirectiva() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();

        if (!$solicitud) {
            $organizacion = Organizaciones::where('iduser', $ses_codigo)->first();
        } else {
            $cod_org = isset($solicitud->cod_org) ? $solicitud->cod_org : '';
            $organizacion = Organizaciones::where('codigo', $cod_org)->first();
        }


        if (!isset($organizacion)) {
            return false;
        }

        $codigo_organizacion = $organizacion->codigo;

        $junta = JuntasDirectivas::select('tb_junta_directiva.*', 'tb_puestos.puesto')
                        ->join('tb_puestos', 'tb_junta_directiva.cod_puesto', '=', 'tb_puestos.codigo')
                        ->where('tb_junta_directiva.cod_org', $codigo_organizacion)
                        ->orderBy('tb_junta_directiva.codigo', 'DESC')->get();

        return $junta;
    }

    public function getJuntaDirectiva($request, $response) {
        return $this->view->render($response, 'auth/documento.twig');
    }

    public function postJuntaDirectiva($request, $response) {
        $validationini = $this->validator->validate($request, [
            'cod_org' => v::notEmpty(),
        ]);


        if ($validationini->failed()) {
            $this->flash->addMessage('error', 'Antes debe registrar a de su organización');
            return $response->withRedirect($this->router->pathFor('auth.organizacion'));
        }

        $auth = JuntasDirectivas::where('cod_org', $request->getParam('cod_org'))->where('dni', $request->getParam('dni'))->first();

        if ($auth) {
            $this->flash->addMessage('error_dni_registrado', 'El responsable ya se encuentra registrado');
            return $response->withRedirect($this->router->pathFor('auth.organizacion'));
        }

        $validation = $this->validator->validate($request, [
            'cod_org' => v::notEmpty(),
            'cod_puesto' => v::notEmpty(),
            'dni' => v::notEmpty(),
            'nombre' => v::notEmpty(),
            'apellido_pat' => v::notEmpty(),
            'apellido_mat' => v::notEmpty(),
            'fecha_nacimiento' => v::notEmpty(),
            'sexo' => v::notEmpty(),
                ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.organizacion'));
        }

        JuntasDirectivas::create([
            'cod_org' => $request->getParam('cod_org'),
            'cod_puesto' => $request->getParam('cod_puesto'),
            'descripcion_cargo' => $request->getParam('descripcion_cargo'),
            'dni' => $request->getParam('dni'),
            'nombre' => $request->getParam('nombre'),
            'apellido_pat' => $request->getParam('apellido_pat'),
            'apellido_mat' => $request->getParam('apellido_mat'),
            'fecha_nacimiento' => $request->getParam('fecha_nacimiento'),
            'sexo' => $request->getParam('sexo'),
            'departamento' => $request->getParam('departamento'),
            'provincia' => $request->getParam('provincia'),
            'distrito' => $request->getParam('distrito'),
            'foto' => $request->getParam('foto'),
        ]);

        $this->flash->addMessage('registro_responsable', 'Responsable registrado correctamente.');
        return $response->withRedirect($this->router->pathFor('auth.organizacion'));
    }

    public function putJuntaDirectiva($request, $response) {
        $validation = $this->validator->validate($request, [
            'nom_org' => v::notEmpty(),
            'obj_denomina' => v::notEmpty(),
            'obj_denomina' => v::notEmpty(),
            'fecha_cons' => v::notEmpty(),
            'num_miem' => v::noWhitespace()->notEmpty(),
            'fines' => v::notEmpty(),
            'domicilio_org' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        }

        // Conseguimos el objeto
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $organizacion = Organizaciones::where('isol', '=', $ses_codigo)->first();

        // Si existe

        if (count($organizacion) >= 1) {
            $organizacion = Organizaciones::where('iduser', '=', $ses_codigo)->update([
                'nombre_org' => $request->getParam('nom_org'),
            ]);
        }

        $this->flash->addMessage('info', 'Datos actualizados correctamente');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    public function deleteJuntaDirectiva($request, $response) {
        JuntasDirectivas::where('codigo', '=', $request->getParam('codigo'))->delete();
        //$this->flash->addMessage('info', 'Datos actualizados correctamente');
        return $response->withRedirect($this->router->pathFor('auth.organizacion'));
    }

    ///////////////////////////////////////REPRESENTANTES//////////////////////////////////////////////////////////

    public function RegistrarRepresentante($request, $response) {

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        $codsolicitud = $solicitud->codigo;

        $auth = Representantes::where('cod_sol', $codsolicitud)->where('dni', $request->getParam('dni'))->first();

        if ($auth) {
            $this->flash->addMessage('error_dni_registrado', 'El responsable ya se encuentra registrado');
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        }

        $validation = $this->validator->validate($request, [
            'cod_puesto' => v::notEmpty(),
            'dni' => v::notEmpty(),
            'nombre' => v::notEmpty(),
            'apellido_pat' => v::notEmpty(),
            'apellido_mat' => v::notEmpty(),
            'sexo' => v::notEmpty(),
            ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        }

        Representantes::create([
            'cod_sol' => $codsolicitud,
            'cod_puesto' => $request->getParam('cod_puesto'),
            'descripcion_cargo' => $request->getParam('descripcion_cargo'),
            'dni' => $request->getParam('dni'),
            'nombre' => $request->getParam('nombre'),
            'apellido_pat' => $request->getParam('apellido_pat'),
            'apellido_mat' => $request->getParam('apellido_mat'),
            'fecha_nacimiento' => $request->getParam('fecha_nacimiento'),
            'sexo' => $request->getParam('sexo'),
            'departamento' => $request->getParam('departamento'),
            'provincia' => $request->getParam('provincia'),
            'distrito' => $request->getParam('distrito'),
            'foto' => $request->getParam('foto'),
        ]);

        $this->flash->addMessage('registro_responsable', 'Responsable registrado correctamente.');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    public function GetRepresentantes() {
        
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
       

        if (!isset($solicitud)) {
            return false;
        }
        
        $codsolicitud = $solicitud->codigo;

        $junta = Representantes::select('tb_representantes.*', 'tb_puestos.puesto')
                        ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
                        ->where('tb_representantes.cod_sol', $codsolicitud)
                        ->orderBy('tb_representantes.codigo', 'DESC')->get();

        return $junta;
    }
    
    
    public function DeleteRepresentante($request, $response) {
        Representantes::where('codigo', '=', $request->getParam('codigo'))->delete();
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }
    
        //OBTENER JUNTADIRECTIVA POR ID
    public function GetRepresentantesXSolicitud($request, $response, $args) {
        try {
            $codigo = $request->getParam('cod_sol');
            $data = Representantes::select('tb_representantes.*', 'tb_puestos.puesto')
                            ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
                            ->where('tb_representantes.cod_sol', $codigo)
                            ->where('tb_representantes.estado', '1')
                            ->orderBy('tb_representantes.codigo', 'DESC')->get();
            $arreglo["data"] = $data;

            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }
    
     //REGISTRAR MIEMBRO DE JUNTA DIRECTIVA
    public function RegistrarRepresentanteDistrital($request, $response, $args) {
        $buscaexistente = Representantes::where('dni', '=', $request->getParam('dni'))->where('cod_sol', '=', $request->getParam('cod_sol'))->where('estado', '=', '1')->get();
        if (count($buscaexistente) == 0) {
            $miembro = Representantes::create([
                        'cod_sol' => $request->getParam('cod_sol'),
                        'cod_puesto' => $request->getParam('cod_puesto'),
                        'descripcion_cargo' => $request->getParam('descripcion_cargo'),
                        'dni' => $request->getParam('dni'),
                        'nombre' => $request->getParam('nombre'),
                        'apellido_pat' => $request->getParam('apellido_pat'),
                        'apellido_mat' => $request->getParam('apellido_mat'),
                        'fecha_nacimiento' => $request->getParam('fecha_nacimiento'),
                        'sexo' => $request->getParam('sexo'),
                        'departamento' => $request->getParam('departamento'),
                        'provincia' => $request->getParam('provincia'),
                        'distrito' => $request->getParam('distrito'),
            ]);
            $dato = $miembro->id;
            $org = $request->getParam('cod_sol');
            $msm['org'] = $org;
            $msm['message'] = $dato . '?' . $this->show('4', 'Miembro registrado correctamente.');
            echo json_encode($msm);
        } else {
            $msm['response'] = 'si';
            $msm['message'] = $this->show('1', 'El dni del miembro ya se encuentra registrado.');
            echo json_encode($msm);
        }
    }
    

}
