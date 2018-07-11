<?php
/**
 * Created by PhpStorm.
 * User: benny
 * Date: 12/09/2016
 * Time: 12:07
 * @var $transport \Magento\Framework\DataObject()
 */
namespace Camelweb\Debug\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DebugObserver implements ObserverInterface{

    public function _construct(){

    }

    public function execute(Observer $observer){
        $transport=$observer->getTransport();
        $transportOutput=$transport->getOutput();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
        $requestManager = $objectManager->get('\Magento\Framework\App\RequestInterface');

        $cookieAdmin=$cookieManager->getCookie('cwc_tpl_admin');
        $cookiePaths=$cookieManager->getCookie('cwc_tpl_paths');
        $cookieClasses=$cookieManager->getCookie('cwc_tpl_classes');
        $cookieBlocks=$cookieManager->getCookie('cwc_tpl_block_names');
        $cookieHandles=$cookieManager->getCookie('cwc_tpl_handles');

        $state =  $objectManager->get('Magento\Framework\App\State');
        $is_admin=('adminhtml' === $state->getAreaCode());

        $showPaths=($is_admin && $cookieAdmin && $cookiePaths) || (!$is_admin && $cookiePaths) || $requestManager->getParam('paths')==1;
        $showClasses=($is_admin && $cookieAdmin && $cookieClasses) || (!$is_admin && $cookieClasses) || $requestManager->getParam('classes')==1;
        $showBlockNames=($is_admin && $cookieAdmin && $cookieBlocks) || (!$is_admin && $cookieBlocks) || $requestManager->getParam('blocks')==1;
        $showHandles=($is_admin && $cookieAdmin && $cookieHandles) || (!$is_admin && $cookieHandles) || $requestManager->getParam('handles')==1;

        //echo "<pre>".print_r($observer->getLayout()->getUpdate()->getHandles(),true)."</pre>";die;
        if (!($showPaths || $showClasses || $showBlockNames || $showHandles)){
            return $observer;
        }

        $fileName=null;
        if ($showPaths){
            $block=$observer->getLayout()->getBlock($observer->getElementName());
            if ($block){
                $fileName=$block->getTemplateFile();
            }
        }
        //$fileName=($showPaths)?$observer->getLayout()->getTemplateFile():null;
        $class=($showClasses)?get_class($observer->getLayout()):null;
        $blockName=($showBlockNames)?$observer->getElementName():null;

        $htmlBefore='<div class="debugging-hints" style="position: relative; border: 1px dotted red; margin: 6px 2px; padding: 18px 2px 2px 2px;">';
        if ($showPaths) {
            $htmlBefore .= '<div class="debugging-hint-template-file" style="position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; left: 0; color: white; white-space: nowrap;" onmouseover="this.style.zIndex = 999;" onmouseout="this.style.zIndex = \'auto\';" title="' . $fileName . '">' . $fileName . '</div>';
        }
        if ($showBlockNames) {
            $htmlBefore .= '<div class="debugging-hint-block-class" style="position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; right: 0; color: blue; white-space: nowrap;" onmouseover="this.style.zIndex = 999;" onmouseout="this.style.zIndex = \'auto\';" title="'.$blockName.'">'.$blockName.'</div>';
        }
        if ($showClasses) {
            $htmlBefore .= '<div class="debugging-hint-block-class" style="position: absolute; top: 0; padding: 2px 5px; font: normal 11px Arial; background: red; right: 0; color: blue; white-space: nowrap;" onmouseover="this.style.zIndex = 999;" onmouseout="this.style.zIndex = \'auto\';" title="'.$class.'">'.$class.'</div>';
        }
        $htmlAfter='</div>';

        if ($showHandles && $observer->getElementName()=='before.body.end'){
            if (!$is_admin) {
                $transportOutput .= "<pre>" . print_r($observer->getLayout()->getUpdate()->getHandles(), true) . "</pre>";
            }else{
                $transportOutput .= "<div style='margin-left:100px'><pre>" . print_r($observer->getLayout()->getUpdate()->getHandles(), true) . "</pre></div>";
            }
        }

        if($fileName || $class || $blockName) {
            $transportOutput = $htmlBefore . $transportOutput . $htmlAfter;
            $transport->setOutput($transportOutput);
        }elseif($showHandles){
            $transport->setOutput($transportOutput);
        }
    }
}