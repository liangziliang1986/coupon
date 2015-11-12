<?php
    namespace Manage\Widget;
    use Think\Controller;
    class UtilWidget extends Controller {
        public function tip () {
            $this->display('Util/tip');
        }
    }