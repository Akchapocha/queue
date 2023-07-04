<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 02.11.18
 * Time: 18:32
 */

class Router
{
    public function dispatch($uri)
    {
        $routes = [
//            0 => PATH_PREFIX.'/',
//            1 => PATH_PREFIX.'/login',
            2 => PATH_PREFIX.'/administrator',
            3 => PATH_PREFIX.'/window',
            4 => PATH_PREFIX.'/monitor',
            5 => PATH_PREFIX.'/stat'
        ];

        $controller = '';
        $view = [];

        foreach ($routes as $item => $route){

            if ($uri === '/'){

                http_response_code(404);
                require_once ERRORS.'404.php';

//                $controller = 'main';

            } else {

                if ($uri === $route){

                    if (PATH_PREFIX === ''){
                        $controller = preg_replace("/\//", '', $route);
                    } else {
                        $controller = preg_replace("/^.+\//", '', $route);

                    }

                }

            }

        }

        if ($controller !== ''){

            $classController = "\controllers\\".ucfirst($controller).'Controller';
            $pathFileController = CONTROLLERS.$controller.'Controller.php';
            $view['name'] = $controller.'View';
            $view['file'] = VIEWS.$controller.'View.php';

            require_once $pathFileController;
            $cObj = New $classController;
            $cObj->indexAction($view);

        } else {

            http_response_code(404);
            require_once ERRORS.'404.php';

        }

    }
}