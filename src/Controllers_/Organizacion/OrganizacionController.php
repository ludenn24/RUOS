<?php

namespace App\Controllers\Organizacion;

use App\Models\Modelos\Documentos;
use App\Models\Modelos\Organizaciones;
use App\Controllers\Controller;
use App\Models\Modelos\Distritos;
use App\Models\Modelos\Solicitudes;
use App\Models\Modelos\TipoOrganizacionDen;
use App\Models\Modelos\TipoOrganizaciones;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;

Class OrganizacionController extends Controller
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


    private function SuccessInsert($sms){
        $string="";
        $string="<h3 style='color:#157D09'>";
        $string.="<i class='fa fa-check-circle' ></i>";
        $string.=$sms;
        $string.="</h3>";
        return $string;
    }

    public function viewOrgnizacion($request, $response)
    {
        return $this->view->render($response, 'auth/organizacion.twig');
    }

    public function viewEditOrgnizacion($request, $response)
    {
        return $this->view->render($response, 'organizacion/editar.twig');
    }

    public function getOrganizacion($request, $response)
    {
        return $this->view->render($response, 'auth/documento.twig');
    }

    public function organizacion()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        return Organizaciones::where('iduser', $ses_codigo  )->first();
    }

    public function getUbicaciones($request, $response, $args)
    {
        try {
              $data = Organizaciones::select('tb_organizacion.codigo',
                  'tb_organizacion.nombre_org',
                  'tb_organizacion.fines',
                  'tb_organizacion.num_org',
                  'tb_organizacion.domicilio_org',
                  'tb_organizacion.num_miem',
                  'tb_organizacion.latitud',
                  'tb_organizacion.longitud',
                  'tb_tipos_orgs.descripcion')
                    ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                    ->join(DB::raw('(SELECT max(codigo) as codigo
                              FROM tb_solicitudes 
                              where cod_org=tb_organizacion.codigo
                              GROUP by tb_organizacion.codigo
                            ) m'), function($join){
                      $join->on("tb_organizacion.codigo","=","m.cod_org");
                  })
              ->where('tb_organizacion.distrito',1251)
              ->orderBy('tb_organizacion.codigo', 'DESC')->get();
                return $this->response->withJson($data, 200);


        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    public function getOrganizaciones()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $data = Organizaciones::select('tb_organizacion.*', 'tb_tipos_orgs.descripcion')
            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
            ->where('tb_organizacion.iduser', $ses_codigo)->orderBy('tb_organizacion.codigo', 'DESC')->get();
        return $data;
    }


    public function getDsitritos()
    {
        return Distritos::get();
    }

    //OBTENER ORGANIZACIÓN POR ID
    public function getById($request, $response, $args)
    {
        try {
            $codigo = $request->getParam('codigo');
            $data = Organizaciones::where('codigo','=',$codigo)->get();
            return $this->response->withJson($data, 200);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }

    //OBTENER ORGANIZACIÓN POR ID
    public function gerOrganizacionTipo($request, $response, $args)
    {
        try {
            $codigo = $request->getParam('codigo');
            $data = Organizaciones::select('tb_organizacion.*', 'tb_tipos_orgs.descripcion')
            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
            ->where('tb_organizacion.codigo','=',$codigo)
            ->get();
            return $this->response->withJson($data, 200);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }

    //ACTUALIZAR ORGANIZACIÓN
    public function Actualizar($request, $response, $args)
    {
            $codigo=$request->getParam('codigoorganizacion');
            $buscaexistente = Organizaciones::where('nombre_org', '=', $request->getParam('nom_org'))
                                            ->where('distrito', '=', $request->getParam('distrito_org'))
                                            ->where('estado', '=', '1')
                                            ->where('codigo', '!=', $codigo)
                                            ->get();
            if(count($buscaexistente)==0){
                    Organizaciones::where('codigo', '=', $codigo)->update([
                    'nombre_org' => $request->getParam('nom_org'),
                    'tipo_org' => $request->getParam('obj_denomina'),
                    'fecha_constitucion' => $request->getParam('fecha_cons'),
                    'num_miem' => $request->getParam('num_miem'),
                    'fines' => $request->getParam('fines'),
                    'distrito' => $request->getParam('distrito_org'),
                    'domicilio_org' => $request->getParam('domicilio_org'),
                    'latitud' => $request->getParam('latitud_editar'),
                    'longitud' => $request->getParam('longitud_editar'),
                    'fecha_vigente' => $request->getParam('fecha_vigente'),
                    'fecha_fin' => $request->getParam('fecha_fin'),
                ]);
                $mensaje = $codigo.'?'.$this->show('4', 'Organización actualizada correctamente.');
                echo json_encode($mensaje);
            }else{
                $msm['response'] = 'si';
                $msm['message'] = $this->show('1', 'El nombre de la organización ya se encuentra registrada.');
                echo json_encode($msm);
            }

    }

    //ACTUALIZAR ORGANIZACIÓN
        public function ActualizarOrgLima($request, $response, $args)
    {
        $codigo=$request->getParam('codigorg');
        $buscaexistente = Organizaciones::where('nombre_org', '=', $request->getParam('nom_org'))
            ->where('distrito', '=', $request->getParam('distrito_org'))
            ->where('estado', '=', '1')
            ->where('codigo', '!=', $codigo)
            ->get();
        if(count($buscaexistente)==0){
            Organizaciones::where('codigo', '=', $codigo)->update([
                'num_org' => $request->getParam('num_org'),
                'nombre_org' => $request->getParam('nom_org'),
                'tipo_org' => $request->getParam('tipo'),
                'nivel' => $request->getParam('nivel'),
                'fecha_constitucion' => $request->getParam('fecha_cons'),
                'num_miem' => $request->getParam('num_miem'),
                'fines' => $request->getParam('fines'),
                'domicilio_org' => $request->getParam('dir_org'),
            ]);
            $mensaje = $codigo.'?'.$this->show('4', 'Organización actualizada correctamente.');
            echo json_encode($mensaje);
        }else{
            $msm['response'] = 'si';
            $msm['message'] = $this->show('1', 'El nombre de la organización ya se encuentra registrada.');
            echo json_encode($msm);
        }

    }

    //REGISTRAR ORGANIZACIÓN
    public function Registrar($request, $response, $args)
    {
        $fecha_actual = date("Y");
        $anio = (int)$fecha_actual;
        $dist = $request->getParam('distrito_orgv2');
        $buscaexistente = Organizaciones::where('nombre_org', '=', $request->getParam('nom_orgv2'))->where('distrito', '=', $request->getParam('distrito_orgv2'))->get();
        if(count($buscaexistente)==0){


            $ultimocodigo = Organizaciones::select(DB::raw('substr(num_org, 1, 4) as num'))
                ->where(DB::raw('substr(num_org, 6, 9)'), '=' , $anio)
                ->where('distrito', '=' , $dist)
                ->orderBy('num', 'DESC')->first();

            if(!$ultimocodigo){
                $numero = 0;
            }else{
                $numero = $ultimocodigo->num;
            }

            $nuevo_numero = $numero + 1 ;
            $nuevo_numero_str = str_pad($nuevo_numero, 4, "0", STR_PAD_LEFT);
            $str_year = $nuevo_numero_str."-".date("Y")."-".$dist;

            $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
            $organizacion = Organizaciones::create([
                'num_org' => $str_year,
                'iduser' => $ses_codigo,
                'nombre_org' => $request->getParam('nom_orgv2'),
                'tipo_org' => $request->getParam('obj_denominav2'),
                'fecha_constitucion' => $request->getParam('fecha_consv2'),
                'num_miem' => $request->getParam('num_miemv2'),
                'fines' => $request->getParam('finesv2'),
                'distrito' => $request->getParam('distrito_orgv2'),
                'domicilio_org' => $request->getParam('domicilio_orgv2'),
                'latitud' => $request->getParam('latitud'),
                'longitud' => $request->getParam('longitud'),
                'fecha_vigente' => $request->getParam('fecha_vigentev2'),
                'fecha_fin' => $request->getParam('fecha_finv2'),
            ]);
            $dato = $organizacion->id;
            $mensaje = $dato.'?'.$this->show('4', 'Organización registrada correctamente.');
            echo json_encode($mensaje);
        }else{
            $msm['response'] = 'si';
            $msm['message'] = $this->show('1', 'El nombre de la organización ya se encuentra registrada.');
            echo json_encode($msm);
        }

    }

    public function check()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $org = Organizaciones::where('iduser', $ses_codigo)->first();
        return !isset($org);
    }

	public function tipoorganizacion()
    {
        return TipoOrganizaciones::where('estado', '1')->get();
    }

    public function tipoorganizacionden()
    {
        return TipoOrganizacionDen::where('estado', '1')->get();
    }

    //HISTORIAL DE DOCUMENTOS
    public function getDenominaciones($request, $response, $args)
    {
        try {
            $idvalor = $request->getParam('idvalor');
            $data = TipoOrganizacionDen::where('codigo_tipo',$idvalor)->get();

            return $this->response->withJson($data, 200);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }

    public function postOrganizacion($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'nom_org' => v::notEmpty(),
            'obj_tipo' => v::notEmpty(),
			'obj_denomina' => v::notEmpty(),
            'fecha_cons' => v::notEmpty(),
            'num_miem' => v::noWhitespace()->notEmpty(),
            'domicilio_org' => v::notEmpty(),
            'latitud' => v::notEmpty(),
			'longitud' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.organizacion'));
        }
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $organizacion = Organizaciones::create([
            'iduser' => $ses_codigo,
            'nombre_org' => $request->getParam('nom_org'),
            'tipo_org' => $request->getParam('obj_tipo'),
			'tipo_den' => $request->getParam('obj_denomina'),
            'fecha_constitucion' => $request->getParam('fecha_cons'),
            'num_miem' => $request->getParam('num_miem'),
            'fines' => $request->getParam('fines'),
            'domicilio_org' => $request->getParam('domicilio_org'),
			'latitud' => $request->getParam('latitud'),
            'longitud' => $request->getParam('longitud'),
            'fecha_vigente' => $request->getParam('fecha_vigente'),
            'fecha_fin' => $request->getParam('fecha_fin'),
        ]);

        $this->flash->addMessage('info', 'Tu te haz registrado exitosamente');
        return $response->withRedirect($this->router->pathFor('auth.organizacion'));

    }

    public function putOrganizacion($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'nom_org' => v::notEmpty(),
            'obj_tipo' => v::notEmpty(),
            'fecha_cons' => v::notEmpty(),
            'num_miem' => v::noWhitespace()->notEmpty(),
            'domicilio_org' => v::notEmpty(),
			'latitud' => v::notEmpty()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.organizacion'));
        }


        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $organizacion = Organizaciones::where('iduser', '=', $ses_codigo)->update([
            'nombre_org' => $request->getParam('nom_org'),
            'tipo_org' => $request->getParam('obj_tipo'),
			'tipo_den' => $request->getParam('obj_denomina'),
            'fecha_constitucion' => $request->getParam('fecha_cons'),
            'num_miem' => $request->getParam('num_miem'),
            'fines' => $request->getParam('fines'),
            'domicilio_org' => $request->getParam('domicilio_org'),
			'latitud' => $request->getParam('latitud'),
            'longitud' => $request->getParam('longitud'),
            'fecha_vigente' => $request->getParam('fecha_vigente'),
            'fecha_fin' => $request->getParam('fecha_fin'),
        ]);

        $this->flash->addMessage('info', 'Datos actualizados correctamente');

        return $response->withRedirect($this->router->pathFor('auth.organizacion'));


    }

}
