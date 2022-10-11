<?php

namespace App\Controllers\Notificacion;

use App\Models\Modelos\Documentos;
use App\Controllers\Controller;
use App\Models\Modelos\Notificaciones;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\Solicitudes;
use Respect\Validation\Validator as v;


Class NotificacionController extends Controller
{



    public function getNotificaciones($request, $response)
    {

		$tipo_user = isset($_SESSION['tipo_user']) ? $_SESSION['tipo_user'] : '';
        if ( $tipo_user == '2') {
			return $this->view->render($response, 'auth/solicitud.twig');
        }else{
			$ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
            $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
            if (!isset($solicitud)) {
                return $this->view->render($response, 'auth/solicitud.twig');
            }elseif($solicitud->flag == 1){
                return $this->view->render($response, 'auth/documento.twig');
            }elseif($solicitud->flag == 2){
                return $this->view->render($response, 'auth/notificacion.twig');
            }else{
                return $this->view->render($response, 'auth/solicitud.twig');
            }
        }

    }


    public function postDocumento($request, $response)
    {

        $validation = $this->validator->validate($request, [
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.documento'));
        }


        $solicitud = $request->getParam('idsol');
        $tipo_sol = $request->getParam('tipo_sol');
        $iduser = $request->getParam('iduser');
        $tipdoc = $request->getParam('tipdoc');
        $carpeta = "../uploads/";
        $nombre = basename($_FILES["file"]["name"]);
        $src = $carpeta . $solicitud . '_' . $tipo_sol . '_' . $iduser . '_' . $tipdoc . '_' . $nombre;
        $tipo = basename($_FILES["file"]["type"]);
        $size = basename($_FILES["file"]["size"]);

        if ($tipo != 'pdf' and
            $tipo != 'msword' and
            $tipo != 'vnd.openxmlformats-officedocument.wordprocessingml.document' and
            $tipo != 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $this->flash->addMessage('error-tipo-archivo', 'Solo se permiten archivos PDF, WORD o EXCEL');
            return $response->withRedirect($this->router->pathFor('auth.documento'));

        } elseif ($size >= 10485760) {

            $this->flash->addMessage('error-tipo-archivo', 'Solo se permiten subir documentos de menos de 10 Megabytes.');
            return $response->withRedirect($this->router->pathFor('auth.documento'));

        } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $src)) {

            $documento = Documentos::create([

                'idsol' => $request->getParam('idsol'),
                'tipo_sol' => $request->getParam('tipo_sol'),
                'iduser' => $request->getParam('iduser'),
                'tipdoc' => $request->getParam('tipdoc'),
                'urldoc' => $src,

            ]);

            if ($documento > 0) {

                $this->flash->addMessage('info', 'Registro correcto');
                return $response->withRedirect($this->router->pathFor('auth.documento'));

            } else {
                $this->flash->addMessage('error', 'No se pudo registrar el archivo');
                return $response->withRedirect($this->router->pathFor('auth.documento'));
            }

        } else {

            $this->flash->addMessage('error', 'No se pudo subir el archivo');
            return $response->withRedirect($this->router->pathFor('auth.documento'));

        }

    }


    public function deleteDocumento($request, $response)
    {
        $documento = Documentos::where('codigo', '=', $request->getParam('codigo'))->delete();
        $this->flash->addMessage('info', 'Documento eliminado');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    public function getMensajes($request, $response, $args)
    {
        try {
            $idvalor = $request->getParam('idvalor');
            $data = Notificaciones::where('iddoc', $idvalor)->get();
            return $this->response->withJson($data, 200);

        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }


    }

    private function SuccessInsert($sms){
        $string="";
        $string="<h3 style='color:#157D09'>";
        $string.="<i class='fa fa-check-circle' ></i>";
        $string.=$sms;
        $string.="</h3>";
        return $string;
    }


    public function postMensaje($request, $response, $args)
    {
        $not = Notificaciones::create([
            'iddoc' => $request->getParam('codigodocumento'),
            'tipo_user' => $request->getParam('tipo_user'),
            'codigo_user' => $request->getParam('iduser'),
            'tipdoc' => $request->getParam('tipdoc'),
            'mensaje' => $request->getParam('texto'),
        ]);

        $ultimovalor = $not->id;
        $mensaje = $ultimovalor.'?'.$this->SuccessInsert('Mensaje agregado correctamente');
        echo $mensaje;

    }

    public function addmensaje($request, $response, $args)
    {

        var_dump($request->getParams());//todo: CSRF check failed!

        $request = Slim::getInstance()->request();
        $notificacion = json_decode($request->getBody());
       try {

            $notificacion = Notificaciones::create([
                 'iddoc' => $request->getParam('codigo'),
                 'tipo_user' => $request->getParam('tipo_user'),
                 'codigo_user' => $request->getParam('iduser'),
                 'tipdoc' => $request->getParam('tipdoc'),
                 'mensaje' => $request->getParam('texto'),

            ]);

            $notificacion->id = $notificacion->lastInsertId();
             $notificacion = null;
             echo json_encode($notificacion);


     } catch(PDOException $e) {
            error_log($e->getMessage(), 3, '/var/tmp/php.log');
            echo '{"error":{"text":'. $e->getMessage() .'}}';
       }


    }





}
