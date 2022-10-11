<?php

namespace App\Controllers\Reconocimiento;



use App\Models\Modelos\DetalleReconocimiento;
use App\Models\Modelos\Reconocimiento;
use App\Models\Modelos\Solicitudes;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;

Class ReconocimientoController extends Controller
{


    private function SuccessInsert($sms){
        $string="";
        $string="<h4 style='color:#157D09'>";
        $string.="<i class='fa fa-check-circle' ></i>";
        $string.= $sms;
        $string.="</h4>";
        return $string;
    }

    private function ErrorInsert($sms){
        $string="";
        $string="<h4>";
        $string.="<i class='fa fa-warning' ></i>";
        $string.= $sms;
        $string.="</h4>";
        return $string;
    }

    public function getViewReconocimiento($request, $response)
    {
        return $this->view->render($response, 'auth/reconocimiento.twig');
    }


    //LISTAR SOLCITUDES DE USUARIO
    public function getReconocimiento()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $reconocmiento = Reconocimiento::
        where('cod_usuario', $ses_codigo)->
        where('estado', 1)->first();
        return $reconocmiento;
    }


    public function postFinReconocimiento($request, $response, $args)
    {
        $codigo = $request->getParam('codigo');

        $detalle_recon = DetalleReconocimiento::where('cod_recon', $codigo)->get();
        if (count($detalle_recon)>0){
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
            Reconocimiento::where('codigo', '=', $codigo)->update([
                'estado' => 2,
                'inicio' =>  $fecha_actual,
                'fin' => $fecha_venci,
            ]);

            $mensaje = $codigo.'?'.$this->SuccessInsert(' ENVIO CORRECTO');
            echo $mensaje;
        }else{

            $mensaje = $codigo.'?'.$this->ErrorInsert(' DEBE AGREGAR AL MENOS UNA SOLICITUD');
            echo $mensaje;

        }


    }


    public function posReconocimiento($request, $response)
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $ses_distrito = isset($_SESSION['distrito']) ? $_SESSION['distrito'] : '';

        $fecha_actual = date("Y");
        $anio = (int)$fecha_actual;

        $ultimocodigo = Reconocimiento::select(DB::raw('substr(num_rec, 1, 4) as num'))
            ->where(DB::raw('substr(num_rec, 6, 9)'), '=' , $anio)
            ->where('cod_usuario', '=' , $ses_codigo)
            ->orderBy('num', 'DESC')->first();

        if(!$ultimocodigo){
            $numero = 0;
        }else{
            $numero = $ultimocodigo->num;
        }

        $nuevo_numero = $numero + 1 ;
        $nuevo_numero_str = str_pad($nuevo_numero, 4, "0", STR_PAD_LEFT);
        $str_year = $nuevo_numero_str."-".date("Y")."-".$ses_distrito;


        Reconocimiento::create([
            'num_rec' => $str_year,
            'cod_usuario' => $ses_codigo,
            'estado' => 1,
        ]);

        $this->flash->addMessage('info', 'Registro de solicitud exitoso');
        return $response->withRedirect($this->router->pathFor('envio.reconocimiento'));
    }

    public function getAdmReconocimientoDistritoPend($request, $response, $args)
    {
        $ses_codigo = $request->getParam('codigo');
        $mes = $request->getParam('mes');
        $anio = $request->getParam('anio');
        try {
            $data = Reconocimiento::select('tb_reconocimiento.codigo',
                'tb_solicitante.nombres',
                'tb_reconocimiento.num_rec',
                'tb_reconocimiento.inicio',
                'tb_reconocimiento.fin',
                'tb_reconocimiento.estado')
                ->join('tb_solicitante', 'tb_reconocimiento.cod_usuario', '=', 'tb_solicitante.codigo')
                ->where('tb_reconocimiento.cod_usuario', $ses_codigo)
                ->where(function ($q) use($anio) {
                    if ($anio) {
                        $q->whereYear('tb_reconocimiento.inicio', '=', $anio);
                    }
                })
                ->where(function ($q) use($mes) {
                    if ($mes) {
                        $q->whereMonth('tb_reconocimiento.inicio', '=', $mes);
                    }
                })

                ->orderBy('tb_reconocimiento.num_rec', 'ASC')->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }

    }


}
