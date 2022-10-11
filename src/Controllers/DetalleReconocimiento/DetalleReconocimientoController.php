<?php

namespace App\Controllers\DetalleReconocimiento;


use App\Models\Modelos\DetalleReconocimiento;
use App\Models\Modelos\Solicitudes;

use App\Controllers\Controller;


Class DetalleReconocimientoController extends Controller
{


    public function enviarSolicitudDistrito($request, $response, $args)
    {
        try {
            $recc = $request->getParam('recc');
            $codigo = $request->getParam('codigo');
            $estado = $request->getParam('estado');
            $existe = Solicitudes::where('codigo', '=', $codigo)->where('flag', '=', 2)->first();
            if($existe){
                $msm['response'] = 'existe';
                echo json_encode($msm);
            }else{
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
                $fecha_venci = date ( 'Y-m-d' , $fecha_venci_noti );


                DetalleReconocimiento::create([
                    'cod_solicitud' => $codigo,
                    'cod_recon' => $recc,
                    'estado' => $estado,
                ]);


                Solicitudes::where('codigo', '=', $codigo)->update([
                    'fec_aprobacion' => $fecha_actual,
                    'flag' => $estado,
                    'fec_revision' =>  $fecha_actual,
                    'fec_venci' => $fecha_venci,
                ]);

                $msm['response'] = 'aprobado';
                echo json_encode($msm);

            }

        } catch (ErrorException $e) {
            $data = "Ocurrió un error.";
            echo $data;
        }
    }


    //CAMBIAR ESTADO DOCUMENTOS
    public function enviardwSolicitudDistrito($request, $response, $args)
    {
        try {


            $fecha_actual = date('Y-m-d');
            $codigo = $request->getParam('codigo');
            $estado = $request->getParam('estado');
            $existe = Solicitudes::where('codigo', '=', $codigo)->where('flag', '=', 2)->first();
            if($existe){
                $msm['response'] = 'existe';
                echo json_encode($msm);
            }else{
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
                $fecha_venci = date ( 'Y-m-d' , $fecha_venci_noti );

                Solicitudes::where('codigo', '=', $codigo)->update([
                    'fec_aprobacion' => $fecha_actual,
                    'flag' => $estado,
                ]);

               DetalleReconocimiento::create([
                    'cod_solicitud' => 659,
                    'cod_recon' => 8,
                    'estado' => 2,

                ]);

                $msm['response'] = 'aprobado';
                echo json_encode($msm);
            }

        } catch (ErrorException $e) {
            $data = "Ocurrió un error.";
            echo $data;
        }
    }

      public function getDetalleReconocimiento($request, $response, $args)
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $estado = $request->getParam('estado');
        $codigo = $request->getParam('codigo');
        try {
             $data = DetalleReconocimiento::select('tb_solicitudes.codigo as codsol',
                'tb_solicitudes.num_sol',
                'tb_organizacion.nombre_org',
                'tb_solicitudes.fec_revision',
                'tb_solicitudes.fec_venci',
                'tb_solicitudes.comentario',
                'tb_solicitudes.tipo_sol',
                'tb_solicitudes.flag',
                'tb_docs.urldoc',
                'tb_solicitante.casa')
                ->join('tb_solicitudes', 'tb_solicitudes.codigo', '=', 'tb_detalle_recono.cod_solicitud')
                ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
                ->join('tb_docs', 'tb_detalle_recono.cod_solicitud', '=', 'tb_docs.idsol')
                ->where('tb_detalle_recono.cod_recon', $codigo)
                ->where('tb_solicitudes.cod_usuario', $ses_codigo)
                ->where('tb_docs.flag', 1)
                ->where('tb_solicitudes.flag', $estado)
                ->orderBy('tb_solicitudes.codigo', 'DESC')
                ->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }


}
