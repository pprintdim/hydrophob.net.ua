<?php
class ControllerStartupUniversalLanguage extends Controller {
    public function index(&$route, &$data) {
        $this->load->language('global'); // загружаем универсальный файл
        $data['global'] = $this->language->all(); // делаем доступным в Twig
    }
}
