<?php
class ControllerDesignSeoMeta extends Controller {
    private $error = [];
    protected $route = 'design/seo_meta';
    protected $language_route = 'design/seo_meta';
    protected $view_route = 'design/seo_meta';
    protected $extension_type = 'module';
    protected $permission_route = 'design/seo_meta';
    protected $legacy_permission_route = '';

    public function index() {
        // the template reads text_* directly, so hand it the whole language file
        $data = $this->load->language($this->language_route);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/language');
        $this->load->model('design/layout');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $post = $this->request->post;

            // Templates
            $templates = [];
            foreach ((array)($post['templates'] ?? []) as $tpl) {
                $name = trim($tpl['name'] ?? '');
                if ($name === '') continue;
            $templates[] = [
                'name'    => $name,
                'content' => $this->normalizeTemplateContent($tpl['content'] ?? []),
            ];
        }

            // Assignments
            $assignments = [];
            foreach ((array)($post['assignments'] ?? []) as $row) {
                $type = trim($row['type'] ?? '');
                if ($type === '') continue;

                $assignments[] = [
                    'type'     => $type,
                    'template' => (int)($row['template'] ?? 0),
                ];
            }

            $this->model_setting_setting->editSetting('module_seo_meta', [
                'module_seo_meta_status'      => (int)($post['module_seo_meta_status'] ?? 0),
                'module_seo_meta_templates'   => $templates,
                'module_seo_meta_assignments' => $assignments,
            ]);

            // indexing lives in the shop config so the catalog header can read it
            $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . (int)($post['config_noindex'] ?? 0) . "' WHERE `key` = 'config_noindex'");

            // GA4 — там само, у config: шапка каталогу читає це на кожній сторінці
            $this->saveConfig('config_ga4_id', trim((string)($post['config_ga4_id'] ?? '')));
            $this->saveConfig('config_ga4_debug', (int)($post['config_ga4_debug'] ?? 0));

            $this->registerEvent();

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link(
                $this->route,
                'user_token=' . $this->session->data['user_token'],
                true
            ));
        }

        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['success']       = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $data['action']     = $this->url->link($this->route, 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel']     = $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        // Languages with flag paths
        $raw_languages = $this->model_localisation_language->getLanguages();
        foreach ($raw_languages as &$lang) {
            $originalImage = $lang['image'] ?? '';
            $lang['image'] = '';

            $flagCandidates = [
                ($lang['code'] ?? '') . '.png',
                $originalImage,
            ];

            foreach ($flagCandidates as $candidate) {
                if ($candidate === '') {
                    continue;
                }

                $fullPath = DIR_APPLICATION . 'language/' . $lang['code'] . '/' . $candidate;
                if (is_file($fullPath)) {
                    $lang['image'] = 'language/' . $lang['code'] . '/' . $candidate;
                    break;
                }
            }
        }
        unset($lang);
        $data['languages'] = $raw_languages;

        // OC config meta (Home uses these)
        $data['config_meta_title']       = $this->config->get('config_meta_title') ?: [];
        $data['config_meta_description'] = $this->config->get('config_meta_description') ?: [];
        $data['settings_url'] = $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true);

        $data['module_seo_meta_status'] = $this->config->get('module_seo_meta_status') ?? 1;
        $data['config_noindex'] = (int)$this->config->get('config_noindex');
        $data['config_ga4_id'] = (string)$this->config->get('config_ga4_id');
        $data['config_ga4_debug'] = (int)$this->config->get('config_ga4_debug');
        $data['templates']              = $this->normalizeTemplates($this->config->get('module_seo_meta_templates') ?: []);

        // Assignment targets: only global page groups plus every OpenCart layout.
        $data['page_types'] = [
            'home'    => $this->language->get('text_home'),
            'general' => $this->language->get('text_general'),
        ];

        foreach ($this->model_design_layout->getLayouts() as $layout) {
            $route_query = $this->db->query("
                SELECT route
                FROM `" . DB_PREFIX . "layout_route`
                WHERE layout_id = '" . (int)$layout['layout_id'] . "'
                ORDER BY store_id ASC, route ASC
                LIMIT 1
            ");
            $route = $route_query->num_rows ? $route_query->row['route'] : '';
            $label = $this->language->get('text_layout') . ': ' . $layout['name'];

            if ($route !== '') {
                $label .= ' (' . $route . ')';
            }

            $data['page_types']['layout:' . (int)$layout['layout_id']] = $label;
        }

        $allowed_page_types = array_keys($data['page_types']);
        $data['assignments'] = [];

        foreach ((array)($this->config->get('module_seo_meta_assignments') ?: []) as $assignment) {
            if (in_array($assignment['type'] ?? '', $allowed_page_types, true)) {
                $data['assignments'][] = $assignment;
            }
        }

        $data['variable_groups'] = [
            'general' => [
                'label' => $this->language->get('text_general'),
                'items' => [
                    '{name}'      => 'Назва сторінки',
                    '{page_name}' => 'Назва сторінки',
                    '{layout_name}' => 'Назва макету',
                    '{layout_id}' => 'ID макету',
                    '{site_name}' => 'Назва магазину',
                    '{phone}'     => 'Телефон магазину',
                ],
            ],
            'category' => [
                'label' => $this->language->get('text_category'),
                'items' => [
                    '{name}'          => 'Назва категорії',
                    '{category_name}' => 'Назва категорії',
                    '{parent_name}'   => 'Батьківська категорія',
                    '{root_name}'     => 'Головна категорія',
                    '{layout_name}'   => 'Назва макету',
                    '{layout_id}'     => 'ID макету',
                    '{site_name}'     => 'Назва магазину',
                    '{phone}'         => 'Телефон магазину',
                ],
            ],
            'product' => [
                'label' => $this->language->get('text_product'),
                'items' => [
                    '{name}'         => 'Назва товару',
                    '{product_name}' => 'Назва товару',
                    '{type}'         => 'Тип продукту',
                    '{product_type}' => 'Тип продукту',
                    '{price}'        => 'Ціна',
                    '{brand}'        => 'Бренд',
                    '{model}'        => 'Модель',
                    '{category_name}' => 'Категорія товару',
                    '{layout_name}'  => 'Назва макету',
                    '{layout_id}'    => 'ID макету',
                    '{site_name}'    => 'Назва магазину',
                    '{phone}'        => 'Телефон магазину',
                ],
            ],
            'manufacturer' => [
                'label' => $this->language->get('text_manufacturer'),
                'items' => [
                    '{name}'              => 'Назва виробника',
                    '{manufacturer_name}' => 'Назва виробника',
                    '{layout_name}'       => 'Назва макету',
                    '{layout_id}'         => 'ID макету',
                    '{site_name}'         => 'Назва магазину',
                    '{phone}'             => 'Телефон магазину',
                ],
            ],
            'blog' => [
                'label' => $this->language->get('text_blog'),
                'items' => [
                    '{name}'      => 'Назва статті',
                    '{post_name}' => 'Назва статті',
                    '{layout_name}' => 'Назва макету',
                    '{layout_id}' => 'ID макету',
                    '{site_name}' => 'Назва магазину',
                    '{phone}'     => 'Телефон магазину',
                ],
            ],
            'search' => [
                'label' => $this->language->get('text_search'),
                'items' => [
                    '{name}'         => 'Назва сторінки',
                    '{search_query}' => 'Пошуковий запит',
                    '{layout_name}'  => 'Назва макету',
                    '{layout_id}'    => 'ID макету',
                    '{site_name}'    => 'Назва магазину',
                    '{phone}'        => 'Телефон магазину',
                ],
            ],
        ];

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home_dash'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
            ['text' => $this->language->get('heading_title'),  'href' => $data['action']],
        ];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->view_route, $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', $this->permission_route) && (!$this->legacy_permission_route || !$this->user->hasPermission('modify', $this->legacy_permission_route))) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    public function install() {
        $this->registerEvent();
    }

    public function uninstall() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'module_seo_meta'");
    }

    // upsert у групу config: editSettingValue робить лише UPDATE, а нових
    // ключів (GA4) у таблиці ще немає — рядок треба створити
    private function saveConfig(string $key, $value): void {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "setting`
            WHERE `key` = '" . $this->db->escape($key) . "' AND store_id = '0'
        ");
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "setting`
            SET store_id = '0',
                `code` = 'config',
                `key` = '" . $this->db->escape($key) . "',
                `value` = '" . $this->db->escape((string)$value) . "',
                serialized = '0'
        ");
    }

    private function registerEvent(): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = 'module_seo_meta'");
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "event`
            SET `code` = 'module_seo_meta',
                `trigger` = 'catalog/controller/*/after',
                `action` = 'extension/module/seo_meta/applyOutput',
                `status` = '1',
                `sort_order` = '99'
        ");
    }

    private function normalizeTemplates(array $templates): array {
        $normalized = [];

        foreach ($templates as $template) {
            $name = trim((string)($template['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'content' => $this->normalizeTemplateContent($template['content'] ?? []),
            ];
        }

        return $normalized;
    }

    private function normalizeTemplateContent($content): array {
        $normalized = [];

        if (!is_array($content)) {
            return $normalized;
        }

        foreach ($content as $language_id => $language_content) {
            if (!is_array($language_content)) {
                continue;
            }

            $legacy_title = trim((string)($language_content['title'] ?? ''));
            $page_title = trim((string)($language_content['page_title'] ?? ''));

            $normalized[(int)$language_id] = [
                'page_title'  => $page_title !== '' ? $page_title : $legacy_title,
                'description' => trim((string)($language_content['description'] ?? '')),
            ];
        }

        return $normalized;
    }
}
