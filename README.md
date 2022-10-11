# Portal RUOS v2 - Slim PHP

## Detalle técnico

### Requerimiento
- Framework Backend: Slim 3.0
- Framework Frontend: Twig
- App required: Composer

### Organización de proyecto
```
crud-slim
├── composer.json
├── composer.lock
├── public
│   ├── css/...
│   ├── images/...
│   ├── js/...
│   ├── modelos/...
│   ├── uploads/...
│   └── index.php
├── README.md
├── src
│   ├── Controller/...
│   ├── Models/...
│   ├── Views/...
│   ├── Helpers/...
│   ├── bootstrap.php
│   ├── routes.php
│   └── settings.php
└── vendor/...
```

# Instalación y configuración

## instalación y actualización de librerias

- En terminal ejecutar: `composer install`

## Apache 2.x

```apacheconfig
<VirtualHost *:80>
	ServerName dominio.proyecto.ruos
	ServerAlias local.dominio.ruos
	DocumentRoot "/RUTA_PROYECTO_RUOS/public"
	<Directory  "/RUTA_PROYECTO_RUOS/public/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>
```
## configuración de settings
- Hacer copia: `cp settings-overseas.php settings.php`
- Parametros de BD: 
```php
'db' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'ruos2',
    'username' => 'rasec',
    'password' => '123456',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]
 ```
## Capacidad de subida
- Hasta 20MB
 
## Permisos de carpetas
 - Dar permisos de escritura a la carpeta "upload": `chmod -R 776 public/uploads/ ` 
 
 ## Script BD
 - Ejecutar el script [`proyecto_ruos.sql`](src/Models/Base/proyecto_ruos.sql)
