<?php

abstract class MU_Controller extends Zend_Controller_Action
{

    public function echoJson($data)
    {
        $json = Zend_Json::encode($data);
        if ($this->_request->isXmlHttpRequest())
        {
            header('Content-Type: application/json');
            echo $json;
        }
        else
        {
            header('Content-Type: text/plain');
            echo Zend_Json::prettyPrint($json);
        }
    }

    public function requireAdmin($redirect = true)
    {
        if (U::isAdmin())
            return true;

        U::error('Access violation: ' . U::getCurrentUserId());

        if ($redirect)
            $this->_redirect('/index');

        $this->noViewNoLayout();
        $this->echoJson(array('ok' => 0, 'error' => '404'));
        return false;
    }

    public function noViewNoLayout()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

}

?>
