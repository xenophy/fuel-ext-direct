<?php

// {{{ Controller_Extdirect

/**
 * Controller_Extdirect
 *
 * Copyright (c) 2016 Xenophy Entertainment.CO.,LTD All rights Reserved.
 * http://www.xenophy-entertainment.com
 */
class Controller_Extdirect extends Controller {

    // {{{ action_index

    /**
     * Extcute Ext Direct functions
     */
    public function action_index() {

        if (Input::server('HTTP_HOST') === 'localhost') {

            // for local development
            $url = parse_url(Input::server('HTTP_ORIGIN'));
            header('Access-Control-Allow-Credentials: true');

            if (isset($url['port'])) {
                header('Access-Control-Allow-Origin: http://localhost:' . $url['port']);
            } else {
                header('Access-Control-Allow-Origin: http://localhost');
            }

            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS');

        }

        $isForm = false;
        $isUpload = false;

        $post_data = file_get_contents("php://input");

        if ($post_data) {

            header('Content-Type: text/javascript');
            $data = json_decode($post_data);

        } else if (isset($_POST[ 'extAction' ])) { // form post

            $isForm = true;
            $isUpload = $_POST[ 'extUpload' ] == 'true';
            $data = new BogusAction();
            $data->action = $_POST[ 'extAction' ];
            $data->method = $_POST[ 'extMethod' ];
            $data->tid = isset($_POST[ 'extTID' ]) ? $_POST[ 'extTID' ] : null;
            $data->data = array($_POST, $_FILES);

        } else {
            die('Invalid request.');
        }

        $response = null;

        if (is_array($data)) {

            $response = array();

            foreach ($data as $d) {
                $response[] = $this->doRpc($d);
            }

        } else {

            $response = $this->doRpc($data);

        }

        if ($isForm && $isUpload) {

            echo '<html><body><textarea>';
            echo json_encode($response);
            echo '</textarea></body></html>';

        } else {

            echo json_encode($response);

        }

    }

    // }}}
    // {{{ doRpc

    private function doRpc($cdata) {

        $API = $this->get_specs();

        // get direct classes directory name
        $classes_dirname = \Config::get('extdirect.classes_dirname', 'direct');

        // get class name root (default is 'Direct' by config)
        $cls_name_root = ucfirst(strtolower($classes_dirname));

        try {

            if (!isset($API[ $cdata->action ])) {
                throw new Exception('Call to undefined action: ' . $cdata->action);
            }

            $action = $cdata->action;
            $a = $API[ $action ];

            $method = $cdata->method;
            $mdef = $a[ 'methods' ][ $method ];

            if (!$mdef) {
                throw new Exception("Call to undefined method: $method " .
                    "in action $action");
            }

            $r = array(
                'type' => 'rpc',
                'tid' => $cdata->tid,
                'action' => $action,
                'method' => $method
            );

            $cls = $cls_name_root . '_' . $action;

            $o = new $cls();

            if (isset($mdef[ 'len' ])) {
                $params = isset($cdata->data) && is_array($cdata->data) ? $cdata->data : array();
            } else {
                $params = array($cdata->data);
            }

            //array_push($params, $cdata->metadata);

            $r[ 'result' ] = call_user_func_array(array($o, $method), $params);
        } catch (Exception $e) {
            $r[ 'type' ] = 'exception';
            $r[ 'message' ] = $e->getMessage();
            $r[ 'where' ] = $e->getTraceAsString();
        }

        return $r;
    }

    // }}}
    // {{{ get_specs

    private function get_specs() {

        // load config
        \Config::load('extdirect', true);

        // get route
        $route = \Config::get('extdirect.route', 'direct');

        // get direct classes directory name
        $classes_dirname = \Config::get('extdirect.classes_dirname', 'direct');

        // create target path
        $target_path = APPPATH . 'classes' . DIRECTORY_SEPARATOR . $classes_dirname;

        // get class files
        $files = $this->get_file_list($target_path);

        // init api definition
        $API = array();

        foreach ($files as $file) {

            // get class name root (default is 'Direct' by config)
            $cls_name_root = array(ucfirst(strtolower($classes_dirname)));

            // get class name
            $cls_name_child = array_map(
                function ($item) {
                    return ucfirst(strtolower(pathinfo($item, PATHINFO_FILENAME)));
                },
                array_filter(explode('/', $this->get_relative_path($target_path, $file)), 'strlen')
            );

            // merge class name
            $cls_name = array_merge($cls_name_root, $cls_name_child);

            // create class name
            $cls_name = implode('_', $cls_name);

            // create class child name
            $cls_name_child = implode('_', $cls_name_child);

            // create reflection class
            $ref = new ReflectionClass($cls_name);

            // create api methods
            $api_methods = array();
            foreach ($ref->getMethods() as $method) {

                if ($method->isPublic() && $method->getDocComment()) {

                    $doc = $method->getDocComment();

                    if (!!preg_match('/@remotable/', $doc)) {

                        $cfg = array(
                            'name' => $method->name,
                            'len' => $method->getNumberOfParameters()
                        );

                        if (!!preg_match('/@formHandler/', $doc)) {
                            $cfg[ 'formHandler' ] = true;
                        }

                        $api_methods[ $method->name ] = $cfg;

                    }
                }
            }

            if (sizeof($api_methods) > 0) {
                $API[ $cls_name_child ] = array('methods' => $api_methods);
            }

        }

        return $API;
    }

    // }}}
    // {{{ action_api

    /**
     * Return Ext Direct Definitions
     */
    public function action_api() {

        $API = $this->get_specs();

        // get route
        $url = \Config::get('extdirect.url', '/direct');

        $actions = array();

        foreach ($API as $aname => &$a) {
            $methods = array();
            foreach ($a[ 'methods' ] as $mname => &$m) {
                if (isset($m[ 'len' ])) {
                    $md = array(
                        'name' => $mname,
                        'len' => $m[ 'len' ]
                    );
                } else {
                    $md = array(
                        'name' => $mname,
                        'params' => $m[ 'params' ]
                    );
                }
                if (isset($m[ 'formHandler' ]) && $m[ 'formHandler' ]) {
                    $md[ 'formHandler' ] = true;
                }
                $methods[] = $md;
            }
            $actions[ $aname ] = $methods;
        }

        $cfg = array(
            'url' => $url,
            'type' => 'remoting',
            'actions' => $actions
        );

        header('Content-Type: text/javascript');
        echo 'var Ext = Ext || {}; Ext.REMOTING_API = ';
        echo json_encode($cfg);
        echo ';';

    }

    // }}}
    // {{{ get_file_list

    /**
     * Get File List
     *
     * @param $dir
     * @return array
     */
    private function get_file_list($dir) {

        $iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($iterator);

        $list = array();

        foreach ($iterator as $fileinfo) {

            if ($fileinfo->isFile()) {
                $list[] = $fileinfo->getPathname();
            }
        }

        return $list;
    }

    // }}}
    // {{{ get_relative_path

    /**
     * Get reletive path method
     *
     * @param $start_dir
     * @param $final_dir
     * @return string
     */
    private function get_relative_path($start_dir, $final_dir) {

        $firstPathParts = explode(DIRECTORY_SEPARATOR, $start_dir);
        $secondPathParts = explode(DIRECTORY_SEPARATOR, $final_dir);

        $sameCounter = 0;

        for ($i = 0; $i < min(count($firstPathParts), count($secondPathParts)); $i++) {
            if (strtolower($firstPathParts[ $i ]) !== strtolower($secondPathParts[ $i ])) {
                break;
            }
            $sameCounter++;
        }

        if ($sameCounter == 0) {
            return $final_dir;
        }

        $newPath = '';

        for ($i = $sameCounter; $i < count($firstPathParts); $i++) {
            if ($i > $sameCounter) {
                $newPath .= DIRECTORY_SEPARATOR;
            }
            $newPath .= "..";
        }

        if (count($newPath) == 0) {
            $newPath = ".";
        }

        for ($i = $sameCounter; $i < count($secondPathParts); $i++) {
            $newPath .= DIRECTORY_SEPARATOR;
            $newPath .= $secondPathParts[ $i ];
        }

        return $newPath;
    }

    // }}}

}

// }}}
// {{{ BogusAction

class BogusAction {

    // {{{ action

    public $action;

    // }}}
    // {{{ method

    public $method;

    // }}}
    // {{{ data

    public $data;

    // }}}
    // {{{ tid

    public $tid;

    // }}}

}
// }}}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
