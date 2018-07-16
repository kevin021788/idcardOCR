<?php

namespace CIClient;

class CIClient {

    public $appid;
    public $secretId;
    public $secretKey;
    public $userid;

    public $http_info = '';

    //身份证OCR识别
    const OCR_IDCARD_URL = 'https://api.youtu.qq.com/youtu/ocrapi/idcardocr';

    //银行卡识别
    const OCR_CREDITCARDOCR_URL = 'https://api.youtu.qq.com/youtu/ocrapi/creditcardocr';

    //营业执照识别
    const OCR_BIZLICENSEOCR_URL = 'https://api.youtu.qq.com/youtu/ocrapi/bizlicenseocr';

    public function __construct ($appid = '', $secretId = '', $secretKey = '', $userid = '') {
        $this->appid = $appid;
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->userid = $userid;
    }

    /**
     * 功能：身份证OCR识别
     *
     * @param string $image_path 可为图片真实url或者相对url
     *
     * @param int    $card_type  0为正面 1为反而
     *
     * @return array|string
     */
    public function idcardocr ($image_path, $card_type = 0) {

        $is_url = substr ($image_path, 0, 4) == "http" ? true : false;

        if ($is_url === true) {
            $post_data = array (
                'app_id'    => $this->appid,
                'url'       => $image_path,
                'seq'       => '',
                'card_type' => $card_type,
            );
        } else {
            $real_image_path = realpath ($image_path);

            if (!file_exists ($real_image_path)) {
                return array ('status' => 0, 'message' => '找不到图片', 'data' => array ());
            }

            $image_data = file_get_contents ($real_image_path);
            $post_data = array (
                'app_id'    => $this->appid,
                'image'     => base64_encode ($image_data),
                'seq'       => '',
                'card_type' => $card_type,
            );
        }

        $postUrl = self::OCR_IDCARD_URL;
        $sign = $this->getSign ();

        $req = array (
            'url'     => $postUrl,
            'method'  => 'post',
            'timeout' => 10,
            'data'    => json_encode ($post_data),
            'header'  => array (
                'Authorization:' . $sign,
                'Content-Type:text/json',
                'Expect: ',
            ),
        );

        $rsp = $this->curlPost ($req);
        $ret = json_decode ($rsp, true);
        if ($ret['errorcode'] == '0' && $ret['errormsg'] == 'OK') {

            if ($card_type == 0) {
                $data = array (
                    'name'    => $ret['name'],
                    'sex'     => $ret['sex'] == '男' ? 1 : 2,
                    'nation'  => $ret['nation'],
                    'birth'   => explode ('/', $ret['birth']),
                    'address' => $ret['address'],
                    'id'      => $ret['id']
                );
            } elseif ($card_type == 1) {
                $data = array (
                    'valid_date' => explode ('-', $ret['valid_date']),
                    'authority'  => $ret['authority']
                );
            }

            return array ('status' => 1, 'message' => '成功', 'data' => $data);
        } else {
            return array ('status' => 0, 'message' => $ret['errormsg'], 'data' => $ret);
        }

    }

    /**
     * 功能：银行卡识别
     *
     * @param $image_path 可为图片真实url或者相对url
     *
     * @return array
     */
    public function creditcardocr ($image_path) {

        $is_url = substr ($image_path, 0, 4) == "http" ? true : false;

        if ($is_url === true) {
            $post_data = array (
                'app_id' => $this->appid,
                'url'    => $image_path,
                'seq'    => ''
            );
        } else {
            $real_image_path = realpath ($image_path);

            if (!file_exists ($real_image_path)) {
                return array ('status' => 0, 'message' => '找不到图片', 'data' => array ());
            }

            $image_data = file_get_contents ($real_image_path);
            $post_data = array (
                'app_id' => $this->appid,
                'image'  => base64_encode ($image_data),
                'seq'    => ''
            );
        }

        $postUrl = self::OCR_CREDITCARDOCR_URL;
        $sign = $this->getSign ();

        $req = array (
            'url'     => $postUrl,
            'method'  => 'post',
            'timeout' => 10,
            'data'    => json_encode ($post_data),
            'header'  => array (
                'Authorization:' . $sign,
                'Content-Type:text/json',
                'Expect: ',
            ),
        );

        $rsp = $this->curlPost ($req);
        $ret = json_decode ($rsp, true);
        if ($ret['errorcode'] == '0' && $ret['errormsg'] == 'OK') {
            $data = array (
                'id'       => isset($ret['items'][0]) ? $ret['items'][0]['itemstring'] : '',
                'type'     => isset($ret['items'][1]) ? $ret['items'][1]['itemstring'] : '',
                'name'     => isset($ret['items'][2]) ? $ret['items'][2]['itemstring'] : '',
                'bank'     => isset($ret['items'][3]) ? $ret['items'][3]['itemstring'] : '',
                'validity' => isset($ret['items'][4]) ? $ret['items'][4]['itemstring'] : '',
            );

            return array ('status' => 1, 'message' => '成功', 'data' => $data);
        } else {
            return array ('status' => 0, 'message' => $ret['errormsg'], 'data' => $ret);
        }
    }

    /**
     * 功能：营业执照识别
     *
     * @param $image_path 可为图片真实url或者相对url
     *
     * @return array
     */
    public function bizlicenseocr($image_path){
        $is_url = substr ($image_path, 0, 4) == "http" ? true : false;

        if ($is_url === true) {
            $post_data = array (
                'app_id' => $this->appid,
                'url'    => $image_path,
                'seq'    => ''
            );
        } else {
            $real_image_path = realpath ($image_path);

            if (!file_exists ($real_image_path)) {
                return array ('status' => 0, 'message' => '找不到图片', 'data' => array ());
            }

            $image_data = file_get_contents ($real_image_path);
            $post_data = array (
                'app_id' => $this->appid,
                'image'  => base64_encode ($image_data),
                'seq'    => ''
            );
        }

        $postUrl = self::OCR_BIZLICENSEOCR_URL;
        $sign = $this->getSign ();

        $req = array (
            'url'     => $postUrl,
            'method'  => 'post',
            'timeout' => 10,
            'data'    => json_encode ($post_data),
            'header'  => array (
                'Authorization:' . $sign,
                'Content-Type:text/json',
                'Expect: ',
            ),
        );

        $rsp = $this->curlPost ($req);
        $ret = json_decode ($rsp, true);
        if ($ret['errorcode'] == '0' && $ret['errormsg'] == 'OK') {
            $data = array (
                'id'       => isset($ret['items'][0]) ? $ret['items'][0]['itemstring'] : '',
                'name'     => isset($ret['items'][1]) ? $ret['items'][1]['itemstring'] : '',
                'address'  => isset($ret['items'][2]) ? $ret['items'][2]['itemstring'] : ''
            );

            return array ('status' => 1, 'message' => '成功', 'data' => $data);
        } else {
            return array ('status' => 0, 'message' => $ret['errormsg'], 'data' => $ret);
        }
    }

    /**
     * curlPost http request
     *
     * @param  array $rq http请求信息
     *                   url        : 请求的url地址
     *                   method     : 请求方法，'get', 'post', 'put', 'delete', 'head'
     *                   data       : 请求数据，如有设置，则method为post
     *                   header     : 需要设置的http头部
     *                   host       : 请求头部host
     *                   timeout    : 请求超时时间
     *                   cert       : ca文件路径
     *                   ssl_version: SSL版本号
     *
     * @return string    http请求响应
     */
    private function curlPost ($rq) {
        $curlHandle = curl_init ();
        curl_setopt ($curlHandle, CURLOPT_URL, $rq['url']);
        switch (true) {
            case isset($rq['method']) && in_array (strtolower ($rq['method']), array ('get', 'post', 'put', 'delete', 'head')):
                $method = strtoupper ($rq['method']);
                break;
            case isset($rq['data']):
                $method = 'POST';
                break;
            default:
                $method = 'GET';
        }
        $header = isset($rq['header']) ? $rq['header'] : array ();
        $header[] = 'Method:' . $method;
        isset($rq['host']) && $header[] = 'Host:' . $rq['host'];
        curl_setopt ($curlHandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        isset($rq['timeout']) && curl_setopt ($curlHandle, CURLOPT_TIMEOUT, $rq['timeout']);
        isset($rq['data']) && in_array ($method, array ('POST', 'PUT')) && curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, $rq['data']);
        $ssl = substr ($rq['url'], 0, 8) == "https://" ? true : false;
        if (isset($rq['cert'])) {
            curl_setopt ($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt ($curlHandle, CURLOPT_CAINFO, $rq['cert']);
            curl_setopt ($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            if (isset($rq['ssl_version'])) {
                curl_setopt ($curlHandle, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt ($curlHandle, CURLOPT_SSLVERSION, 4);
            }
        } else if ($ssl) {
            curl_setopt ($curlHandle, CURLOPT_SSL_VERIFYPEER, false);   //true any ca
            curl_setopt ($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);       //check only host
            if (isset($rq['ssl_version'])) {
                curl_setopt ($curlHandle, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt ($curlHandle, CURLOPT_SSLVERSION, 4);
            }
        }
        $ret = curl_exec ($curlHandle);
//        $this->http_info = curl_getinfo ($curlHandle);
        curl_close ($curlHandle);

        return $ret;
    }

    /**
     * 功能：签名
     *
     * @param string $expired 过期时间
     *
     * @return string
     */
    private function getSign ($expired = '2592000') {

        $now = time ();
        $rdm = rand ();
        $plainText = 'a=' . $this->appid . '&k=' . $this->secretId . '&e=' . (time () + $expired) . '&t=' . $now . '&r=' . $rdm . '&u=' . $this->userid;
        $bin = hash_hmac ("SHA1", $plainText, $this->secretKey, true);
        $bin = $bin . $plainText;
        $sign = base64_encode ($bin);

        return $sign;
    }

    private function statusText ($status) {
        switch ($status) {
            case 0:
                $statusText = 'CONNECT_FAIL';
                break;
            case 200:
                $statusText = 'HTTP OK';
                break;
            case 400:
                $statusText = 'BAD_REQUEST';
                break;
            case 401:
                $statusText = 'UNAUTHORIZED';
                break;
            case 403:
                $statusText = 'FORBIDDEN';
                break;
            case 404:
                $statusText = 'NOTFOUND';
                break;
            case 411:
                $statusText = 'REQ_NOLENGTH';
                break;
            case 423:
                $statusText = 'SERVER_NOTFOUND';
                break;
            case 424:
                $statusText = 'METHOD_NOTFOUND';
                break;
            case 425:
                $statusText = 'REQUEST_OVERFLOW';
                break;
            case 500:
                $statusText = 'INTERNAL_SERVER_ERROR';
                break;
            case 503:
                $statusText = 'SERVICE_UNAVAILABLE';
                break;
            case 504:
                $statusText = 'GATEWAY_TIME_OUT';
                break;
            default:
                $statusText = $status;
                break;
        }

        return $statusText;
    }

}