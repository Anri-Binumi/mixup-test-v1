<?php

class Application_Model_Users extends Zend_Db_Table_Abstract
{
    /* protected $_schema = 'test'; */

    protected $_name = 'mu_users';

    const STATUS_ACTIVE = 0;

    public function getAllActive()
    {
        $select = $this->select()
                ->where('status = ?', self::STATUS_ACTIVE)
                ->limit(100);

        $res = $this->fetchAll($select)->toArray();

        foreach ($res as & $r)
        {
            if (!empty($r['json']))
                $r['json'] = Zend_Json::decode($r['json']);
        }

        return $res;
    }

}

?>
