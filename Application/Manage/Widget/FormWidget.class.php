<?php
    namespace Manage\Widget;
    use Think\Controller;
    class FormWidget extends Controller {
        private function __initialize () {
            layout(false);
        }
        public function input($id, $name, $label_name, $value, $place_holder){
            $this->id = $id;
            $this->name = $name;
            $this->value = $value;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->display('Form/input');
        }

        public function check_box_list ($list, $label_name) {
            $this->list = $list;
            $this->label_name = $label_name;
            $this->display('Form/checkbox_list');
        }

        public function stations_list ($label_name) {
            $this->label_name = $label_name;
            $this->display('Form/stations_list');
        }

        public function hidden_input ($id, $name, $value) {
            $this->id = $id;
            $this->name = $name;
            $this->value = $value;
            $this->display('Form/hidden_input');
        }
    }