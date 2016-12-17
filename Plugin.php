<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

include_once 'Utils.php';
/**
 * LinxApi for Typecho
 * 
 * @package LinxApi
 * @author linx
 * @version 1.0
 * @link https://llinx.me
 */
class LinxApi_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Helper::addRoute("route_recent_posts","/recent/posts",'LinxApi_Action','recent');
        Helper::addRoute("route_post","/post","LinxApi_Action","post");
        Helper::addRoute("route_newpost","/newpost","LinxApi_Action","newpost");
        Helper::addRoute("route_editpost","/editpost","LinxApi_Action","editpost");
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        //Helper::removeRoute("route_posts");
        Helper::removeRoute("route_post");
        Helper::removeRoute("route_recent_posts");
        Helper::removeRoute("route_newpost");
        Helper::removeRoute("route_editpost");
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $element_apikey = new Typecho_Widget_Helper_Form_Element_Text('apikey', null, Utils::getRandChar(16), _t('ApiKey 应用程序密钥'), '用于获得基本的数据获取权限, 默认随机生成');
        $element_admkey = new Typecho_Widget_Helper_Form_Element_Text('admkey', null, Utils::getRandChar(16), _t('AdmKey 管理密钥'), '用于获得发布修改等高级权限, 默认随机生成');
        $element_uid = new Typecho_Widget_Helper_Form_Element_Text('userid',null,Utils::getLoggedUserId(),_t('用户id'),'发布使用的用户id');

        $form->addInput($element_apikey);
        $form->addInput($element_admkey);
        $form->addInput($element_uid);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    {

    }


   
}
