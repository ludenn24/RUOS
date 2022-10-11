<?php

namespace App\Controllers\Resolucion;


use App\Models\Modelos\Documentos;
use App\Models\Modelos\Resolucion;
use App\Models\Modelos\Verificacion;
use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;



Class ResolucionController extends Controller
{

    public function getViewResolucionesDistritales($request, $response) {
        return $this->view->render($response, 'auth/resdistritales.twig');
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
    
    private function SuccessInsert($sms) {
        $string = "";
        $string = "<h3 style='color:#157D09'>";
        $string .= "<i class='fa fa-check-circle' ></i>";
        $string .= $sms;
        $string .= "</h3>";
        return $string;
    }

    public function getViewResoluciones($request, $response)
    {
        return $this->view->render($response, 'auth/resoluciones.twig');
    }

    //SUBIR RESOLUCIÓN
    public function SubirResolucion($request, $response, $args)
    {
        $solicitud = $request->getParam('solicitud');
        $carpeta = "uploads/resoluciones/";
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $solicitud . '_' . $fecha_actual. '_' .  $nombre;
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
            $doc = Resolucion::create([
                'num_res' => $request->getParam('num_res'),
                'cod_solicitud' => $request->getParam('solicitud'),
                'cod_user' => $request->getParam('admin'),
                'ruta' => $src,
                'fecha_registro' => $request->getParam('fecha_registro'),
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


    public function getResoluciones($request, $response, $args)
    {
        $distrito = $request->getParam('distrito');
        $tipo = $request->getParam('tipo');
        $anio = $request->getParam('anio');
        try {

              $data = Resolucion::select('tb_resolucion.num_res', 'tb_resolucion.cod_solicitud','tb_organizacion.nombre_org','tb_solicitudes.num_sol','tb_solicitudes.inicio','tb_solicitudes.fin','tb_organizacion.domicilio_org','tb_organizacion.nivel','tb_distritos.distrito','tb_tipos_orgs.descripcion','tb_resolucion.ruta','tb_resolucion.fecha_registro','tb_organizacion.num_org')
               ->join('tb_solicitudes', 'tb_resolucion.cod_solicitud', '=', 'tb_solicitudes.codigo')
               ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
               ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
               ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
               ->where(function ($q) use($anio) {
                      if ($anio) {
                           $q->where(DB::raw('substr(tb_resolucion.num_res, 5, 4)'), $anio);
                      }
                  })
               ->where(function ($q) use($tipo) {
                    if ($tipo) {
                        $q->where('tb_organizacion.tipo_org', $tipo);
                    }
                })
                ->where(function ($q) use($distrito) {
                     if ($distrito) {
                         $q->where('tb_organizacion.distrito', $distrito);
                     }
                 })
               ->where('tb_resolucion.estado', 1)    
               ->orderBy('tb_resolucion.fecha_registro', 'DESC')->get();


            $arreglo["data"] = $data;

            return $this->response->withJson($arreglo);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }
    
    public function getResolucionesDistritales($request, $response, $args) {
        $distrito = $request->getParam('distrito');
        $tipo = $request->getParam('tipo');
        $anio = $request->getParam('anio');
        try {

            $data = Verificacion::select('tb_verificacion.num_ver', 'tb_verificacion.cod_recon', 'tb_distritos.distrito', 'tb_verificacion.url_doc')
                            ->join('tb_reconocimiento', 'tb_verificacion.cod_recon', '=', 'tb_reconocimiento.codigo')
                            ->join('tb_solicitante', 'tb_reconocimiento.cod_usuario', '=', 'tb_solicitante.codigo')
                            ->join('tb_distritos', 'tb_solicitante.distrito', '=', 'tb_distritos.idDist')
                            ->where(function ($q) use($distrito) {
                                if ($distrito) {
                                    $q->where('tb_solicitante.distrito', $distrito);
                                }
                            })
                            ->where('tb_verificacion.estado', 1)
                            ->orderBy('tb_verificacion.fecha', 'DESC')->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }
    
    public function EliminarResolucion($request, $response, $args) {
        $solicitud = Resolucion::where('codigo', '=', $request->getParam('cod_res'))->update([
            'estado' => 2,
        ]);
        $mensaje = $this->SuccessInsert('RESOLUCIÓN ELIMINADA');
        echo $mensaje;
    }

}
