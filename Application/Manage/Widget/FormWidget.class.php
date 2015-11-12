<?php
    namespace Manage\Widget;
    use Think\Controller;
    class FormWidget extends Controller {
        private function __initialize () {
            layout(false);
        }
        public function input($id, $name, $label_name, $value, $place_holder, $help_block){
            $this->id = $id;
            $this->name = $name;
            $this->input_value = $value;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->help_block = $help_block;
            $this->display('Form/input');
        }

        public function check_box_list ($list, $label_name, $help_block) {
            $this->list = $list;
            $this->label_name = $label_name;
            $this->help_block = $help_block;
            $this->display('Form/checkbox_list');
        }

        public function checkbox ($id, $name, $label_name, $value, $help_block) {
            $this->id = $id;
            $this->name = $name;
            $this->label_name = $label_name;
            $this->checkbox_value = $value;
            $this->help_block = $help_block;
            $this->display('Form/checkbox');
        }

        public function stations_list ($label_name) {
            $this->label_name = $label_name;
            $this->display('Form/stations_list');
        }

        public function hidden_input ($id, $name, $value) {
            $this->id = $id;
            $this->name = $name;
            $this->hidden_input_value = $value;
            $this->display('Form/hidden_input');
        }

        public function search ($id, $name, $label_name, $place_holder, $direction = 'right', $ico = true) {
            $this->id = $id;
            $this->name = $name;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->direction = $direction;
            $this->ico = $ico;
            $this->display('Form/search');
        }
        
        public function single_select ($id, $name, $options, $label_name, $place_holder, $help_block) {
            $this->select($id, $name, $options, $label_name, $place_holder, $help_block, 'single_select');
        }

        public function multiple_select ($id, $name, $options, $label_name, $place_holder, $help_block) {
            $this->select($id, $name, $options, $label_name, $place_holder, $help_block, 'multiple_select');
        }

        public function select ($id, $name, $options, $label_name, $place_holder, $help_block, $type) {
            $this->id = $id;
            $this->name = $name;
            $this->options = $options;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->help_block = $help_block;
            $this->display('Form/' . $type);
        }

        public function textarea ($id, $name, $label_name, $value, $place_holder, $help_block) {
            $this->id = $id;
            $this->name = $name;
            $this->textarea_value = $value;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->help_block = $help_block;
            $this->display('Form/textarea');
        }

        public function spinner ($id, $name, $label_name, $value, $help_block) {
            $this->id = $id;
            $this->name = $name;
            $this->spinner_value = $value;
            $this->label_name = $label_name;
            $this->help_block = $help_block;
            $this->display('Form/spinner');
        }

        public function ueditor ($id, $name, $label_name, $value, $help_block) {
            $this->id = $id;
            $this->name = $name;
            $this->ueditor_value = $value;
            $this->label_name = $label_name;
            $this->help_block = $help_block;
            $this->display('Form/ueditor');
        }

        public function timer ($id, $name, $label_name, $value) {
            $this->id = $id;
            $this->name = $name;
            $this->timer_value = $value;
            $this->label_name = $label_name;
            $this->help_block = $help_block;
            $this->display('Form/timer');
        }

        public function phone ($id, $name, $label_name, $value, $place_holder) {
            $this->id = $id;
            $this->name = $name;
            $this->phone_value = $value;
            $this->label_name = $label_name;
            $this->place_holder = $place_holder;
            $this->help_block = $help_block;
            $this->display('Form/phone');
        }

        public function upload ($id, $name, $label_name, $value, $queue_id, $is_weixin, $help_block) {
            $this->id = $id;
            $this->name = $name;
            $this->upload_value = $value;
            $this->label_name = $label_name;
            $this->queue_id = $queue_id;
            $this->is_weixin = $is_weixin;
            $this->help_block = $help_block;
            $this->display('Form/upload');
        }
    }