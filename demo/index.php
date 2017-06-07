<?php

header('Content-type:text');
define("TOKEN", "weiqiang");

$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
    public $AppId ="wx641707d466cea6a0";
    public $AppSecret ="3d11dcf677675860759059dd4b304ead";
//     public $menu = <<<JSON
// {
//    "button":[
//    {
//         "type":"click",
//         "name":"最新消息",
//         "key":"NEWS"
//     },
//     {
//         "type":"click",
//         "name":"整点音乐",
//         "key":"COME_SOME_MUSIC"
//     },
//     {
//          "name":"微信资源",
//          "sub_button":[
//          {
//              "type":"view",
//              "name":"搜索",
//              "url":"http://www.soso.com/"
//           },
//           {
//              "type":"view",
//              "name":"视频",
//              "url":"http://v.qq.com/"
//           },
//           {
//              "type":"click",
//              "name":"赞一下我们",
//              "key":"V1001_GOOD"
//           }]
//      }]
// }
// JSON
// ;
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }
    // public function getAccessToken($AppId,$AppSecret){
    
    //     $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$Appid}&secret={$Appsecret}";
    //     //但是现在PHP里面,如何让PHP请求这个地址，//(1) file_get_contents();
    //     $result = file_get_contents($url);
    //     $arr = json_decode($result,true);
    //     $api_url = $arr['access_token'];
    //     return $response_content = $this->_POST($api_url, $menu);
    // }
    public function responseMsg(){

        $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj = simplexml_load_string($postStr);
        $type = $postObj->MsgType;
        switch($type){
            case "event":
                $result = $this->receiveEvent($postObj);
                break;
            case "text":
                $result = $this->receiveText($postObj);
                break;
            case 'image':
                $result = $this->receiveImage($postObj);
                break;
        }
        echo $result;
    }
    public function receiveText($postObj){

        $type = $postObj->Content;
        switch ($type) {
            case '你好':
                $content = '你好，亲爱的';
                break;
            case '亲爱的':
                $content ='亲爱的，你真有眼光';
                break;
            default:
                $url='http://i.itpk.cn/api.php?question='.$type;
                $content = file_get_contents($url);
                if ($type =='笑话') {
                    $str =  iconv('ASCII', 'UTF-8//IGNORE', $content); 
                    // var_dump($result);die;
                    $json = json_decode( $str);
                    $content = $json->title."\n".$json->content;
                   
                 
                }else if ($type =='观音灵签') {
                    $str =  iconv('ASCII', 'UTF-8//IGNORE', $content); 
                    $json = json_decode( $str);
                    $content = $json->haohua."\n".$json->jieqian;
                 
                }else if ($type =='财神爷灵签') {
                    $str =  iconv('ASCII', 'UTF-8//IGNORE', $content); 
                    $json = json_decode( $str);
                    $content = $json->cwyj."\n"."功名：".$json->gongming."\n"."事业：".$json->shiye;
                }else if ($type =='月老灵签') {
                    $str =  iconv('ASCII', 'UTF-8//IGNORE', $content); 
                    $json = json_decode( $str);
                    $content = $json->haohua."\n"."解签：".$json->jieqian."\n"."事业：".$json->shiye;
                }
                // $content = $this->curl($url);
             
                // $content = $this->is_not_json($content,$type);        
               
        }
        $result = $this->zhuanhuanText($postObj,$content);
        return $result;
    }

 
  // 响应发送数据模板
    private $_template = array(
        'text' => <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>
XML
,
        );
    /**
     * 处理图片类型的消息
     */
    private function receiveImage($msg) {
        $content = '你所上传的图片的URL地址为: ' . $msg->PicUrl;
        // 做响应
        $template = $this->_template['text'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $content);
        echo $response_content;
        file_put_contents('./media_id.txt', $msg->MediaId);

    }
    public function receiveEvent($postObj){
        
        $eventType = $postObj->Event;
        switch ($eventType) {
            case 'subscribe':
                $content = '终于等到你了，双击评论666';
                break;
            case "unsubscribe":
                $content = "你太没眼光了…………";
                break;
            case 'CLICK':
                $this->menuClick($postObj);
                break;
        }
        $result = $this->zhuanhuanText($postObj,$content);
        return $result;
    }
    //转换成xml文档数据的功能
    private function zhuanhuanText($postObj,$content){
        $textTpl =  "<xml>
             <ToUserName><![CDATA[%s]]></ToUserName>
             <FromUserName><![CDATA[%s]]></FromUserName> 
             <CreateTime>%s</CreateTime>
             <MsgType><![CDATA[text]]></MsgType>
             <Content><![CDATA[%s]]></Content>
         </xml>";
         $result = sprintf($textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$content);
         return $result;
        
    }
    /**
     * [_POST description]
     * @param  [type]  $url   [description]
     * @param  [type]  $data  [description]
     * @param  boolean $https [description]
     * @return [type]         [description]
     */
    private function _POST($url, $data, $https=true) {
        return $this->_request($url, $https, 'POST', $data);
    }


    /**
     * [_request description]
     * @param  [type]  $url   [description]
     * @param  boolean $https [description]
     * @param  string  $type  [description]
     * @param  array   $data  [description]
     * @return [type]         [description]
     */
    private function _request($url, $https=true, $type='GET', $data=null) {
        $curl = curl_init();

        // 设定选项
        curl_setopt($curl, CURLOPT_URL, $url);
        // 请求时通常会携带选项，代理信息，referer来源信息
        // 请求代理信息
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
        // 自动生成请求来源
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        // 请求超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        // 是否获取响应头
        curl_setopt($curl, CURLOPT_HEADER, false);
        // 是否返回响应结果
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($https) {// 是HTTPS请求
            // https相关：是否对服务器的ssl验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            // https相关：ssl主机验证方式
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        }
        if ($type == 'POST') {// post请求
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // 发出请求
        $response_content = curl_exec($curl);

        if ($response_content === false) {
            trigger_error('请求不能完成，所请求的URL为：' . $url . "\n" . 'curl错误为：' . curl_error($curl), E_USER_ERROR);
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        return $response_content;
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
 
}
?>