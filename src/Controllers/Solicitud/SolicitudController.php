<?php

namespace App\Controllers\Solicitud;

use App\Models\Modelos\Documentos;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\Solicitudes;
use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;

Class SolicitudController extends Controller {

    public function getViewSolicitudesDistritales($request, $response) {
        return $this->view->render($response, 'auth/solicitudesdistritales.twig');
    }

    public function getSolicitudExterna($request, $response) {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return $this->view->render($response, 'auth/solicitudexterna.twig');
        } elseif ($solicitud->flag == 1) {
            return $this->view->render($response, 'auth/documento.twig');
        } elseif ($solicitud->flag == 2) {
            return $this->view->render($response, 'auth/notificacion.twig');
        }
    }

    public function getSolicitudesDistritales($request, $response, $args) {

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';


        $mes = $request->getParam('mes');
        $anio = $request->getParam('anio');

        try {
            $data = Solicitudes::select('tb_solicitudes.tipo_sol', 'tb_organizacion.nombre_org', 'tb_organizacion.fecha_vigente', 'tb_organizacion.fecha_fin', 'tb_organizacion.domicilio_org', 'tb_organizacion.nivel', 'tb_distritos.distrito', 'tb_tipos_orgs.descripcion', 'tb_organizacion.num_org', 'tb_solicitudes.inicio', 'tb_solicitudes.fec_revision', 'tb_solicitudes.fin', 'tb_solicitudes.res_dis', 'tb_docs.urldoc')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                            ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
                            ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                            ->where(function ($q) use($anio) {
                                if ($anio) {
                                    $q->where(DB::raw('substr(tb_solicitudes.fec_revision, 1, 4)'), $anio);
                                }
                            })
                            ->where(function ($q) use($mes) {
                                if ($mes) {
                                    $q->where(DB::raw('substr(tb_solicitudes.fec_revision, 6, 2)'), $mes);
                                }
                            })
                            ->where('tb_organizacion.iduser', $ses_codigo)
                            ->where('tb_solicitudes.flag', '!=', 1)
                            ->where('tb_solicitudes.flag', '!=', 4)
                            ->orderBy('tb_solicitudes.codigo', 'DESC')->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
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

    public function getSolicitud($request, $response) {
        $tipo_user = isset($_SESSION['tipo_user']) ? $_SESSION['tipo_user'] : '';
        if ($tipo_user == '2') {
            return $this->view->render($response, 'auth/solicitud.twig');
        } else {
            $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
            $org = Organizaciones::where('iduser', $ses_codigo)->first();
            if ($org) {
                $cod_org = $org->codigo;
                $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_org', $cod_org)->first();
                if (!isset($solicitud)) {
                    return $this->view->render($response, 'auth/solicitud.twig');
                } elseif ($solicitud->flag == 1) {
                    return $this->view->render($response, 'auth/documento.twig');
                } elseif ($solicitud->flag == 2) {
                    return $this->view->render($response, 'auth/notificacion.twig');
                } else {
                    return $this->view->render($response, 'auth/solicitud.twig');
                }
            } else {
                return $this->view->render($response, 'auth/solicitud.twig');
            }
        }
    }

    //BUSCAR SOLICITUD
    public function buscar($request, $response, $args) {
        try {
            $codigo_ajax = $request->getParam('codigo');
            $solicitud = Solicitudes::where('cod_org', $codigo_ajax)->where('flag', '=', '1')->where('flag', '=', '2')->get();

            if (count($solicitud) > 0) {
                $existe = "si";
            } else { {
                    $existe = "no";
                }
            }
            $msm['count'] = count($solicitud);
            $msm['response'] = $existe;
            return json_encode($msm);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //REGISTRA SOLICITUD --- DISTRITALES
    public function RegistrarSolicitud($request, $response) {
        $solicitud = Solicitudes::create([
                    'cod_org' => $request->getParam('cod_org'),
                    'tipo_sol' => $request->getParam('tipo_sol'),
        ]);

        $this->flash->addMessage('info', 'Registro de solicitud exitoso');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    //OBTENER JUNTADIRECTIVA POR ID
    public function getSolicitudesporOrg($request, $response, $args) {
        try {
            $codigo = $request->getParam('cod_org');
            $data = Solicitudes::select(DB::raw('count(DISTINCT tb_representantes.codigo) as miembros'),
                            DB::raw('count(DISTINCT tb_docs.codigo) as documentos'), 'tb_solicitudes.*')
                    ->leftJoin('tb_representantes', function($join) {
                        $join->on('tb_representantes.cod_sol', '=', 'tb_solicitudes.codigo')
                        ->where('tb_representantes.estado', '1');
                    })
                    ->leftJoin('tb_docs', function($join) {
                        $join->on('tb_docs.idsol', '=', 'tb_solicitudes.codigo')
                        ->where('tb_docs.flag', '1');
                    })
                    ->where('tb_solicitudes.cod_org', $codigo)
                    ->groupby('tb_solicitudes.codigo')
                    ->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //REGISTRA AJAX SOLICITUD --- DISTRITALES
    public function Registrar($request, $response) {

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $ses_distrito = isset($_SESSION['distrito']) ? $_SESSION['distrito'] : '';

        $fecha_actual = date("Y");
        $anio = (int) $fecha_actual;
        $ultimocodigo = Solicitudes::select(DB::raw('substr(num_sol, 1, 4) as num'))
                        ->where(DB::raw('substr(num_sol, 6, 9)'), '=', $anio)
                        ->where('cod_usuario', '=', $ses_codigo)
                        ->orderBy('num', 'DESC')->first();

        if (!$ultimocodigo) {
            $numero = 0;
        } else {
            $numero = $ultimocodigo->num;
        }

        $nuevo_numero = $numero + 1;
        $nuevo_numero_str = str_pad($nuevo_numero, 4, "0", STR_PAD_LEFT);
        $str_year = $nuevo_numero_str . "-" . date("Y") . "-" . $ses_distrito;


        $tipo = $request->getParam('tipo_solicitud');

        if ($tipo == 1) {
            $solicitud = Solicitudes::create([
                        'num_sol' => $str_year,
                        'cod_org' => $request->getParam('cod_org_sol'),
                        'cod_usuario' => $ses_codigo,
                        'tipo_sol' => $request->getParam('tipo_solicitud'),
                        'inicio' => $request->getParam('inicio'),
                        'fin' => $request->getParam('fin'),
                        'res_dis' => $request->getParam('res_dis')
            ]);
        } else {
            $solicitud = Solicitudes::create([
                        'num_sol' => $str_year,
                        'cod_org' => $request->getParam('cod_org_sol'),
                        'cod_usuario' => $ses_codigo,
                        'tipo_sol' => $request->getParam('tipo_solicitud'),
                        'tipo_mod' => $request->getParam('tipo_mod'),
                        'comentario' => $request->getParam('comentario'),
                        'inicio' => $request->getParam('inicio'),
                        'fin' => $request->getParam('fin'),
                        'res_dis' => $request->getParam('res_dis')
            ]);
        }
        $solicitud = $solicitud->id;
        $msm['solicitud'] = $solicitud;
        $msm['organizacion'] = $request->getParam('cod_org_sol');
        $msm['message'] = $this->show('4', 'Solicitud procesada corectamente.');
        return json_encode($msm);
    }

    public function RegistroSolicitudExterno($request, $response, $args) {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';

        $solicitud = Solicitudes::create([
                    'cod_org' => $request->getParam('cod_org'),
                    'cod_usuario' => $ses_codigo,
                    'tipo_sol' => 2,
        ]);


        $solicitud = $solicitud->id;
        return json_encode($solicitud);
    }

    //Consulta si ya existe una solicitud nueva
    public function checkSolNueva() {

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $org = Organizaciones::where('iduser', $ses_codigo)->first();
        //$cod_org = $org->codigo;
        $cod_org = isset($org->codigo) ? $org->codigo : '';
        $solicitud = Solicitudes::where('cod_org', $cod_org)->where('flag', '=', '3')->first();
        return !isset($solicitud);
    }

    public function solicitud() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        return Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
    }

    public function getMisSolicitudes($request, $response) {
        return $this->view->render($response, 'auth/missolicitudes.twig');
    }

    public function getResoluciones($request, $response) {
        return $this->view->render($response, 'auth/resoluciones.twig');
    }

    //LISTAR SOLCITUDES DE USUARIO
    public function getListarSolicitudes() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('cod_usuario', $ses_codigo)->get();
        return $solicitud;
    }

    public function getUbicaciones($request, $response, $args) {
        try {
            $data = Solicitudes::select('tb_solicitudes.codigo',
                            'tb_solicitudes.cod_org',
                            'tb_solicitudes.inicio',
                            'tb_solicitudes.fin',
                            'tb_organizacion.codigo',
                            'tb_organizacion.nombre_org',
                            'tb_organizacion.fines',
                            'tb_organizacion.num_org',
                            'tb_organizacion.domicilio_org',
                            'tb_organizacion.num_miem',
                            'tb_organizacion.latitud',
                            'tb_organizacion.longitud',
                            'tb_tipos_orgs.descripcion')
                    ->join(DB::raw('(SELECT max(codigo) as codigo
                              FROM tb_solicitudes
                              where flag=3
                              GROUP by cod_org
                            ) m'), function($join) {
                        $join->on("tb_solicitudes.codigo", "=", "m.codigo");
                    })
                    ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                    ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                    ->where('tb_organizacion.distrito', 1251)
                    ->get();
            return $this->response->withJson($data, 200);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }
	  public function getUbicacionesCulturales($request, $response, $args) {
        try {
            $data = Solicitudes::select('tb_solicitudes.codigo',
                            'tb_solicitudes.cod_org',
                            'tb_solicitudes.inicio',
                            'tb_solicitudes.fin',
                            'tb_organizacion.codigo',
                            'tb_organizacion.nombre_org',
                            'tb_organizacion.fines',
                            'tb_organizacion.num_org',
                            'tb_organizacion.domicilio_org',
                            'tb_organizacion.num_miem',
                            'tb_organizacion.latitud',
                            'tb_organizacion.longitud',
                            'tb_tipos_orgs.descripcion')
                    ->join(DB::raw('(SELECT max(codigo) as codigo
                              FROM tb_solicitudes
                              where flag=3
                              GROUP by cod_org
                            ) m'), function($join) {
                        $join->on("tb_solicitudes.codigo", "=", "m.codigo");
                    })
                    ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                    ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                    ->where('tb_organizacion.distrito', 1251)
					->where('tb_organizacion.tipo_den', 14)
                    ->get();
            return $this->response->withJson($data, 200);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    public function getUbicacionesGenerales($request, $response, $args) {
        try {
            $data = Solicitudes::select('tb_solicitudes.codigo',
                            'tb_solicitudes.cod_org',
                            'tb_solicitudes.inicio',
                            'tb_solicitudes.fin',
                            'tb_organizacion.codigo',
                            'tb_organizacion.nombre_org',
                            'tb_organizacion.fines',
                            'tb_organizacion.num_org',
                            'tb_organizacion.domicilio_org',
                            'tb_organizacion.num_miem',
                            'tb_organizacion.latitud',
                            'tb_organizacion.longitud',
                            'tb_tipos_orgs.descripcion')
                    ->join(DB::raw('(SELECT max(codigo) as codigo
                              FROM tb_solicitudes
                              where flag=3
                              GROUP by cod_org
                            ) m'), function($join) {
                        $join->on("tb_solicitudes.codigo", "=", "m.codigo");
                    })
                    ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                    ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                    // ->where('tb_organizacion.distrito',1251)
                    ->get();
            return $this->response->withJson($data, 200);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    public function SolicitudesOrganzaciones() {
        $data = Solicitudes::select('tb_solicitudes.codigo',
                        'tb_solicitudes.cod_org',
                        'tb_solicitudes.inicio',
                        'tb_solicitudes.fin',
                        'tb_organizacion.codigo',
                        'tb_organizacion.nombre_org',
                        'tb_organizacion.fines',
                        'tb_organizacion.num_org',
                        'tb_organizacion.domicilio_org',
                        'tb_organizacion.num_miem',
                        'tb_organizacion.latitud',
                        'tb_organizacion.longitud',
                        'tb_tipos_orgs.descripcion')
                ->join(DB::raw('(SELECT max(codigo) as codigo
                              FROM tb_solicitudes
                              where flag=3
                              GROUP by cod_org
                            ) m'), function($join) {
                    $join->on("tb_solicitudes.codigo", "=", "m.codigo");
                })
                ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                ->where('tb_organizacion.distrito', 1251)
                ->get();

        return $data;
    }

    public function getAdmSolicitudes($request, $response, $args) {
        $tipo_den = 16;
        $tipo_sol = $request->getParam('tipo_sol');
        $estado = $request->getParam('estado');
        try {
            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                            'tb_solicitudes.num_sol',
                            'tb_organizacion.nombre_org',
                            'tb_solicitudes.fec_revision',
                            'tb_solicitudes.fec_venci',
                            'tb_solicitudes.tipo_sol',
                            'tb_solicitudes.flag',
                            'tb_solicitante.casa')
                    ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                    ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                    //->where('tb_organizacion.tipo_den','!=', '16')
                    //->orWhereNull('tb_organizacion.tipo_den')
                    ->where('tb_organizacion.distrito', 1251)
                    ->where(function ($q) use($tipo_den) {
                        if ($tipo_den) {
                            $q->where('tb_organizacion.tipo_den', '!=', $tipo_den);
                            $q->orWhereNull('tb_organizacion.tipo_den');
                        }
                    })
                    ->where(function ($q) use($tipo_sol) {
                        if ($tipo_sol) {
                            $q->where('tb_solicitudes.tipo_sol', $tipo_sol);
                        }
                    })
                    ->where(function ($q) use($estado) {
                        if ($estado) {
                            $q->where('tb_solicitudes.flag', $estado);
                        }
                    })
                    ->orderBy('tb_solicitudes.codigo', 'DESC')
                    ->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 500);
        }
    }

    public function getAdmSolicitudesJuveniles($request, $response, $args) {
        $estado = $request->getParam('estado');
        try {
            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                                    'tb_solicitudes.num_sol',
                                    'tb_organizacion.nombre_org',
                                    'tb_solicitudes.fec_revision',
                                    'tb_solicitudes.fec_venci',
                                    'tb_solicitudes.tipo_sol',
                                    'tb_solicitudes.flag',
                                    'tb_solicitante.casa')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                            ->where('tb_organizacion.distrito', 1251)
                            ->where('tb_organizacion.tipo_den', 16)
                            ->where(function ($q) use($estado) {
                                if ($estado) {
                                    $q->where('tb_solicitudes.flag', $estado);
                                }
                            })
                            ->orderBy('tb_solicitudes.codigo', 'DESC')->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 500);
        }
    }

    public function getListMisSolicitudes($request, $response, $args) {
        try {
            $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';

            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                                    'tb_solicitudes.num_sol',
                                    'tb_organizacion.nombre_org',
                                    'tb_solicitudes.fec_revision',
                                    'tb_solicitudes.fec_venci',
                                    'tb_solicitudes.tipo_sol',
                                    'tb_solicitudes.comentario',
                                    'tb_solicitudes.flag',
                                    'tb_solicitante.casa')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                            ->where('tb_solicitudes.cod_usuario', $ses_codigo)->get();


            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 500);
        }
    }

    public function getAdmSolicitudesDistrito($request, $response, $args) {
        $codigo = $request->getParam('codigo');
        //$tipo_sol = $request->getParam('tipo_sol');
        $estado = $request->getParam('estado');
        $mes = $request->getParam('mes');
        $anio = $request->getParam('anio');

        try {

            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                                    'tb_solicitudes.num_sol',
                                    'tb_organizacion.nombre_org',
                                    'tb_solicitudes.fec_revision',
                                    'tb_solicitudes.fec_venci',
                                    'tb_solicitudes.comentario',
                                    'tb_solicitudes.tipo_sol',
                                    'tb_solicitudes.flag',
                                    'tb_docs.urldoc',
                                    'tb_solicitante.casa')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                            ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                            ->where('tb_solicitudes.cod_usuario', $codigo)
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

    public function getAdmSolicitudesDistritoPend($request, $response, $args) {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $estado = $request->getParam('estado');

        try {

            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                                    'tb_solicitudes.num_sol',
                                    'tb_organizacion.nombre_org',
                                    'tb_solicitudes.fec_revision',
                                    'tb_solicitudes.fec_venci',
                                    'tb_solicitudes.comentario',
                                    'tb_solicitudes.tipo_sol',
                                    'tb_solicitudes.flag',
                                    'tb_docs.urldoc',
                                    'tb_solicitante.casa')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                            ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                            ->where('tb_solicitudes.cod_usuario', $ses_codigo)
                            ->where('tb_docs.flag', 1)
                            ->where('tb_solicitudes.flag', $estado)
                            ->orderBy('tb_solicitudes.codigo', 'DESC')->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    public function getSolicitudesDistrito($request, $response, $args) {
        $tipo_sol = $request->getParam('tipo_sol');
        $estado = $request->getParam('estado');

        try {

            $data = Solicitudes::select('tb_solicitudes.codigo as codsol',
                                    'tb_solicitudes.num_sol',
                                    'tb_organizacion.nombre_org',
                                    'tb_solicitudes.fec_revision',
                                    'tb_solicitudes.fec_venci',
                                    'tb_solicitudes.tipo_sol',
                                    'tb_solicitudes.flag',
                                    'tb_solicitante.casa')
                            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                            ->join('tb_solicitante', 'tb_organizacion.iduser', '=', 'tb_solicitante.codigo')
                            ->where('tb_solicitante.', 1251)
                            ->where(function ($q) use($tipo_sol) {
                                if ($tipo_sol) {
                                    $q->where('tb_solicitudes.tipo_sol', $tipo_sol);
                                }
                            })
                            ->where(function ($q) use($estado) {
                                if ($estado) {
                                    $q->where('tb_solicitudes.flag', $estado);
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

    public function postSolicitud($request, $response) {
        $validation = $this->validator->validate($request, [
            'tipo_sol' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.solicitud'));
        }

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $org = Organizaciones::where('iduser', $ses_codigo)->first();
        $cod_organizacion = $org->codigo;

        $solicitud = Solicitudes::create([
                    'cod_org' => $cod_organizacion,
                    'cod_usuario' => $ses_codigo,
                    'tipo_sol' => $request->getParam('tipo_sol'),
        ]);

        //$this->flash->addMessage('info', 'Registro de solicitud exitoso');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    public function updateSolicitud($request, $response) {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        $codsolicitud = $solicitud->codigo;

        $fecha_actual = date('Y-m-d');
        $anio_actual = date('Y');
        $feriados = array(
            '2019-06-29',
            '2019-07-28',
            '2019-07-29',
            '2019-07-30',
            '2019-08-29',
            '2019-08-30',
            '2019-10-08',
            '2019-10-31',
            '2019-12-08',
            '2019-12-25'
        );

        $comienzo = strtotime($fecha_actual);
        $fecha_venci_noti = $comienzo;
        $i = 0;

        while ($i < 30) {
            $fecha_venci_noti += 86400;
            $es_feriado = FALSE;

            foreach ($feriados as $key => $feriado) {
                //Verifico Si La Fecha Final Actual Es Feriado O No
                if (date("Y-m-d", $fecha_venci_noti) === date("Y-m-d", strtotime($feriado))) {
                    $es_feriado = TRUE;
                }
            }

            if (!(date("w", $fecha_venci_noti) == 6 || date("w", $fecha_venci_noti) == 0 || $es_feriado)) {
                $i++;
            }
        }

        $fecha_venci = date('Y-m-d', $fecha_venci_noti);

        if (isset($solicitud)) {

            $solicitud = Solicitudes::select(DB::raw('substr(num_sol, 1, 3) as num'))
                            ->where(DB::raw('substr(num_sol, 5, 8)'), '=', $anio_actual)
                            ->orderBy('num', 'DESC')->first();

            $numero = $solicitud->num;
            $nuevo_numero = $numero + 1;
            $nuevo_numero_str = str_pad($nuevo_numero, 3, "0", STR_PAD_LEFT);
            $str_year = $nuevo_numero_str . "-" . date("Y");
            //var_dump($str_year);
            //die();

            Solicitudes::where('codigo', '=', $codsolicitud)->update([
                'num_sol' => $str_year,
                'flag' => 2,
                'fec_revision' => $fecha_actual,
                'fec_venci' => $fecha_venci,
            ]);

            $this->flash->addMessage('info', 'Datos actualizados correctamente');
            return $response->withRedirect($this->router->pathFor('auth.notificaciones'));
        }
    }

    public function updateTipoActualizacion($request, $response) {
        $validation = $this->validator->validate($request, [
            'tipos' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            $this->flash->addMessage('error', 'Debe seleccionar un tipo de modificación y un comentario');
        }

        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        $codsolicitud = $solicitud->codigo;

        if ($solicitud) {
            Solicitudes::where('codigo', '=', $codsolicitud)->update([
                'inicio' => $request->getParam('inicio'),
                'fin' => $request->getParam('fin'),
                'tipo_mod' => $request->getParam('tipos'),
                'comentario' => $request->getParam('comentario'),
            ]);

            $this->flash->addMessage('info', 'Datos actualizados correctamente');
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        }
    }

    public function updateTipoNuevo($request, $response) {
        $validation = $this->validator->validate($request, [
            'inicio' => v::noWhitespace()->notEmpty(),
            'fin' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            $this->flash->addMessage('error', 'Debe ingresar la fecha de inicio y fin de vigencia de la junta directiva');
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        } else {

            $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
            $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
            $codsolicitud = $solicitud->codigo;

            if ($solicitud) {

                Solicitudes::where('codigo', '=', $codsolicitud)->update([
                    'inicio' => $request->getParam('inicio'),
                    'fin' => $request->getParam('fin'),
                ]);

                $this->flash->addMessage('info', 'Datos actualizados correctamente');
                return $response->withRedirect($this->router->pathFor('auth.documento'));
            }
        }
    }

    private function SuccessInsert($sms) {
        $string = "";
        $string = "<h3 style='color:#157D09'>";
        $string .= "<i class='fa fa-check-circle' ></i>";
        $string .= $sms;
        $string .= "</h3>";
        return $string;
    }

    public function eliminarsolicitud($request, $response, $args) {
        $fecha_actual = date('Y-m-d');
        $solicitud = Solicitudes::where('codigo', '=', $request->getParam('codigo'))->update([
            'comentario' => $request->getParam('comentario'),
            'flag' => $request->getParam('flag'),
            'fecha_archivamiento' => $fecha_actual,
        ]);

        $ultimovalor = $request->getParam('codigo');
        $mensaje = $ultimovalor . '?' . $this->SuccessInsert(' SOLICITUD ARCHIVADA');
        echo $mensaje;
    }

    public function aprobarsolicitud($request, $response, $args) {
        $fecha_actual = date('Y-m-d');
        $solicitud = Solicitudes::where('codigo', '=', $request->getParam('codigo_sol'))->update([
            'fec_aprobacion' => $fecha_actual,
            'flag' => 3,
        ]);

        $mensaje = $this->SuccessInsert(' SOLICITUD APROBADA');
        echo $mensaje;
    }

    //CAMBIAR ESTADO DOCUMENTOS
    public function aprobarSolicitudDistrito($request, $response, $args) {
        try {
            $fecha_actual = date('Y-m-d');
            $codigo = $request->getParam('codigo');
            $estado = $request->getParam('estado');

            $existe = Solicitudes::where('codigo', '=', $codigo)->where('flag', '=', 3)->first();

            if ($existe) {
                $msm['response'] = 'existe';
                echo json_encode($msm);
            } else {
                Solicitudes::where('codigo', '=', $codigo)->update([
                    'fec_aprobacion' => $fecha_actual,
                    'flag' => $estado,
                ]);
                $msm['response'] = 'aprobado';
                echo json_encode($msm);
            }
        } catch (ErrorException $e) {
            $data = "Ocurrió un error.";
            echo $data;
        }
    }

    public function UpdateSolicitudDis($request, $response, $args) {
        try {
            $codigo = $request->getParam('idsoldisaprob');
            $codorghelp = $request->getParam('codorghelapro');

            $fecha_actual = date('Y-m-d');
            $feriados = array(
                '2019-06-29',
                '2019-07-28',
                '2019-07-29',
                '2019-07-30',
                '2019-08-29',
                '2019-08-30',
                '2019-10-08',
                '2019-10-31',
                '2019-12-08',
                '2019-12-25',
                '2019-01-01',
                '2019-04-09',
                '2019-05-01'
            );

            $comienzo = strtotime($fecha_actual);
            $fecha_venci_noti = $comienzo;
            $i = 0;

            while ($i < 30) {
                $fecha_venci_noti += 86400;
                $es_feriado = FALSE;
                foreach ($feriados as $key => $feriado) {
                    //Verifico Si La Fecha Final Actual Es Feriado O No
                    if (date("Y-m-d", $fecha_venci_noti) === date("Y-m-d", strtotime($feriado))) {
                        $es_feriado = TRUE;
                    }
                }
                if (!(date("w", $fecha_venci_noti) == 6 || date("w", $fecha_venci_noti) == 0 || $es_feriado)) {
                    $i++;
                }
            }
            $fecha_venci = date('Y-m-d', $fecha_venci_noti);
            Solicitudes::where('codigo', '=', $codigo)->update([
                'flag' => 2,
                'fec_revision' => $fecha_actual,
                'fec_venci' => $fecha_venci,
            ]);

            $msm['org'] = $codorghelp;
            $msm['response'] = "success";
            return $this->response->withJson($msm);
        } catch (ErrorException $e) {
            $msm['response'] = "error";
            $msm['message'] = $this->show('1', 'Error al eliminar');
            return $this->response->withJson($msm);
        }
    }

    public function ArchivarSolicitudDis($request, $response, $args) {
        try {
            $codigo = $request->getParam('idsoldisaprob');
            $codorghelp = $request->getParam('codorghelapro');
            $motivo = $request->getParam('motivo');

            $validar = Solicitudes::where('codigo', '=', $codigo)
                    ->Where('flag', '!=', 1)
                    ->first();


            if ($validar) {
                $msm['org'] = $codorghelp;
                $msm['response'] = "aprobado";
            } else {
                $fecha_actual = date('Y-m-d');
                Solicitudes::where('codigo', '=', $codigo)->update([
                    'flag' => 4,
                    'comentario' => $motivo,
                    'fecha_archivamiento' => $fecha_actual,
                ]);
                $msm['org'] = $codorghelp;
                $msm['response'] = "success";
            }

            return $this->response->withJson($msm);
        } catch (ErrorException $e) {
            $msm['response'] = "error";
            $msm['message'] = $this->show('1', 'Error al archivar solicitud');
            return $this->response->withJson($msm);
        }
    }

    public function getAdminSolicitud($request, $response, $args) {
        try {
            $codigo = $request->getParam('codigo');
            $data = Solicitudes::where('codigo', '=', $codigo)->get();
            return $this->response->withJson($data, 200);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    public function GuardarSolicitud($request, $response, $args) {

        $codigo = $request->getParam('codigo');

        if ($codigo) {
            Solicitudes::where('codigo', '=', $codigo)->update([
                'inicio' => $request->getParam('inicio'),
                'fin' => $request->getParam('fin'),
            ]);
            $mensaje['response'] = 'success';
            $mensaje['message'] = 'Datos actualizados';
        }

        echo json_encode($mensaje);
    }

}
