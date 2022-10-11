<?php

use Respect\Validation\Validator as v;
use Slim\Http\UploadedFile;

session_start();
require __DIR__ . '/../vendor/autoload.php';

$settings = require 'settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

$container['ConstatacionesController'] = function ($container) {
    return new \App\Controllers\Constataciones\ConstatacionesController($container);
};

$container['AdminController'] = function ($container) {
    return new \App\Controllers\Admin\AdminController($container);
};

$container['MenuCategoriaController'] = function ($container) {
    return new \App\Controllers\MenuCategoria\MenuCategoriaController($container);
};
$container['AuthController'] = function ($container) {
    return new \App\Controllers\Auth\AuthController($container);
};

$container['JuntaDirectivaController'] = function ($container) {
    return new \App\Controllers\JuntaDirectiva\JuntaDirectivaController($container);
};

$container['DocumentoController'] = function ($container) {
    return new \App\Controllers\Documento\DocumentoController($container);
};

$container['OrganizacionController'] = function ($container) {
    return new \App\Controllers\Organizacion\OrganizacionController($container);
};

$container['SolicitudController'] = function ($container) {
    return new \App\Controllers\Solicitud\SolicitudController($container);
};

$container['ReconocimientoController'] = function ($container) {
    return new \App\Controllers\Reconocimiento\ReconocimientoController($container);
};

$container['DetalleReconocimientoController'] = function ($container) {
    return new \App\Controllers\DetalleReconocimiento\DetalleReconocimientoController($container);
};


$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/Views', [
        'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('AdminController', [
        'admin' => $container->AdminController->admin(),
    ]);

    $view->getEnvironment()->addGlobal('AuthController', [
        'check' => $container->AuthController->check(),
        'usuario' => $container->AuthController->usuario(),
    ]);

    $view->getEnvironment()->addGlobal('SolicitudController', [
        'checkSolNueva' => $container->SolicitudController->checkSolNueva(),
        'solicitud' => $container->SolicitudController->solicitud(),
        'getListarSolicitudes' => $container->SolicitudController->getListarSolicitudes(),
        'SolicitudesOrganzaciones' => $container->SolicitudController->SolicitudesOrganzaciones(),
    ]);

    $view->getEnvironment()->addGlobal('OrganizacionController', [
        'check' => $container->OrganizacionController->check(),
        'organizacion' => $container->OrganizacionController->organizacion(),
        'tipoorganizacionden' => $container->OrganizacionController->tipoorganizacionden(),
		'tipoorganizacion' => $container->OrganizacionController->tipoorganizacion(),
        'organizaciones' => $container->OrganizacionController->getOrganizaciones(),
        'getdistritos' => $container->OrganizacionController->getDsitritos(),
    ]);

    $view->getEnvironment()->addGlobal('JuntaDirectivaController', [
        'listapuestos' => $container->JuntaDirectivaController->listapuestos(),
        'juntadirectiva' => $container->JuntaDirectivaController->juntadirectiva(),
        'representantes' => $container->JuntaDirectivaController->GetRepresentantes(),
    ]);

    $view->getEnvironment()->addGlobal('DocumentoController', [
        'getsolicitudgpv' => $container->DocumentoController->getsolicitudgpv(),
        'getdocumentosnuevo' => $container->DocumentoController->getListDocumentos(),
        'getactadefundacion' => $container->DocumentoController->getactadefundacion(),
        'getestatutos' => $container->DocumentoController->getestatutos(),
        'getactadeleccion' => $container->DocumentoController->getactadeleccion(),
        'getsnominadirectivo' => $container->DocumentoController->getsnominadirectivo(),
        'getnominamiembros' => $container->DocumentoController->getnominamiembros(),
        'getubicacionlocal' => $container->DocumentoController->getubicacionlocal(),
        'getubicacionradio' => $container->DocumentoController->getubicacionradio(),
        'getotro' => $container->DocumentoController->getotro(),
        'getasambleageneral' => $container->DocumentoController->getasambleageneral(),
        'getconvocatoria' => $container->DocumentoController->getconvocatoria(),
        'getpadronactualizado' => $container->DocumentoController->getpadronactualizado(),
        'getpestatutoorg' => $container->DocumentoController->getpestatutoorg(),
        'getdocumentos' => $container->DocumentoController->getdocumentos(),
        'getdocumentosactualizacion' => $container->DocumentoController->getdocumentosactualizacion(),
        'getactjuveniles' => $container->DocumentoController->getACT_REU(),
        'getmiejuveniles' => $container->DocumentoController->get_MIE_ORG(),
        'getsoljuveniles' => $container->DocumentoController->getSOL_JU(),
    ]);

    $view->getEnvironment()->addGlobal('ReconocimientoController', [
        'getreconocimiento' => $container->ReconocimientoController->getReconocimiento(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);
    return $view;
};

$container['validator'] = function ($container) {
    return new App\Helpers\validator;
};

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};

$container['ResolucionController'] = function ($container) {
    return new \App\Controllers\Resolucion\ResolucionController($container);
};

$container['MenuItemController'] = function ($container) {
    return new \App\Controllers\MenuItem\MenuItemController($container);
};

$container['NotificacionController'] = function ($container) {
    return new \App\Controllers\Notificacion\NotificacionController($container);
};

$container['AsesoriaController'] = function ($container) {
    return new \App\Controllers\Asesoria\AsesoriaController($container);
};

$container['ExportController'] = function ($container) {
    return new \App\Controllers\Export\ExportController($container);
};

$container['csrf'] = function ($container) {
   // return new \Slim\Csrf\Guard;

    $guard = new \Slim\Csrf\Guard();
    $guard->setPersistentTokenMode(true);
    return $guard;
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

v::with('App\\Helpers\\Rules\\');

require __DIR__ . '/../src/routes.php';
