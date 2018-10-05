<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                    'type' => 'Zend\Mvc\Router\Http\Literal',
                    'options' => array(
                        'route'    => '/',
                        'defaults' => array(
                            'controller' => 'Nel\Controller\Inicio',
                            'action'     => 'inicio',
                        ),
                    ),
                    'may_terminate' => true,
                        'child_routes' => array(
                            'default' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[:controller[/:action][/:id]]',
                                    'constraints' => array(
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                    ),
                                    'defaults' => array(
                                        'action' => 'inicio',
                                        '__NAMESPACE__' => 'Nel\Controller'
                                        
                                    )
                                )
                            )
                        )
                    ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Nel\Controller\Inicio' => 'Nel\Controller\InicioController',
            'Nel\Controller\Administrador' => 'Nel\Controller\AdministradorController',
            'Nel\Controller\Persona' => 'Nel\Controller\PersonaController',
            'Nel\Controller\ConfigurarCantonProvincia' => 'Nel\Controller\ConfigurarCantonProvinciaController',
            'Nel\Controller\ConfigurarParroquiaCanton' => 'Nel\Controller\ConfigurarParroquiaCantonController',
            'Nel\Controller\Provincias' => 'Nel\Controller\ProvinciasController',
            'Nel\Controller\Cantones' => 'Nel\Controller\CantonesController',
            'Nel\Controller\Parroquias' => 'Nel\Controller\ParroquiasController',
            'Nel\Controller\LugaresMisa' => 'Nel\Controller\LugaresMisaController',
            'Nel\Controller\Sacerdote' => 'Nel\Controller\SacerdoteController',
            'Nel\Controller\Misas' => 'Nel\Controller\MisasController',
            'Nel\Controller\ConfigurarMisa' => 'Nel\Controller\ConfigurarMisaController',
            'Nel\Controller\DireccionPersona' => 'Nel\Controller\DireccionPersonaController',
            'Nel\Controller\Usuario' => 'Nel\Controller\UsuarioController',
            'Nel\Controller\GestionarModulosPrivilegios' => 'Nel\Controller\GestionarModulosPrivilegiosController',
            'Nel\Controller\Cursos' => 'Nel\Controller\CursosController',
            'Nel\Controller\Periodos' => 'Nel\Controller\PeriodosController',
            'Nel\Controller\Docentes' => 'Nel\Controller\DocentesController',
            'Nel\Controller\Horarios' => 'Nel\Controller\HorariosController',
            'Nel\Controller\RangoAsistencia' => 'Nel\Controller\RangoAsistenciaController',
            'Nel\Controller\ConfigurarCurso' => 'Nel\Controller\ConfigurarCursoController',
            'Nel\Controller\Certificados' => 'Nel\Controller\CertificadosController',
            'Nel\Controller\Matriculas' => 'Nel\Controller\MatriculasController',
            'Nel\Controller\Bautismo' => 'Nel\Controller\BautismoController',
            'Nel\Controller\Asistencias' => 'Nel\Controller\AsistenciasController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/login'           => __DIR__ . '/../view/layout/login.phtml',
            'layout/administrador'           => __DIR__ . '/../view/layout/administrador.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        
        //poder usar el ajax
        'strategies' => array (
            'ViewJsonStrategy'
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
