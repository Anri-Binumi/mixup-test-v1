<?php

class U
{

    public static function dateToAge($then)
    {
        $thenTs = strtotime($then);
        $thenYear = date('Y', $thenTs);
        $age = date('Y') - $thenYear;
        if (strtotime('+' . $age . ' years', $thenTs) > time())
            $age--;
        return $age;
    }

    public static function urlify($str)
    {
        $str = mb_strtolower($str);
        $str = urlencode($str);
        return trim(preg_replace('/(%\d+|\+)/', '-', $str), '-');
    }

    public static function isDevelopmentMode()
    {
        return (APPLICATION_ENV != 'production' &&
                strpos(APPLICATION_ENV, '-prod') === false &&
                strpos(APPLICATION_ENV, '-node') === false
                ) ? true : false;
    }

    public static function isProductionMode()
    {
        return self::isDevelopmentMode() ? false : true;
    }

    public static function executeShellCommand($command)
    {
        U::log('cmd: ' . wordwrap($command, 100, " .. \n"));

        $rows = array();
        exec($command, $rows);
        $result = implode("\n", $rows);
        return $result;
    }

    public static function removeCache($key)
    {
        /* @var $cache Zend_Cache_Core */
        $cache = Zend_Registry::get('cache');
        self::log('cache remove: ' . $key);

        return $cache->remove($key);
    }

    public static function putCache($key, $value, $ttl = 0)
    {
        /* @var $cache Zend_Cache_Core */
        $cache = Zend_Registry::get('cache');
        self::log('cache save: ' . $key . ' (ttl: ' . $ttl . ')');

        return $cache->save($value, $key, array(), $ttl);
    }

    public static function getCache($key)
    {
        /* @var $cache Zend_Cache_Core */
        $cache = Zend_Registry::get('cache');
        $value = $cache->load($key);
        if ($value !== false)
            self::log('cache hit: ' . $key);

        return $value;
    }

    public static function log($str)
    {
        self::_log($str, Zend_Log::INFO);
    }

    public static function info($str)
    {
        self::_log($str, Zend_Log::INFO);
    }

    public static function error($str)
    {
        self::_log('######## ERROR !', Zend_Log::ERR);
        self::_log($str, Zend_Log::ERR);

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $first = array_shift($bt);

        self::_log('BACKTRACE: ' . print_r($first, true), Zend_Log::ERR);
    }

    public static function debug($str)
    {
        self::_log($str, Zend_Log::DEBUG);
    }

    public static function normalizeString($str)
    {
        /* note the /u modifier, making preg_replace treat both pattern and input string as UTF-8 */
        return preg_replace('/\W+/u', '', $str);
    }

    public static function trimSentence($str, $length = 0)
    {
        if (!$length)
        {
            switch (self::getLanguage())
            {
                case 'en':
                    $length = 28;
                    break;
                case 'th':
                    $length = 28;
                    break;
            }
        }

        if (mb_strlen($str) > $length)
        {

            $str = mb_substr($str, 0, $length);

            if (mb_strpos($str, ' ') !== false)
                $str = mb_substr($str, 0, mb_strrpos($str, ' '));

            $str = trim($str, '.?:, ') . '..';
            /* $str = rtrim(mb_strimwidth($str, 0, $length)) . '..'; */
        }

        return $str;
    }

    public static function getLanguage()
    {
        $session = self::getSession();
        if (empty($session->locale))
            $session->locale = 'en';

        return $session->locale;
    }

    public static function setLanguage($lang)
    {
        $session = self::getSession();
        self::log('Setting language: ' . $lang);

        switch ($lang)
        {
            case 'en':
                $session->locale = 'en';
                break;
            case 'th':
                $session->locale = 'th';
                break;
            default:
                self::log('Cannot set unsupported language: ' . $lang);
        }

        return $session->locale;
    }

    public static $ip;
    public static $uniq;
    public static $userId;

    public static function _log($str, $level)
    {
        if (!self::$ip)
            self::$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        if (!self::$uniq)
            self::$uniq = substr(uniqid(), 0, 5);

        if (!isset(self::$userId))
            self::$userId = self::getCurrentUserId();

        $log = Zend_Registry::get('log');
        $log->log('[' . self::$ip . ' ' . self::$uniq . ' ' . self::$userId . '] ' . $str, $level);
    }

    /**
     * @return Zend_Config
     */
    public static function config()
    {
        return Zend_Registry::get('cfg');
    }

    public static function sign($str)
    {
        return md5(self::config()->security->salt . $str);
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $err = "[$errno] $errstr  --  $errfile , line: $errline";
        self::error($err);

        /* DO NOT execute PHP internal error handler */
        return false;
    }

    public static function exceptionHandler($exception)
    {
        echo 'EXCEPTION: Check Application Log', "\n";
        self::error('EXCEPTION: ' . $exception->getMessage());

        $trace = $exception->getTrace();
        $count = 0;
        foreach ($trace as $t)
        {
            self::error(++$count . '. ' . (!empty($t['class']) ? $t['class'] : '') . ' > ' . $t['function'] . ' [' . (!empty($t['line']) ? $t['line'] : 'UN') . ']');

            if (count($t['args']))
                self::error('Args: ');
            foreach ($t['args'] as $a)
            {
                if (!is_object($a))
                    self::error(var_export($a, true));
            }
        }
    }

    public static function getRestUrlId()
    {
        if (isset($_SERVER['REQUEST_URI']))
        {
            $parts = explode('/', $_SERVER['REQUEST_URI']);
            return intval(array_pop($parts));
        }

        return false;
    }

    public static function getRequestUri()
    {
        if (isset($_SERVER['REQUEST_URI']))
            return preg_replace('/(http:\/\/|[#\?].*?$|\/$)/', '', $_SERVER['REQUEST_URI']);

        return false;
    }

    /**
     * @return Zend_Session_Namespace
     */
    public static function getSession()
    {
        $session = Zend_Registry::get('session');
        return $session;
    }

    public static function userData($key, $json = false)
    {
        return Biff_ViewHelper::getUserProfileData($key, $json);
    }

    public static function mediaUrl($s3Path, $version = 'small')
    {
        return Biff_ViewHelper::getMediaUrl($s3Path, $version);
    }

    public static function getCurrentUser()
    {
        $session = self::getSession();
        return is_array($session->currentUser) ? $session->currentUser : false;
    }

    public static function getCurrentUserId()
    {
        $user = self::getCurrentUser();
        return $user !== false ? $user['id'] : false;
    }

    public static function isLoggedIn()
    {
        $session = self::getSession();
        return is_array($session->currentUser);
    }

    public static function isAdmin()
    {
        $user = self::getCurrentUser();
        if ($user === false)
            return false;

        $admins = self::config()->admins->toArray();
        return in_array($user['email'], $admins);
    }

    public static function loginUser(array $user)
    {
        $session = self::getSession();
        $session->currentUser = $user;
        return $session->currentUser;
    }

    public static function logoutUser()
    {
        $session = self::getSession();
        unset($session->currentUser);
    }

    /** @var Facebook */
    public static $facebook;

    /**
     * @return Facebook
     */
    public static function getFacebookApi()
    {
        if (self::$facebook === null)
        {
            require_once 'facebook-php-sdk/src/facebook.php';

            // Create our Application instance (replace this with your appId and secret).
            self::$facebook = new Facebook(array(
                        'appId' => self::config()->facebook->client_id,
                        'secret' => self::config()->facebook->client_secret
                    ));
        }
        return self::$facebook;
    }

}

class SessionHandler
{

    public function __construct()
    {
        if (!session_id())
            session_start();
    }

    protected function set($key, $value)
    {
        $finalKey = $this->createSessionVariable($key);
        $_SESSION[$finalKey] = $value;
    }

    protected function get($key, $default = false)
    {
        $finalKey = $this->createSessionVariable($key);
        return isset($_SESSION[$finalKey]) ?
                $_SESSION[$finalKey] : $default;
    }

    protected function clear($key)
    {
        $finalKey = $this->createSessionVariable($key);
        unset($_SESSION[$finalKey]);
    }

    protected function createSessionVariable($key)
    {
        return 'biff_' . $key;
    }

}
