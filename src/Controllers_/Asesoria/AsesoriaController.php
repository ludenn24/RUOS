<?php

namespace App\Controllers\Asesoria;


use App\Models\Modelos\Asesorias;
use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;



Class AsesoriaController extends Controller
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
          $div='<div class="alert alert-success"><i class="fa fa-calendar"></i> '.$string.'</div>';
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

    public function getViewAsesoria($request, $response)
    {
        return $this->view->render($response, 'auth/asesoria.twig');
    }

    public function Registrar($request, $response, $args)
    {
        $buscaexistente = Asesorias::where('fecha_ase', '=', $request->getParam('fecha_ase'))
        ->where('hora_ase', '=', $request->getParam('hora_ase'))
        ->Where('estado', '<', 3)
        ->get();

        if(count($buscaexistente)==0){
            $asesoria = Asesorias::create([
                'dni' => $request->getParam('dni'),
                'nombres' => $request->getParam('nombres'),
                'apellido_pat' => $request->getParam('apellido_pat'),
                'apellido_mat' => $request->getParam('apellido_mat'),
                'correo' => $request->getParam('correo'),
                'telefono' => $request->getParam('telefono'),
                'distrito' => $request->getParam('distrito'),
                'provincia' => $request->getParam('provincia'),
                'departamento' => $request->getParam('departamento'),
                'foto' => $request->getParam('foto'),
                'tipo_asesoria' => $request->getParam('tipo_asesoria'),
                'nom_org' => $request->getParam('nom_org'),
                'dist_org' => $request->getParam('dist_org'),
                'fecha_ase' => $request->getParam('fecha_ase'),
                'hora_ase' => $request->getParam('hora_ase'),
                'medio' => $request->getParam('medio'),
            ]);
            $dato = $asesoria->id;
            $mensaje = $dato.'?'.$this->show('4', ' <strong>RECORDATORIO DE ASESORÍA</strong> <hr> Solicitante: '.$request->getParam('nombres').' '.$request->getParam('apellido_pat').' '.$request->getParam('apellido_mat').'<hr> Fecha: '.$request->getParam('fecha_ase').'<hr> Hora: '.$request->getParam('hora_ase') );
            echo json_encode($mensaje);
        }else{
            $msm['response'] = 'si';
            $msm['message'] = $this->show('1', 'Lo sentimos, ya no quedan asesorías para el horario seleccionado');
            echo json_encode($msm);
        }

    }

    public function getListAsesorias($request, $response, $args)
    {
        $estado = $request->getParam('estado');
        try {
            $data = Asesorias::select('tb_asesoria.codigo',
            'tb_asesoria.dni',
            'tb_asesoria.nombres',
            'tb_asesoria.apellido_pat',
            'tb_asesoria.apellido_mat',
            'tb_asesoria.correo',
            'tb_asesoria.telefono',
            'tb_asesoria.tipo_asesoria',
            'tb_asesoria.nom_org',
            'tb_asesoria.dist_org',
            'tb_asesoria.fecha_ase',
            'tb_asesoria.hora_ase',
            'tb_asesoria.medio',
            'tb_asesoria.observaciones',
            'tb_asesoria.estado')
                ->where(function ($q) use($estado) {
                    if ($estado) {
                        $q->where('tb_asesoria.estado', $estado);
                    }
                })
                ->orderBy('tb_asesoria.codigo', 'DESC')->get();

            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 500);
        }

    }
     public function Antederasesoria( $request, $response, $args)
     {
         try {
             $codigo = $request->getParam('codigo');
             $estado = $request->getParam('estado');
             $observacion = $request->getParam('observacion');
             $existe = Asesorias::where('codigo', '=', $codigo)->where('estado', '=', 2)->first();
             if($existe){
                 $msm['response'] = 'aprobado';
                 echo json_encode($msm);
             }else{
                 Asesorias::where('codigo', '=', $codigo)->update([
                     'observaciones' => $observacion,
                     'estado' => $estado,
                 ]);
                 $msm['response'] = 'atendido';
                 echo json_encode($msm);
             }

         } catch (ErrorException $e) {
             $data = "Ocurrió un error.";
             echo $data;
         }
     }




}
