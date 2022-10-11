<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;



$app->get('/', 'HomeController:index')->setName('home');
$app->get('/mapa', 'HomeController:getViewMapa')->setName('mapa');
$app->get('/mapa-cultural', 'HomeController:getViewMapaCultural')->setName('mapacultural');
$app->get('/mapa-general', 'HomeController:getViewMapaTotal')->setName('mapatotal');
$app->get('/mapa/lista', 'SolicitudController:getUbicaciones');
$app->get('/mapa/lista-cultural', 'SolicitudController:getUbicacionesCulturales');
$app->get('/mapa/lista/general', 'SolicitudController:getUbicacionesGenerales');
$app->get('/resoluciones', 'ResolucionController:getViewResoluciones')->setName('resoluciones');
$app->get('/resoluciones/lista', 'ResolucionController:getResoluciones');
$app->get('/resoluciones/lista-distritales', 'ResolucionController:getResolucionesDistritales');
$app->get('/asesorialegal', 'AsesoriaController:getViewAsesoria')->setName('asesoria');
$app->post('/asesoria/registrar', 'AsesoriaController:Registrar');
$app->get('/auth/resoluciones', 'SolicitudController:getResoluciones')->setName('auth.resoluciones');
$app->get('/auth/resoluciones-distritales', 'ResolucionController:getViewResolucionesDistritales')->setName('auth.resdistritales');
$app->get('/almacenamiento', 'HomeController:getAlmacenamiento')->setName('almacenamiento');

//Administador

$app->group('', function () {
    $this->get('/admin/auth', 'AdminController:getViewDashSignIn')->setName('admin.signin');
    $this->post('/admin/auth', 'AdminController:postSignIn');

    $this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
    $this->post('/auth/signup', 'AuthController:postSignUp');
    $this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/auth/signin', 'AuthController:postSignIn');

})->add(new GuestMiddleware($container));

$app->group('/admin', function () {
    $this->get('/auth/signout', 'AuthController:getSignOutAdm')->setName('auth.admin.signout');
    $this->get('/export/{cod}', 'ExportController:export')->setName('export');
    $this->get('/consolidado-orgs-lima', 'ExportController:ConsolidadoOrganizacionesLima')->setName('consolidado-lima');
    $this->get('/consolidado-orgs-distritales', 'ExportController:ConsolidadoOrganizacionesDistritales')->setName('consolidado-distritales');
    $this->get('/dash', 'AdminController:getViewDash')->setName('admin.dash');
    $this->get('/m-c-principal', 'AdminController:getViewListmcprincipal')->setName('admin.m-c-principal');
    $this->get('/m-c-secundario', 'AdminController:getViewListmcsecundario')->setName('admin.m-c-secundario');
    $this->get('/m-c-juvenil', 'AdminController:getViewListmcjuvenil')->setName('admin.m-c-juvenil');
    $this->get('/m-d-principal', 'AdminController:getViewListmdprincipal')->setName('admin.m-d-secundario');
    $this->get('/listamenu', 'MenuCategoriaController:getMenuCategory');
    $this->get('/listaitem', 'MenuItemController:getMenuItem');
    $this->get('/listadistritos', 'AdminController:getAdmDistritos');
    $this->get('/listadistritosssol', 'AdminController:getAdmDistritosSS');
    $this->get('/listasolicitudes', 'SolicitudController:getAdmSolicitudes');
    $this->get('/listasolicitudesjuveniles', 'SolicitudController:getAdmSolicitudesJuveniles');
    $this->get('/solicitud/{cod}', 'AdminController:getViewSolicitud')->setName('admin.solicitud');
    $this->get('/m-citas', 'AdminController:getAsesorias')->setName('admin.asesoria');
    $this->get('/listaasesorias', 'AsesoriaController:getListAsesorias');
    $this->get('/solicitudes/{cod}', 'AdminController:getViewSolicitudesDistrito')->setName('admin.solicitudes');
    $this->get('/solicitudes/detalle/{cod}', 'AdminController:getViewDetalleSolicitudesDistrito')->setName('admin.detalle');
    $this->get('/listasolicitudesdistritales', 'SolicitudController:getAdmSolicitudesDistrito');
    $this->get('/listareconocimientodistritales', 'ReconocimientoController:getAdmReconocimientoDistritoPend');
    //VALIDAR
    $this->get('/listadetallereconocimientodistritales', 'AdminController:getAdmDetalleSolicitudesDistrito');
    //VALIDAR
    $this->get('/listaconsolidadoreconocimiento', 'AdminController:getConsilidadoDetalleSolicitudesDistrito');
    $this->post('/atenderasesoria', 'AsesoriaController:Antederasesoria');
    $this->post('/subirverificacion', 'AdminController:SubirVerificacion');
    $this->post('/aprobarverificacion', 'AdminController:aprobarVerificacion');
    $this->post('/eliminarreconocimiento', 'AdminController:eliminarReconocimiento');
    $this->get('/adminsolicitud', 'SolicitudController:getAdminSolicitud');
     $this->post('/actualizarfechas', 'SolicitudController:GuardarSolicitud');
})->add(new AuthMiddleware($container));

$app->group('', function () {
    $this->post('/subirresolucion', 'ResolucionController:SubirResolucion');
    $this->get('/documentos', 'DocumentoController:getdocumentosallx2');
    $this->get('/edit/{codigo}', 'OrganizacionController:getById');
    $this->get('/mensajes', 'NotificacionController:getMensajes');
    $this->post('/mensajesadd', 'NotificacionController:postMensaje');
    $this->post('/eliminarsolicitud', 'SolicitudController:eliminarsolicitud');
    $this->post('/aprobarsolicitud', 'SolicitudController:aprobarsolicitud');
    $this->post('/eliminarresolcuion', 'ResolucionController:EliminarResolucion');
    $this->post('/redocumento', 'DocumentoController:ResubirDocumento');
    $this->post('/subirdocumento', 'DocumentoController:SubirDocumento');
    $this->post('/subirdocumentojuveniles', 'DocumentoController:SubirDocumentosJuveniles');
    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
    $this->get('/auth/solicitud', 'SolicitudController:getSolicitud')->setName('auth.solicitud');
    $this->get('/auth/solicitud-e', 'SolicitudController:getSolicitudExterna')->setName('auth.solicitudexterna');
    $this->post('/auth/solicitud', 'SolicitudController:postSolicitud');
    $this->get('/auth/missolicitudes', 'SolicitudController:getMisSolicitudes')->setName('auth.solicitudes');
    //VER ORGANIZACIÓN
    $this->get('/auth/organizacion', 'OrganizacionController:viewOrgnizacion')->setName('auth.organizacion');
    $this->get('/auth/solicitudactualizar', 'SolicitudController:getSolicitud')->setName('auth.solicitudactualizar');
    $this->post('/auth/solicitudactualizar', 'SolicitudController:updateSolicitud');
    $this->get('/auth/documento', 'DocumentoController:getDocumento')->setName('auth.documento');
    $this->post('/auth/documento', 'OrganizacionController:postOrganizacion');
    $this->get('/auth/notificaciones', 'NotificacionController:getNotificaciones')->setName('auth.notificaciones');
    $this->post('/auth/notificaciones', 'NotificacionController:postNotificaciones');

})->add(new AuthMiddleware($container));

$app->group('/notificaciones/', function () {
    $this->get('notificaciones', 'NotificacionController:getNotificaciones')->setName('notificaciones.list');
    $this->post('notificaciones', 'NotificacionController:postNotificaciones');
})->add(new AuthMiddleware($container));

$app->group('/envio/', function () {
    $this->get('solicitud', 'ReconocimientoController:getViewReconocimiento')->setName('envio.reconocimiento');
    $this->post('solicitud', 'ReconocimientoController:posReconocimiento');
    $this->post('finalizar', 'ReconocimientoController:postFinReconocimiento');
})->add(new AuthMiddleware($container));

$app->group('/reconocimiento/', function () {
    $this->get('lista', 'ReconocimientoController:getReconocimientos');
    $this->get('consolidado/{cod}', 'ExportController:export')->setName('export');
})->add(new AuthMiddleware($container));

$app->group('/solicitud/', function () {
    $this->get('listamissolicitudes', 'SolicitudController:getListMisSolicitudes');
    $this->get('listar', 'SolicitudController:getSolicitudesporOrg');
    $this->get('buscar', 'SolicitudController:buscar');
    $this->post('registrar', 'SolicitudController:Registrar');
    $this->post('registrarsolexterna', 'SolicitudController:RegistroSolicitudExterno');
    $this->get('solicitudactualizar', 'SolicitudController:getSolicitud')->setName('solicitudes.solicitudes');
    $this->post('solicitudactualizar', 'SolicitudController:updateSolicitud');
    $this->get('solicitudtipomodificacion', 'SolicitudController:getSolicitud')->setName('solicitud.actualizacion');
    $this->post('solicitudtipomodificacion', 'SolicitudController:updateTipoActualizacion');
    $this->post('updatefecha', 'SolicitudController:updateTipoNuevo');
    $this->post('enviar', 'SolicitudController:UpdateSolicitudDis');
    $this->post('archivar', 'SolicitudController:ArchivarSolicitudDis');
    $this->get('distritales', 'SolicitudController:getViewSolicitudesDistritales')->setName('distritales');
    $this->get('distritales/lista', 'SolicitudController:getSolicitudesDistritales');
    $this->get('aprobarsoldistrito', 'SolicitudController:aprobarSolicitudDistrito');
    $this->get('enviarsoldistrito', 'DetalleReconocimientoController:enviarSolicitudDistrito');
    $this->get('listasolicitudesdistritalesPend', 'SolicitudController:getAdmSolicitudesDistritoPend');
    $this->get('listasolicitudesxdetallereconocimiento', 'DetalleReconocimientoController:getDetalleReconocimiento');

})->add(new AuthMiddleware($container));

$app->group('/organizacion/', function () {
    $this->get('editar', 'OrganizacionController:getById');
    $this->get('organizacion', 'OrganizacionController:getOrganizacion')->setName('organizacion.organizacion');
    $this->post('organizacion', 'OrganizacionController:putOrganizacion');
    $this->post('actualizar', 'OrganizacionController:Actualizar');
    $this->post('actualizarorgl', 'OrganizacionController:ActualizarOrgLima');
    $this->post('registrar', 'OrganizacionController:Registrar');
    $this->get('denominaciones', 'OrganizacionController:getDenominaciones');
    $this->get('getorganizaciontipo', 'OrganizacionController:gerOrganizacionTipo');
})->add(new AuthMiddleware($container));

$app->group('/juntadirectiva/', function () {
    $this->get('eliminar', 'JuntaDirectivaController:Eliminar');
    $this->post('registrar', 'JuntaDirectivaController:Registrar');
    $this->get('listaruntadirectiva', 'JuntaDirectivaController:getById');
    $this->get('juntadirectiva', 'JuntaDirectivaController:getJuntaDirectiva')->setName('juntadirectiva.registrar');
    $this->post('juntadirectiva', 'JuntaDirectivaController:postJuntaDirectiva');
    $this->post('juntadirectivaeliminar', 'JuntaDirectivaController:deleteJuntaDirectiva');
    $this->get('juntadirectivaeliminar', 'JuntaDirectivaController:getJuntaDirectiva')->setName('juntadirectiva.eliminar');
     
    $this->post('registrarrepresentante', 'JuntaDirectivaController:RegistrarRepresentanteDistrital');
    $this->get('listarepresentantes', 'JuntaDirectivaController:GetRepresentantesXSolicitud');
    $this->post('representantes', 'JuntaDirectivaController:RegistrarRepresentante');
    $this->post('eliminarrepresentante', 'JuntaDirectivaController:DeleteRepresentante');
    
})->add(new AuthMiddleware($container));

$app->group('/documento/', function () {
    $this->post('subirdocdis', 'DocumentoController:SubirDocDistrital');
    $this->get('buscar', 'DocumentoController:getDocumentoPorId');
    $this->get('documento', 'DocumentoController:getDocumento')->setName('documento.registrar');
    $this->post('documento', 'DocumentoController:postDocumento');
    $this->get('documentoeliminar', 'DocumentoController:getDocumento')->setName('documento.eliminar');
    $this->post('documentoeliminar', 'DocumentoController:deleteDocumento');
    $this->post('deletedocdis', 'DocumentoController:DeleteDocDistrital');
    $this->get('cambiarestado', 'DocumentoController:UpdateEstado');

})->add(new AuthMiddleware($container));
