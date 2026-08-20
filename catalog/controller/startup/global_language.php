<?php
class ControllerStartupGlobalLanguage extends Controller {
    public function index(&$route, &$data) {
        // Загружаем глобальный языковой файл
        $this->load->language('global');

        // Делаем строки доступными в Twig
        $data['global'] = $this->language->all();
    }
}
