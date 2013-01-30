<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initMyConfig()
    {
        /* Set internal character encoding to UTF-8 */
        mb_internal_encoding("UTF-8");

        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('cfg', $config);

        /* needed for multi-auth */
        Zend_Registry::set('config', $this->getOptions());

        /* setup the default timezone */
        date_default_timezone_set($config->globals->timezone);
    }

    protected function _initMySession()
    {
        ini_set('session.gc_maxlifetime', 3600 * 24 * 2);
        ini_set('session.cookie_lifetime', 3600 * 24 * 2);

        $session = new Zend_Session_Namespace();
        $session->setExpirationSeconds(3600 * 24 * 2);

        Zend_Registry::set('session', $session);
    }

    protected function _initMyLog()
    {
        $this->bootstrap(array('log'));
        $log = $this->getResource('log');

        if (U::isProductionMode())
        {
            $filter = new Zend_Log_Filter_Priority(Zend_Log::INFO);
            $log->addFilter($filter);
        }

        Zend_Registry::set('log', $log);
        U::log('***** START *****');

        set_error_handler('U::errorHandler');
        set_exception_handler('U::exceptionHandler');

        $dontSave = array(
            '/login/twitter',
            '/login/google',
            '/login/facebook',
            '/user/login',
            '/user/logout',
            '/user/connect',
            '/login',
            '/logout',
        );

        $uri = U::getRequestUri();
        if (!in_array($uri, $dontSave))
        {
            U::getSession()->saveURI = $uri;
            U::log('Stored URI: ' . $uri);
        }
    }

    protected function _initMyDb()
    {
        $this->bootstrap(array('db', 'log'));

        /* @var $db Zend_Db_Adapter_Abstract */
        $db = $this->getResource('db');
        Zend_Registry::set('db', $db);

        if (U::isDevelopmentMode())
        {
            if (true)
            {
                $profiler = new MU_DbProfiler(true);
                $db->setProfiler($profiler);
                U::debug('DEV MODE - DB Profiler enabled!');
            }
            else
                U::debug('DEV MODE - But DB Profiler disabled');
        }
    }

    protected function _initMyCache()
    {
        $this->bootstrap(array('cachemanager', 'log'));

        $cache = $this->getResource('cachemanager');
        $basicCache = $cache->getCache('basic');
        Zend_Registry::set('cache', $basicCache);

        // setup a basic cache for table metadata, since we're using Zend_Tb_Table
        Zend_Db_Table_Abstract::setDefaultMetadataCache($basicCache);
    }

    protected function _initMyTraceRequest()
    {
        $this->bootstrap('log');

        if (U::isDevelopmentMode())
        {
            if (count($_GET))
                U::debug('GET: ' . print_r($_GET, true));

            if (count($_POST))
                U::debug('POST: ' . print_r($_POST, true));

            /*
              U::debug(print_r(getallheaders(), true));
              if (count($_COOKIE))
              U::debug('COOKIE: ' . print_r($_COOKIE, true));
             *
             */

            if (isset($_SERVER['REQUEST_URI']))
                U::debug('URI: ' . $_SERVER['REQUEST_URI'] . ' [IP ' . $_SERVER['REMOTE_ADDR'] . ']');
        }
    }

    /**
     *
     * This function initializes routes so that http://host_name/login
     * and http://host_name/logout is redirected to the user controller.
     *
     * There is also a dynamic route for clean callback urls for the login
     * providers
     */
    protected function _initRoutes()
    {
        /* @var $front Zend_Controller_Front */
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        $route = new Zend_Controller_Router_Route_Static('uploads',
                        array(
                            'controller' => 'index',
                            'action' => 'uploads'
                ));
        $router->addRoute('9', $route);

        $route = new Zend_Controller_Router_Route('start',
                        array(
                            'controller' => 'index',
                            'action' => 'start'
                ));
        $router->addRoute('8', $route);

        $route = new Zend_Controller_Router_Route('clients',
                        array(
                            'controller' => 'index',
                            'action' => 'our-clients'
                ));
        $router->addRoute('7', $route);

        $route = new Zend_Controller_Router_Route('about',
                        array(
                            'controller' => 'index',
                            'action' => 'about-us'
                ));
        $router->addRoute('6', $route);

        $route = new Zend_Controller_Router_Route('contact',
                        array(
                            'controller' => 'index',
                            'action' => 'contact'
                ));
        $router->addRoute('5', $route);

        $route = new Zend_Controller_Router_Route('create',
                        array(
                            'controller' => 'index',
                            'action' => 'start'
                ));
        $router->addRoute('4', $route);

        $route = new Zend_Controller_Router_Route('login/:provider',
                        array(
                            'controller' => 'index',
                            'action' => 'facebook-auth'
                ));
        $router->addRoute('3', $route);

        $route = new Zend_Controller_Router_Route_Static('login',
                        array(
                            'controller' => 'index',
                            'action' => 'facebook-auth'
                ));
        $router->addRoute('2', $route);

        $route = new Zend_Controller_Router_Route_Static('logout',
                        array(
                            'controller' => 'index',
                            'action' => 'logout'
                ));
        $router->addRoute('1', $route);
    }

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initCompleted()
    {
        $this->bootstrap('log');

        $log = $this->getResource('log');
        Zend_Registry::set('logger', $log);

        $log->info('Zend bootstrap completed..');
    }

}

