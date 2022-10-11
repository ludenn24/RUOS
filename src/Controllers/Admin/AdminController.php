<?php

namespace App\Controllers\Admin;

use App\Models\Modelos\Admin;
use App\Controllers\Controller;
use App\Models\Modelos\Asesorias;
use App\Models\Modelos\Constataciones;
use App\Models\Modelos\DetalleReconocimiento;
use App\Models\Modelos\Documentos;
use App\Models\Modelos\Reconocimiento;
use App\Models\Modelos\Resolucion;
use App\Models\Modelos\Solicitudes;
use App\Models\Modelos\Usuarios;
use App\Models\Modelos\Verificacion;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

Class AdminController extends Controller
{

    public function admin()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $admin = Admin::where('codigo', $ses_codigo)->first();
        return $admin;
    }

    public function getViewDash($request, $response)
    {
        $st = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_organizacion.distrito', '=', 1251)->get()->count();
        $s3 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 3)->where('tb_organizacion.distrito', '=', 1251)->get()->count();
        $s2 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 2)->where('tb_organizacion.distrito', '=', 1251)->get()->count();
        $s1 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 1)->where('tb_organizacion.distrito', '=', 1251)->get()->count();
        $s4 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 4)->where('tb_organizacion.distrito', '=', 1251)->get()->count();

        $sdt = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_organizacion.distrito', '!=', 1251)->get()->count();
        $sd3 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 3)->where('tb_organizacion.distrito', '!=', 1251)->get()->count();
        $sd2 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 2)->where('tb_organizacion.distrito', '!=', 1251)->get()->count();
        $sd1 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 1)->where('tb_organizacion.distrito', '!=', 1251)->get()->count();
        $sd4 = Solicitudes::join('tb_organizacion','tb_organizacion.codigo', '=', 'tb_solicitudes.cod_org')
            ->where('tb_solicitudes.flag', 4)->where('tb_organizacion.distrito', '!=', 1251)->get()->count();




        return $this->view->render($response, 'admin/dash.twig',[
            'st' => $st,
            's3' => $s3,
            's2' => $s2,
            's4' => $s4,
            's1' => $s1,
            'sdt' => $sdt,
            'sd3' => $sd3,
            'sd2' => $sd2,
            'sd4' => $sd4,
            'sd1' => $sd1,
    ]);
    }

    public function getViewSolicitud($request, $response, $args)
    {
        $codigo = $args['cod'];

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $admin = Admin::where('codigo', $ses_codigo)->first();


        $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
            'tb_solicitudes.num_sol',
            'tb_organizacion.codigo',
            'tb_organizacion.distrito',
            'tb_organizacion.nombre_org',
            'tb_organizacion.tipo_org',
            'tb_organizacion.num_org',
            'tb_organizacion.nivel',
            'tb_organizacion.domicilio_org',
            'tb_organizacion.fines',
            'tb_organizacion.num_miem',
            'tb_organizacion.fecha_vigente',
            'tb_organizacion.fecha_fin',
            'tb_organizacion.fecha_constitucion',
            'tb_solicitudes.fec_revision',
            'tb_solicitudes.fec_venci',
            'tb_solicitudes.inicio',
            'tb_solicitudes.fin',
            'tb_solicitudes.tipo_sol',
            'tb_solicitudes.flag',
            'tb_solicitante.dni',
            'tb_solicitante.nombres',
            'tb_solicitante.apellidopaterno',
            'tb_solicitante.apellidomaterno',
            'tb_solicitante.correo',
            'tb_solicitante.telefono',
            'tb_solicitante.casa')
            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
            ->where('tb_solicitudes.codigo', $codigo)->first();

        $res = Resolucion::where('cod_solicitud', $codigo)->where('estado', 1)->first();


        $doc = Documentos::select('tb_docs.*', 'tb_docs.codigo', 'tb_docs.tipdoc', 'tb_docs.freg')
            ->join(DB::raw('(SELECT max(codigo) as codigo,
                              tipdoc
                              FROM tb_docs
                              where idsol='.$codigo.'
                              GROUP by tipdoc
                            ) m'), function($join){
                $join->on("tb_docs.codigo","=","m.codigo");
            })

            ->where('tb_docs.idsol', $codigo)
            ->orderBy('tb_docs.tipdoc', 'ASC')
            ->get();


        $cons = Constataciones::where('cod_solicitud', $codigo)->first();

        return $this->view->render($response, 'admin/auth/solicitud.twig',[
            'model' => $data,
            'doc' => $doc,
            'res' => $res,
            'cons' => $cons,
            'admin' => $admin,
        ]);
    }

    public function getAsesorias($request, $response, $args)
    {
        return $this->view->render($response, 'admin/auth/m-citas.twig');
    }


    public function getViewSolicitudesDistrito($request, $response, $args)
    {
        $codigo = $args['cod'];
        return $this->view->render($response, 'admin/auth/solicitudes.twig',[
            'solicitudes' => $codigo ,
        ]);
    }

    public function getViewDetalleSolicitudesDistrito($request, $response, $args)
    {
        $codigo = $args['cod'];
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $admin = Admin::where('codigo', $ses_codigo)->first();
        $ver = Verificacion::where('cod_recon', $codigo)->where('estado', 1)->first();
        $reconocimiento = Reconocimiento::select('tb_reconocimiento.num_rec',
            'tb_solicitante.distrito',
            'tb_solicitante.nombres',
            'tb_reconocimiento.estado',
            'tb_reconocimiento.inicio',
            'tb_reconocimiento.fin',
            'tb_reconocimiento.codigo')
            ->where('tb_reconocimiento.codigo', $codigo)
            ->join('tb_solicitante', 'tb_reconocimiento.cod_usuario', '=', 'tb_solicitante.codigo')
            ->first();

        return $this->view->render($response, 'admin/auth/detalle.twig',[
            'solicitud' => $codigo ,
            'reconocimiento' => $reconocimiento ,
            'ver' => $ver ,
            'admin' => $admin ,
        ]);
    }

    public function getAdmDistritos($request, $response, $args)
    {
        try {
            $data = Usuarios::select('tb_solicitante.nombres',
                'tb_reconocimiento.num_rec',
                'tb_solicitante.usuario',
                'tb_solicitante.codigo',
                DB::raw('count(tb_reconocimiento.codigo) as conteo'))
                ->join('tb_reconocimiento', function ($join) {
                    $join->on('tb_solicitante.codigo', '=', 'tb_reconocimiento.cod_usuario')
                        ->where('tb_reconocimiento.estado', '=', 2);
                })
            ->where('tb_solicitante.tipo_user', 2)
            ->groupBy('tb_solicitante.codigo')
            ->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }
    
    public function getAdmDistritosSS($request, $response, $args)
    {
        try {
            $data = Usuarios::select('tb_solicitante.nombres',
                'tb_reconocimiento.num_rec',
                'tb_solicitante.usuario',
                'tb_solicitante.codigo',
                DB::raw('count(tb_reconocimiento.codigo) as conteo'))
                ->leftjoin('tb_reconocimiento', function ($join) {
                    $join->on('tb_solicitante.codigo', '=', 'tb_reconocimiento.cod_usuario');
                })
            ->where('tb_solicitante.tipo_user', 2)
            ->groupBy('tb_solicitante.codigo')
            ->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }

    public function getAdmDetalleSolicitudesDistrito($request, $response, $args)
    {
        $codigo = $request->getParam('codigo');
        $estado = $request->getParam('estado');
        $mes = $request->getParam('mes');
        $anio = $request->getParam('anio');

        try {

            $data = DetalleReconocimiento::select('tb_detalle_recono.codigo',
                'tb_detalle_recono.cod_solicitud',
                'tb_detalle_recono.cod_recon',
                'tb_solicitudes.fec_venci',
                'tb_solicitudes.comentario',
                'tb_solicitudes.tipo_sol',
                'tb_solicitudes.flag',
                'tb_organizacion.nombre_org',
                'tb_solicitudes.num_sol',
                'tb_solicitudes.codigo as codsol',
                'tb_docs.urldoc')
                ->join('tb_reconocimiento', 'tb_detalle_recono.cod_recon', '=', 'tb_reconocimiento.codigo')
                ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                ->where('tb_detalle_recono.cod_recon', $codigo)
                ->where('tb_docs.flag', 1)

                ->where(function ($q) use($estado) {
                    if ($estado) {
                        $q->where('tb_solicitudes.flag', $estado);
                    }
                })
                ->where(function ($q) use($anio) {
                    if ($anio) {
                        $q->whereYear('tb_solicitudes.fec_revision', '=', $anio);
                    }
                })
                ->where(function ($q) use($mes) {
                    if ($mes) {
                        $q->whereMonth('tb_solicitudes.fec_revision', '=', $mes);
                    }
                })

                ->orderBy('tb_solicitudes.codigo', 'DESC')->get();


            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }



    public function getConsilidadoDetalleSolicitudesDistrito($request, $response, $args)
    {
        $codigo = $request->getParam('codigo');

        try {
                $data = Reconocimiento::select('tb_detalle_recono.codigo',
                                                'tb_detalle_recono.cod_solicitud',
                                                'tb_detalle_recono.cod_recon',
                                                'tb_solicitudes.fec_venci',
                                                'tb_solicitudes.comentario',
                                                'tb_solicitudes.tipo_sol',
                                                'tb_solicitudes.flag',
                                                'tb_organizacion.nombre_org',
                                                'tb_solicitudes.num_sol',
                                                'tb_solicitudes.codigo as codsol',
                                                'tb_docs.urldoc',
                                                DB::raw('count(DISTINCT tb_representantes.codigo) as miembros'), 
                                                DB::raw('count(DISTINCT tb_docs.codigo) as documentos') )
                            ->join('tb_detalle_recono', 'tb_reconocimiento.codigo', '=', 'tb_detalle_recono.cod_recon')
                            ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')

                            ->leftJoin('tb_representantes', function($join)
                            {
                                $join->on('tb_representantes.cod_sol', '=', 'tb_solicitudes.codigo')
                                ->where('tb_representantes.estado', '1');
                            })
                           ->leftJoin('tb_docs', function($join)
                            {
                                $join->on('tb_docs.idsol', '=', 'tb_solicitudes.codigo')
                                ->where('tb_docs.flag', '1');
                            })
                            ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
                            ->where('tb_reconocimiento.codigo', $codigo)
                            ->groupby('tb_solicitudes.codigo')
                            ->orderBy('tb_solicitudes.codigo', 'DESC')->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }


    public function getViewListmcprincipal($request, $response)
    {
        return $this->view->render($response, 'admin/auth/m-c-principal.twig');
    }

    public function getViewListmdprincipal($request, $response)
    {
        return $this->view->render($response, 'admin/auth/m-d-principal.twig');
    }

    public function getViewListmcsecundario($request, $response)
    {
        return $this->view->render($response, 'admin/auth/m-c-secundario.twig');
    }
    
    public function getViewListmcjuvenil($request, $response)
    {
        return $this->view->render($response, 'admin/auth/m-c-juvenil.twig');
    }

    public function getViewDashSignIn($request, $response)
    {
        return $this->view->render($response, 'admin/auth/signin.twig');
    }

    public function validar($login, $clave)
    {
        $admin = Admin::where('login', $login)->first();
        if (!$admin) {
            return false;
        }
        if (password_verify($clave, $admin->clave)) {
            $_SESSION['codigo'] = $admin->codigo;
            $_SESSION['dni'] = $admin->dni;
            return true;
        }
        return false;
    }

    public function logout()
    {
        unset($_SESSION['dni']);
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
        $auth = $this->AdminController->validar(
            $request->getParam('login'),
            $request->getParam('clave')
        );

        if (!$auth) {
            $this->flash->addMessage('error', 'El DNI o contraseña son incorrectos');
            return $response->withRedirect($this->router->pathFor('admin.signin'));
        }
        $this->flash->addMessage('info', 'Haz iniciado sesion correctamente');
        return $response->withRedirect($this->router->pathFor('admin.dash'));

    }

    //SUBIR RESOLUCIÓN
    public function SubirVerificacion($request, $response, $args)
    {
        $reconocimiento = $request->getParam('reconocimiento');
        $distrito = $request->getParam('distrito');
        $carpeta = "uploads/resoluciones/".$distrito."/";
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $reconocimiento . '_' . $fecha_actual. '_' .  $nombre;
        $tipo = basename($_FILES["file"]["type"]);
        $size = basename($_FILES["file"]["size"]);

        if ($tipo != 'pdf' and
            $tipo != 'msword' and
            $tipo != 'vnd.openxmlformats-officedocument.wordprocessingml.document' and
            $tipo != 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $msm['response'] = 'error';
            $msm['message'] = "Solo se permiten archivos PDF, WORD o EXCEL.";
        } elseif ($size >= 26214400) {
            $msm['response'] = 'error';
            $msm['message'] = $this->show('1','Solo se permiten subir documentos de menos de 25 Megabytes.');
        } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $src)) {
            $doc = Verificacion::create([
                'num_ver' => $request->getParam('num_ver'),
                'cod_recon' => $request->getParam('reconocimiento'),
                'cod_user' => $request->getParam('admin'),
                'url_doc' => $src,
                'fecha' => $request->getParam('fecha'),
                'estado' => 1,
            ]);

            $dato = $doc->id;
            if ($dato > 0) {
                $msm['response'] = 'success';
                $msm['message'] = $this->show('4','Documento subido correctamente');
            } else {
                $msm['response'] = 'error';
                $msm['message'] = "No se pudo registrar el documento.";
            }
        }
        else
        {
            $msm['response'] = 'error';
            $msm['message'] = "No se pudo subir el documento.";
        }

        echo json_encode($msm);
    }

    public function aprobarVerificacion($request, $response, $args)
    {
        $fecha_actual = date('Y-m-d');
        Reconocimiento::where('codigo', '=', $request->getParam('codigo_rec'))->update([
            'fec_aprobacion' => $fecha_actual,
            'estado' => 3,
        ]);

        $mensaje = $this->SuccessInsert(' SOLICITUD APROBADA');
        echo $mensaje;

    }

    public function eliminarReconocimiento($request, $response, $args)
    {
        $fecha_actual = date('Y-m-d');
        Reconocimiento::where('codigo', '=', $request->getParam('codigo_rec'))->update([
            'observacion' => $request->getParam('observacion'),
            'estado' => $request->getParam('estado'),
            'fec_archivamiento' => $fecha_actual,
        ]);

        $ultimovalor = $request->getParam('codigo_rec');
        $mensaje = $ultimovalor.'?'.$this->SuccessInsert(' SOLICITUD ARCHIVADA');
        echo $mensaje;

    }

    private function SuccessInsert($sms){
        $string="";
        $string="<h3 style='color:#157D09'>";
        $string.="<i class='fa fa-check-circle' ></i>";
        $string.=$sms;
        $string.="</h3>";
        return $string;
    }

    function show($type,$string)
    {
        $div='';
        if($type==1){
            $div='<div id="login-status" class="error-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><i class="fa fa-times"></i></div>
                    <div id="login-status-message">'.$string.'</div>
                </div>
            </div>
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

}
