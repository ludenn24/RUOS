<?php

namespace App\Controllers\Documento;

use App\Helper\JsonRequest;
use App\Helper\JsonRenderer;
use App\Models\Modelos\Documentos;
use App\Controllers\Controller;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\Solicitudes;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;

Class DocumentoController extends Controller {

    //VARIANTE DE ALERTAS
    function show($type, $string) {
        $div = '';
        if ($type == 1) {
            $div = '<div id="login-status" class="error-notice">
            <div class="content-wrapper">
                <div id="login-detail">
                    <div id="login-status-icon-container"><i class="fa fa-times"></i></div>
                    <div id="login-status-message">' . $string . '</div>
                </div>
            </div>
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

    private function SuccessInsert($sms) {
        $string = "";
        $string = "<h3 style='color:#157D09'>";
        $string .= "<i class='fa fa-check-circle' ></i>";
        $string .= $sms;
        $string .= "</h3>";
        return $string;
    }

    //OBTENER VISTA
    public function getDocumento($request, $response) {
        $tipo_user = isset($_SESSION['tipo_user']) ? $_SESSION['tipo_user'] : '';
        if ($tipo_user == 2) {
            return $this->view->render($response, 'auth/solicitud.twig');
        } else {
            $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
            $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
            if (!isset($solicitud)) {
                return $this->view->render($response, 'auth/solicitud.twig');
            } elseif ($solicitud->flag == 1) {
                return $this->view->render($response, 'auth/documento.twig');
            } elseif ($solicitud->flag == 2) {
                return $this->view->render($response, 'auth/notificacion.twig');
            } else {
                return $this->view->render($response, 'auth/solicitud.twig');
            }
        }
    }

    //SUBIR DOCUMENTO AJAX
    public function SubirDocumento($request, $response, $args) {

        $solicitud = $request->getParam('idsol');
        $tipo_sol = $request->getParam('tiposol');
        $iduser = $request->getParam('user');
        $tipdoc = $request->getParam('tipo_doc');
        $carpeta = "uploads/";
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $solicitud . '_' . $tipo_sol . '_' . $iduser . '_' . $tipdoc . '_' . $fecha_actual . '_' . $nombre;
        $tipo = basename($_FILES["file"]["type"]);
        $size = basename($_FILES["file"]["size"]);
        $moveee = $_FILES["file"]["tmp_name"];

        if ($tipo != 'pdf' and
                $tipo != 'msword' and
                $tipo != 'vnd.openxmlformats-officedocument.wordprocessingml.document' and
                $tipo != 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {

            $msm['response'] = 'error';
            $msm['message'] = "Solo se permiten archivos PDF, WORD o EXCEL.";
        } elseif ($size >= 262144000) {

            $msm['response'] = 'error';
            $msm['message'] = $this->show('1', 'Solo se permiten subir documentos de menos de 25 Megabytes.');
        } elseif (move_uploaded_file($moveee, $src)) {

            $doc = Documentos::create([
                        'idsol' => $request->getParam('idsol'),
                        'tipdoc' => $request->getParam('tipo_doc'),
                        'urldoc' => $src,
            ]);

            $dato = $doc->id;

            if ($dato > 0) {

                $count = Documentos::where('idsol', '=', $request->getParam('idsol'))
                                ->where('tipdoc', '!=', 'OTR_DOC')
                                ->where('tipdoc', '!=', 'EST_ORG')
                                ->where('flag', '=', '1')->get();

                $numdocumentos = count($count);

                if ($request->getParam('tiposol') == 1) {
                    if ($numdocumentos >= 8) {
                        $msm['postulacion'] = 'full';
                    } else {
                        $msm['postulacion'] = 'incomplete';
                    }
                } else {

                    if ($numdocumentos >= 4) {
                        $msm['postulacion'] = 'full';
                    } else {
                        $msm['postulacion'] = 'incomplete';
                    }
                }
                $msm['response'] = 'success';
                $msm['message'] = $this->show('4', 'Documento subido correctamente');
            } else {
                $msm['response'] = 'error';
                $msm['message'] = "No se pudo registrar el documento.";
            }
        } else {
            $msm['response'] = 'error';
            $msm['message'] = "No se pudo subir el documento.";
        }
        echo json_encode($msm);
    }
    
    
        //SUBIR DOCUMENTO JUVENILES AJAX
    public function SubirDocumentosJuveniles($request, $response, $args) {

        $solicitud = $request->getParam('idsol');
        $tipo_sol = $request->getParam('tiposol');
        $iduser = $request->getParam('user');
        $tipdoc = $request->getParam('tipo_doc');
        $carpeta = "uploads/$solicitud/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $solicitud . '_' . $tipo_sol . '_' . $iduser . '_' . $tipdoc . '_' . $fecha_actual . '_' . $nombre;
        $tipo = basename($_FILES["file"]["type"]);
        $size = basename($_FILES["file"]["size"]);
        $moveee = $_FILES["file"]["tmp_name"];

        if ($tipo != 'pdf' and
                $tipo != 'msword' and
                $tipo != 'vnd.openxmlformats-officedocument.wordprocessingml.document' and
                $tipo != 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $msm['response'] = 'error';
            $msm['message'] = $this->show('1', 'Solo se permiten archivos PDF, WORD o EXCEL');
        } elseif ($size >= 262144000) {
            $msm['response'] = 'error';
            $msm['message'] = $this->show('1', 'Solo se permiten subir documentos de menos de 25 Megabytes.');
        } elseif (move_uploaded_file($moveee, $src)) {
            $doc = Documentos::create([
                        'idsol' => $request->getParam('idsol'),
                        'tipdoc' => $request->getParam('tipo_doc'),
                        'urldoc' => $src,
            ]);
            $dato = $doc->id;
            if ($dato > 0) {
                $count = Documentos::where('idsol', '=', $request->getParam('idsol'))
                                ->where('flag', '=', '1')
                                ->get();
                $numdocumentos = count($count);
                    if ($numdocumentos >= 3) {
                        $msm['postulacion'] = 'full';
                    } else {
                        $msm['postulacion'] = 'incomplete';
                    }
                $msm['response'] = 'success';
                $msm['message'] = $this->show('4', 'Documento subido correctamente');
            } else {
                $msm['response'] = 'error';
                $msm['message'] = "No se pudo registrar el documento.";
            }
        } else {
            $msm['response'] = 'error';
            $msm['message'] = "No se pudo subir el documento.";
        }
        echo json_encode($msm);
    }

    //ACTUALZIAR DOCUMENTO AJAX
    public function SubirDocDistrital($request, $response, $args) {

        $solicitud = $request->getParam('idsoldis');
        $organizacion = $request->getParam('codigoorghelpn');
        $carpeta = "uploads/";
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $solicitud . '_' . $fecha_actual . '_' . $nombre;
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
            $msm['message'] = $this->show('1', 'Solo se permiten subir documentos de menos de 25 Megabytes.');
        } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $src)) {
            $doc = Documentos::create([
                        'idsol' => $request->getParam('idsoldis'),
                        'tipdoc' => $request->getParam('tipo_doc'),
                        'urldoc' => $src,
            ]);
            $dato = $doc->id;
            if ($dato > 0) {
                $msm['organizacion'] = $organizacion;
                $msm['response'] = 'success';
                $msm['message'] = $this->show('4', 'Documento subido correctamente');
            } else {
                $msm['response'] = 'error';
                $msm['message'] = "No se pudo registrar el documento.";
            }
        } else {
            $msm['response'] = 'error';
            $msm['message'] = "No se pudo subir el documento.";
        }

        echo json_encode($msm);
    }

    //ELIMINAR DOCUMENTO AJAX
    public function DeleteDocDistrital($request, $response, $args) {
        try {
            $codigo = $request->getParam('codigodelete');
            $cod_org = $request->getParam('codigoorghelp');
            $doc = Documentos::where('codigo', $codigo)->update(['flag' => '4']);

            $msm['response'] = "success";
            $msm['organizacion'] = $cod_org;
            $msm['message'] = $this->show('4', 'Documento eliminado');
            echo json_encode($msm);
        } catch (ErrorException $e) {
            $msm['response'] = "error";
            $msm['message'] = $this->show('1', 'Error al eliminar');
            return $this->response->withJson($msm);
        }
    }

    public function ResubirDocumento($request, $response, $args) {
        $solicitud = $request->getParam('idsol');
        $tipdoc = $request->getParam('tipo_doc');
        $carpeta = "uploads/";
        $nombre = basename($_FILES["file"]["name"]);
        $fecha_actual = date('Y-m-d-H-i-s');
        $src = $carpeta . $solicitud . '_' . $tipdoc . '_' . $fecha_actual . '_' . $nombre;
        $tipo = basename($_FILES["file"]["type"]);
        $size = basename($_FILES["file"]["size"]);
        $moveee = basename($_FILES["file"]["tmp_name"]);

        if ($tipo != 'pdf' and
                $tipo != 'msword' and
                $tipo != 'vnd.openxmlformats-officedocument.wordprocessingml.document' and
                $tipo != 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $msm['response'] = 'error';
            $msm['message'] = "Solo se permiten archivos PDF, WORD o EXCEL.";
        } elseif ($size >= 262144000) {

            $msm['response'] = 'error';
            $msm['message'] = $this->show('1', 'Solo se permiten subir documentos de menos de 25 Megabytes.');
        } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $src)) {
            $doc = Documentos::create([
                        'idsol' => $request->getParam('idsol'),
                        'tipdoc' => $request->getParam('tipo_doc'),
                        'urldoc' => $src,
            ]);

            $dato = $doc->id;

            if ($dato > 0) {
                $msm['response'] = 'success';
                $msm['message'] = $this->show('4', 'Documento subido correctamente');
            } else {
                $msm['response'] = 'error';
                $msm['message'] = "No se pudo registrar el documento.";
            }
        } else {

            $msm['response'] = 'error';
            $msm['message'] = "No se pudo subir el documento.";
        }

        echo json_encode($msm);
    }

    //SUBIR DOCUMENTO - VERSION 1.0
    public function postDocumento($request, $response) {
        $validation = $this->validator->validate($request, [
            'file' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            $this->flash->addMessage('error-tipo-archivo', 'Debe adjuntar un archivo');
        }
        $solicitud = $request->getParam('idsol');
        $tipo_sol = $request->getParam('tipo_sol');
        $iduser = $request->getParam('iduser');
        $tipdoc = $request->getParam('tipdoc');
        $carpeta = "uploads/";
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
        } elseif ($size >= 26214400) {

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

            $contador = is_array($documento);
            if ($contador > 0) {
                $count = Documentos::where('codigo', '=', $request->getParam('idsol'))->where('tipdoc', '!=', 'EST_ORG')->where('tipdoc', '!=', 'OTR_DOC')->get();
                $numdocumentos = is_array($count);
                if (is_array($numdocumentos) == 8) {
                    $msm['postulacion'] = 'full';
                } else {
                    $msm['postulacion'] = 'incomplete';
                }

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

    //ELIMINAR DOCUMENTO
    public function deleteDocumento($request, $response) {
        $documento = Documentos::where('codigo', '=', $request->getParam('codigo'))->delete();
        $this->flash->addMessage('info', 'Documento eliminado');
        return $response->withRedirect($this->router->pathFor('auth.documento'));
    }

    //CAMBIAR ESTADO DOCUMENTOS
    public function UpdateEstado($request, $response, $args) {
        try {
            $codigo = $request->getParam('codigo');
            $estado = $request->getParam('estado');
            Documentos::where('codigo', '=', $codigo)->update([
                'flag' => $estado,
            ]);
            $msm['response'] = 'modificado';
            echo json_encode($msm);
        } catch (ErrorException $e) {
            $data = "Ocurrió un error.";
            echo $data;
        }
    }

    //OBTENER JUNTADIRECTIVA POR ID
    public function BuscarDocs($request, $response, $args) {
        try {
            $codigo = $request->getParam('idsol');
            $data = Documentos::where('idsol', $codigo)->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //OBTENER DOCUMENTOS
    public function getdocumentos() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $org = Organizaciones::where('iduser', $ses_codigo)->first();
        $cod_org = isset($org->codigo) ? $org->codigo : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('cod_org', $cod_org)->first();
        if (!isset($solicitud)) {
            return false;
        }

        $codsolicitud = $solicitud->codigo;
        return Documentos::where('flag', '!=', '4')->where('flag', '!=', '3')->where('idsol', $codsolicitud)->where('tipdoc', '!=', 'OTR_DOC')->get();
    }

    public function getdocumentosall() {
        $data = Documentos::all();
        return $this->response->withJson($data, 200);
    }

    //OBTENER DOCUMENTOS
    public function getdocumentosallx($request, $response, $args) {
        $data = Documentos::where('idsol', $args['codigo'])->get();
        return $this->response->withJson($data, 200);
    }

    //HISTORIAL DE DOCUMENTOS
    public function getdocumentosallx2($request, $response, $args) {
        try {
            $idvalor = $request->getParam('idvalor');
            $tipo = $request->getParam('tipo');
            $data = Documentos::where([['idsol', '=', $idvalor], ['tipdoc', '=', $tipo]])->get();
            return $this->response->withJson($data, 200);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //OBTENER JUNTADIRECTIVA POR ID
    public function getDocumentoPorId($request, $response, $args) {
        try {
            $idsol = $request->getParam('idsol');
            $data = Documentos::where('idsol', $idsol)->where('flag', '1')->get();
            $arreglo["data"] = $data;
            return $this->response->withJson($arreglo);
        } catch (ErrorException $e) {
            $data = "Hubo un error al listar los datos.";
            return $this->response->withJson($data, 200);
        }
    }

    //BUSCA LA SOLICITUD
    public function getsolicitudgpv() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'SOL_GPV')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA DE DOCUMENTOS
    public function getListDocumentos() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;

        $junta = Documentos::select('tb_docs.*', 'tb_docs.codigo', 'tb_docs.tipdoc', 'tb_docs.freg')
                ->join(DB::raw('(SELECT max(codigo) as codigo, 
                              tipdoc
                              FROM tb_docs 
                              where idsol=' . $codsolicitud . '
                              GROUP by tipdoc 
                            ) m'), function($join) {
                    $join->on("tb_docs.codigo", "=", "m.codigo");
                })
                ->where('tb_docs.idsol', $codsolicitud)
                ->orderBy('tb_docs.tipdoc', 'ASC')
                ->get();

        return $junta;
    }

    //BUSCA ACTA DE FUNDACIÓN O DE CONSTITUCIÓN
    public function getactadefundacion() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'ACT_FUN')->where('idsol', $codsolicitud)->first();
    }

    //ESTATUTO Y ACTA DE APROBACIÓN
    public function getestatutos() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'EST_ACT')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA ACTA DE ELECCIÓN DEL ÓRGANO DIRECTIVO
    public function getactadeleccion() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'ACT_ELE')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA NÓMINA DE LOS MIEMBROS DEL ÓRGANO DIRECTIVO
    public function getsnominadirectivo() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'NOM_MOS')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA NÓMINA DE LOS MIEMBROS DE LA ORGANIZACIÓN SOCIAL
    public function getnominamiembros() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'NOM_MIEM')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA PLANO O CROQUIS REFERENCIAL DE LA UBICACIÓN DEL LOCAL
    public function getubicacionlocal() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'PLA_UBI')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA PLANO O CROQUIS DEL RADIO DE ACCIÓN
    public function getubicacionradio() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'PLA_ACI')->where('idsol', $codsolicitud)->first();
    }

    //BUSCA OTRO ACERVO DOCUMENTAL
    public function getotro() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'OTR_DOC')->where('idsol', $codsolicitud)->first();
    }

    // ACTUALIZACIÓN - ACTA ASAMBLEA GENERAL

    public function getasambleageneral() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'ACT_ASAM')->where('idsol', $codsolicitud)->first();
    }

    // ACTUALIZACIÓN - CONVOCATORIA O ESQUELA DE INVITACIÓN A LA ASAMBLEA GENERAL *

    public function getconvocatoria() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }

        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'CONV_INVI')->where('idsol', $codsolicitud)->first();
    }

    // ACTUALIZACIÓN - PADRÓN O NÓMINA ACTUALIZADA DE LOS MIEMBROS DE LA ORGANIZACIÓN

    public function getpadronactualizado() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'PAD_ACT')->where('idsol', $codsolicitud)->first();
    }

    // ACTUALIZACIÓN - ESTATUTO DE LA ORGANIZACIÓN EN CASO HAYA MODIFICACIÓN PARCIAL O TOTAL *
    public function getpestatutoorg() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'EST_ORG')->where('idsol', $codsolicitud)->first();
    }
    
        public function getdocumentosactualizacion()
    {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('idsol', $codsolicitud)->where('tipdoc', '!=', 'EST_ORG')->get();

    }

    // JUVENILES - ACTA DE LA REUNION DE CONSTITUCION *
    public function getACT_REU() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'ACT_REU')->where('idsol', $codsolicitud)->first();
    }

    // JUVENILES - ACTA DE LA REUNION DE CONSTITUCION *
    public function get_MIE_ORG() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'MIE_ORG')->where('idsol', $codsolicitud)->first();
    }

    // JUVENILES - ACTA DE LA REUNION DE CONSTITUCION *
    public function getSOL_JU() {
        $ses_codigo = isset($_SESSION['codigo']) ? $_SESSION['codigo'] : '';
        $solicitud = Solicitudes::where('flag', '!=', '4')->where('flag', '!=', '3')->where('cod_usuario', $ses_codigo)->first();
        if (!isset($solicitud)) {
            return false;
        }
        $codsolicitud = $solicitud->codigo;
        return Documentos::where('tipdoc', 'SOL_JU')->where('idsol', $codsolicitud)->first();
    }

}
