<?php
/**
 * Created by PhpStorm.
 * User: benny
 * Date: 12/09/2016
 * Time: 10:48
 */

namespace Camelweb\Debug\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action {

    public function execute()
    {
    	echo "<pre>".print_r('supppperrrrr',true)."</pre>";
        echo "<pre>".print_r(__METHOD__,true)."</pre>";
    }
}
