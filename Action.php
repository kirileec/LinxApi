<?php
include_once 'Utils.php';
include_once 'Response.php';

class LinxApi_Action extends Widget_Abstract_Contents implements Widget_Interface_Do
{
    var $result ;
    public function execute()
    {

    }

    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->result = new Response();
    }

    private function checkMethod($method)
    {
        header('Content-type: application/json');
        if(!($_SERVER['REQUEST_METHOD']==$method))
        {
            $this->result->message = 'must be METHOD: '.$method;
            http_response_code(400);
            return false;
        }
        return true;
    }

    private function checkApiKey()
    {
        if (!isset($_GET['apikey'])) {
            $this->result->message = 'Apikey not set.';
            http_response_code(401);
            return false;
        }
        //获取系统配置
        $options = Helper::options();
        $apikey = $options->plugin('LinxApi')->apikey;
        if ($_GET['apikey'] != $apikey) {
            $this->result->message = 'Apikey invalid.';
            http_response_code(401);
            return false;
        }
        return true;
    }

    private function checkAdmKey()
    {
        if (!isset($_GET['admkey'])) {
            $this->result->message = 'AdmKey not set.';
            http_response_code(401);
            return false;
        }
        //获取系统配置
        $options = Helper::options();
        $admKey = $options->plugin('LinxApi')->admkey;
        if ($_GET['admkey'] != $admKey) {
            $this->result->message = 'admKey invalid.';
            http_response_code(401);
            return false;
        }
        return true;
    }

    private function checkParam($param)
    {
        if (!isset($_GET[$param])) {
            $this->result->message = 'param ['.$param.'] not set.';
            http_response_code(400);
            return false;
        }
        return true;
    }

    public function post()
    {
        if(!$this->checkMethod('GET')||!$this->checkApiKey()||!$this->checkParam('cid'))
        {
            echo json_encode($this->result);
            return;
        }

        http_response_code(200);

        $cid = $_GET['cid'];

        $post = Utils::GetPost($cid);

        if(count($post)==1)
        {
            echo json_encode($post[0]);
            return;
        }
        http_response_code(404);
        $this->result->message = 'not found.';
        echo json_encode($this->result);
    }

    public function recent()
    {
        if(!$this->checkMethod('GET')||!$this->checkApiKey()||!$this->checkParam('count'))
        {
            echo json_encode($this->result);
            return;
        }


        $recentCount = $_GET['count'];

        $posts = Utils::GetRecentPosts($recentCount);
        array_push($result, $posts);
        echo json_encode($result);
    }

    public function newpost()
    {
        if(!$this->checkMethod('POST')||!$this->checkApiKey()||!$this->checkAdmKey())
        {
            echo json_encode($this->result);
            return;
        }

        $jsonbody =json_decode(file_get_contents("php://input"));

        //echo json_encode($jsonbody);

        $title = $jsonbody->{'title'};
        $content = $jsonbody->{'content'};
        $user = $jsonbody->{'user'};
        $password =$jsonbody->{'password'};


        if(!$this->post_article($user,$password,$title,$content))
        {
            echo json_encode($this->result);
        }
        else
        {
            http_response_code(201);
            echo json_encode($this->result);
        }

    }


    public function editpost()
    {
        if(!$this->checkMethod('POST')||!$this->checkApiKey()||!$this->checkAdmKey())
        {
            echo json_encode($this->result);
            return;
        }

        $jsonbody =json_decode(file_get_contents("php://input"));

        //echo json_encode($jsonbody);

        $title = $jsonbody->{'title'};
        $content = $jsonbody->{'content'};
        $cid = $jsonbody->{'cid'};


        if(!$this->edit_article($cid,$title,$content))
        {
            echo json_encode($this->result);
        }
        else
        {
            http_response_code(201);
            echo json_encode($this->result);
        }
    }



    /**
     * 接口需要实现的入口函数
     *
     * @access public
     * @return void
     */
    public function action()
    {

    }

    private function post_article($user,$password,$title,$text)
    {

        if (!$this->user->hasLogin()) {
            $options = Helper::options();
            $userid = $options->plugin('LinxApi')->userid;
            echo $userid;
            if (!$this->user->simpleLogin($userid)) { //使用特定的账号登陆
                http_response_code(401);
                $this->result->message="login failed";
                return false;
            }
        }

        $request = Typecho_Request::getInstance();

        //填充文章的相关字段信息。
        $request->setParams(
            array(
                'title'=>$title,
                'text'=>$text,
                'fieldNames'=>array(),
                'fieldTypes'=>array(),
                'fieldValues'=>array(),
                'cid'=>'',
                'do'=>'publish',
                'markdown'=>'1',
                'date'=>'',
                'category'=>array(),
                'tags'=>'',
                'visibility'=>'publish',
                'password'=>'',
                'allowComment'=>'1',
                'allowPing'=>'1',
                'allowFeed'=>'1',
                'trackback'=>'',
            )
        );

        //设置token，绕过安全限制
        $security = $this->widget('Widget_Security');
        $request->setParam('_', $security->getToken($this->request->getReferer()));
        //设置时区，否则文章的发布时间会查8H
        date_default_timezone_set('PRC');

        //执行添加文章操作
        $widgetName = 'Widget_Contents_Post_Edit';
        $reflectionWidget = new ReflectionClass($widgetName);
        if ($reflectionWidget->implementsInterface('Widget_Interface_Do'))
        {
            //$result[0]['status']=201;
            $this->result->message="publish success";

            $this->widget($widgetName,NULL,NULL,false)->writePost();

            return true;
        }
        http_response_code(500);
        return false;
    }


    private function edit_article($cid,$title,$text)
    {

        if (!$this->user->hasLogin()) {
            $options = Helper::options();
            $userid = $options->plugin('LinxApi')->userid;

            if (!$this->user->simpleLogin($userid)) { //使用特定的账号登陆
                http_response_code(401);
                $this->result->message="login failed";
                return false;
            }
        }

        $request = Typecho_Request::getInstance();

        //填充文章的相关字段信息。
        $request->setParams(
            array(
                'title'=>$title,
                'text'=>$text,
                'fieldNames'=>array(),
                'fieldTypes'=>array(),
                'fieldValues'=>array(),
                'cid'=>$cid,
                'do'=>'publish',
                'markdown'=>'1',
                'date'=>'',
                'category'=>array(),
                'tags'=>'',
                'visibility'=>'publish',
                'password'=>'',
                'allowComment'=>'1',
                'allowPing'=>'1',
                'allowFeed'=>'1',
                'trackback'=>'',
            )
        );

        //设置token，绕过安全限制
        $security = $this->widget('Widget_Security');
        $request->setParam('_', $security->getToken($this->request->getReferer()));
        //设置时区，否则文章的发布时间会查8H
        date_default_timezone_set('PRC');

        //执行添加文章操作
        $widgetName = 'Widget_Contents_Post_Edit';
        $reflectionWidget = new ReflectionClass($widgetName);
        if ($reflectionWidget->implementsInterface('Widget_Interface_Do'))
        {
            //$result[0]['status']=201;

            $this->widget($widgetName,NULL,NULL,false)->writePost();
            $this->result->message="Edit Post Success";
            return true;
        }
        http_response_code(500);
        return false;
    }

}