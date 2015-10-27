<?php
    namespace Manage\Widget;
    use Think\Controller;
    class PageWidget extends Controller {
        private function __initialize () {
            layout(false);
        }
        public function page($id, $name, $label_name, $value, $place_holder){
            $this->id = $id;
            $this->name = $name;
            $this->value = $value;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->display('Page/page');
        }
    }