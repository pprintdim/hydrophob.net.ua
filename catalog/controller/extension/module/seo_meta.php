<?php
class ControllerExtensionModuleSeoMeta extends Controller {
    public function index(): array {
        return $this->resolveMeta();
    }

    // Фолбек перед рендером хедера: сторінкам без власних meta підставляємо
    // значення з шаблонів. Ручні мета сутностей мають пріоритет і не чіпаються.
    public function apply(&$route, &$data): void {
        if (!$this->config->get('module_seo_meta_status')) {
            return;
        }

        $meta = $this->resolveMeta();

        if (!empty($meta['description']) && !$this->document->getDescription()) {
            $this->document->setDescription($meta['description']);
        }

        if (!empty($meta['page_title'])) {
            $current = trim((string)$this->document->getTitle());

            // короткий «голий» заголовок без бренду — ознака автогенерації
            if ($current === '' || (mb_stripos($current, 'hydrophob') === false && mb_strlen($current) < 60)) {
                $this->document->setTitle($meta['page_title']);
            }
        }

        // усі сторінки пагінації мали однаковий <title> — дописуємо номер
        $page = $this->getPaginationPage();

        if ($page >= 2) {
            $title = trim((string)$this->document->getTitle());
            // словника модуля тут немає (подія header/before), тож підпис беремо
            // за кодом мови — інакше в title потрапила б сама назва ключа
            $suffix = strpos((string)$this->config->get('config_language'), 'ru') === 0 ? 'страница' : 'сторінка';

            if ($title !== '' && mb_stripos($title, $suffix) === false) {
                $this->document->setTitle($title . ' — ' . $suffix . ' ' . $page);
            }
        }

        // robots рахувався, але нікуди не йшов: службові сторінки (кабінет,
        // кошик, оформлення, пошук) і пагінація ≥2 лишались відкритими для
        // індексації. Кладемо і в заголовок, і в реєстр — шаблон додасть <meta>.
        if (!empty($meta['robots'])) {
            // Контролер сторінки міг сам закрити її від індексації (404,
            // комбінація 2+ фільтрів). Правило контролера строгіше за
            // шаблонне — не перетираємо його на «index».
            $already = $this->registry->has('seo_meta_robots')
                ? (string)$this->registry->get('seo_meta_robots')
                : '';

            if ($already !== '' && stripos($already, 'noindex') !== false && stripos($meta['robots'], 'noindex') === false) {
                return;
            }

            if (!headers_sent()) {
                header('X-Robots-Tag: ' . $meta['robots'], true);
            }

            $this->registry->set('seo_meta_robots', $meta['robots']);
        }
    }

    public function applyOutput(&$route, &$data, &$output): void {
    }

    // H1/опис для сторінки "Активні компоненти" за поточною комбінацією фільтра —
    // викликається напряму з product/components.php (не є частиною title/description потоку).
    public function getComponentsContent(): array {
        $result = ['heading_title' => '', 'description' => '', 'meta_title' => '', 'meta_description' => ''];

        if (!$this->componentsTableExists()) {
            return $result;
        }

        $params = $this->getCurrentOcfilterParams();
        $filter_id = $this->getPrimaryOcfilterFilterId($params);

        if ($filter_id <= 0 || $params === '') {
            return $result;
        }

        $language_id = (int)$this->config->get('config_language_id');

        $query = $this->db->query("
            SELECT scd.heading_title, scd.description, scd.meta_title, scd.meta_description
            FROM `" . DB_PREFIX . "seo_meta_components` sc
            LEFT JOIN `" . DB_PREFIX . "seo_meta_components_description` scd ON scd.seo_meta_components_id = sc.seo_meta_components_id
            WHERE sc.entity_id = '" . $filter_id . "'
              AND sc.entity_key = '" . $this->db->escape($params) . "'
              AND sc.status = '1'
              AND scd.language_id = '" . $language_id . "'
            LIMIT 1
        ");

        if ($query->num_rows) {
            $result['heading_title'] = html_entity_decode((string)$query->row['heading_title'], ENT_QUOTES, 'UTF-8');
            $result['description'] = (string)$query->row['description'];
            $result['meta_title'] = html_entity_decode((string)$query->row['meta_title'], ENT_QUOTES, 'UTF-8');
            $result['meta_description'] = html_entity_decode((string)$query->row['meta_description'], ENT_QUOTES, 'UTF-8');
        }

        return $result;
    }

    private function componentsTableExists(): bool {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . "seo_meta_components") . "'");
        $exists = (bool)$query->num_rows;

        return $exists;
    }

    private function resolveMeta(?array $context = null): array {
        $document_title = $this->normalizeDocumentValue($this->document->getTitle());
        $document_description = $this->normalizeDocumentValue($this->document->getDescription());

        $result = [
            'title'       => $document_title,
            'description' => $document_description,
            'robots'      => '',
            'canonical'   => '',
            'layout_id'   => 0,
            'layout_name' => '',
        ];

        $context = $context ?: $this->getContext();

        if (!$context) {
            return $result;
        }

        $result['layout_id'] = (int)($context['layout_id'] ?? 0);
        $result['layout_name'] = (string)($context['layout_name'] ?? '');
        $result['robots'] = $this->resolveRobots($context);
        $result['canonical'] = $this->resolveCanonical($context);

        if (!$this->config->get('module_seo_meta_status')) {
            return $result;
        }

        $manual_title = trim((string)($context['manual_title'] ?? ''));
        $manual_description = trim((string)($context['manual_description'] ?? ''));
        $is_ocfilter_page = (($context['type'] ?? '') === 'ocfilter_page');
        // "Активні компоненти" (product/components) так само мають поле-за-полем
        // перекриття для конкретної комбінації фільтра — без чекбокса, як OCFilter-сторінки.
        $has_field_override = $is_ocfilter_page || (($context['type'] ?? '') === 'components');

        // OCFilter-сторінки не мають чекбокса "брати meta з полів" — для них
        // заповнені поля самої сторінки завжди перекривають шаблон, поле за полем,
        // без потреби вмикати щось окремо.
        if (!$is_ocfilter_page && $this->shouldUseEntityMeta($context)) {
            if ($manual_title !== '') {
                $result['title'] = $manual_title;
            }

            if ($manual_description !== '') {
                $result['description'] = $manual_description;
            }

            return $this->applyPaginationMeta($result, $context);
        }

        $template = $this->getTemplateForContext($context);

        if ($template) {
            $values = $this->getValues($context);
            $content = $this->getTemplateContent($template);
            $template_page_title = trim((string)($content['page_title'] ?? ($content['title'] ?? '')));
            $template_description = trim((string)($content['description'] ?? ''));

            if ($template_page_title !== '') {
                $rendered_title = $this->renderTemplate($template_page_title, $values);

                if ($rendered_title !== '') {
                    $result['title'] = $rendered_title;
                }
            }

            if ($template_description !== '') {
                $result['description'] = $this->renderTemplate($template_description, $values);
            }
        }

        if ($has_field_override) {
            if ($manual_title !== '') {
                $result['title'] = $manual_title;
            }

            if ($manual_description !== '') {
                $result['description'] = $manual_description;
            }
        }

        return $this->applyPaginationMeta($result, $context);
    }

    private function getContext(string $event_route = ''): ?array {
        $route = $event_route ?: ($this->request->get['route'] ?? 'common/home');

        if ($route === '') {
            $route = 'common/home';
        }

        if ($route === 'common/home') {
            return null;
        }

        $context = [
            'route'              => $route,
            'type'               => '',
            'target_id'          => 0,
            'name'               => '',
            'manual_title'       => '',
            'manual_description' => '',
            'data'               => [],
        ];

        if ($route === 'product/category') {
            $page = [];

            // Сторінку OCFilter можна досягти або через ocfilter_page_id (плаский keyword),
            // або через параметри фільтра (ocf=F...) на "красивому" складеному URL —
            // другий випадок не має ocfilter_page_id в GET, тому беремо вже готовий
            // результат зіставлення параметрів з самого вендорського OCFilter.
            if (!empty($this->request->get['ocfilter_page_id'])) {
                $page = $this->getOcfilterPage((int)$this->request->get['ocfilter_page_id']);
            } elseif ($this->registry->has('ocfilter')) {
                $ocf_page_info = $this->ocfilter->seo->getPageInfo();

                if ($ocf_page_info) {
                    $page = $ocf_page_info;
                }
            }

            if ($page) {
                $context['type'] = 'ocfilter_page';
                $context['robots_entity_type'] = 'ocfilter_page';
                $context['target_id'] = (int)($page['page_id'] ?? 0);
                $context['name'] = $page['name'] ?? '';
                $context['manual_title'] = $page['meta_title'] ?? '';
                $context['manual_description'] = $page['meta_description'] ?? '';
                $context['data'] = $page;

                return $this->withLayout($context);
            }
        }

        if ($route === 'product/category' && !empty($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)end($parts);

            $this->load->model('catalog/category');
            $category = $this->model_catalog_category->getCategory($category_id);

            if (!$category) {
                return null;
            }

            $context['type'] = 'category';
            $context['robots_entity_type'] = 'category';
            $context['target_id'] = $category_id;
            $context['name'] = $category['name'] ?? '';
            $context['manual_title'] = $category['meta_title'] ?? '';
            $context['manual_description'] = $category['meta_description'] ?? '';
            $context['data'] = $category;
            $context['data']['path'] = $parts;

            return $this->withLayout($context);
        }

        if ($route === 'product/product' && !empty($this->request->get['product_id'])) {
            $this->load->model('catalog/product');
            $product = $this->model_catalog_product->getProduct((int)$this->request->get['product_id']);

            if (!$product) {
                return null;
            }

            $context['type'] = 'product';
            $context['target_id'] = (int)$product['product_id'];
            $context['name'] = $product['name'] ?? '';
            $context['manual_title'] = $product['meta_title'] ?? '';
            $context['manual_description'] = $product['meta_description'] ?? '';
            $context['data'] = $product;
            $context['data']['category_name'] = $this->getProductCategoryName((int)$product['product_id']);

            return $this->withLayout($context);
        }

        if ($route === 'product/manufacturer/info' && !empty($this->request->get['manufacturer_id'])) {
            $this->load->model('catalog/manufacturer');
            $manufacturer = $this->model_catalog_manufacturer->getManufacturer((int)$this->request->get['manufacturer_id']);

            if (!$manufacturer) {
                return null;
            }

            $context['type'] = 'manufacturer';
            $context['target_id'] = (int)$manufacturer['manufacturer_id'];
            $context['name'] = $manufacturer['name'] ?? '';
            $context['manual_title'] = $manufacturer['meta_title'] ?? '';
            $context['manual_description'] = $manufacturer['meta_description'] ?? '';
            $context['data'] = $manufacturer;

            return $this->withLayout($context);
        }

        if ($route === 'extension/post' && !empty($this->request->get['post_id'])) {
            $this->load->model('extension/blog');
            $post = $this->model_extension_blog->getblog((int)$this->request->get['post_id']);

            if (!$post) {
                return null;
            }

            $context['type'] = 'blog';
            $context['robots_entity_type'] = 'blog_post';
            $context['target_id'] = (int)$post['post_id'];
            $context['name'] = $post['name'] ?? '';
            $context['manual_title'] = $post['meta_title'] ?? '';
            $context['manual_description'] = $post['meta_description'] ?? '';
            $context['data'] = $post;

            return $this->withLayout($context);
        }

        if ($route === 'extension/blogcategory') {
            $context['type'] = 'blog';
            $context['name'] = $this->normalizeDocumentValue($this->document->getTitle()) ?: 'Блог';

            if (!empty($this->request->get['bpath'])) {
                $parts = explode('_', (string)$this->request->get['bpath']);
                $blog_category_id = (int)end($parts);

                $this->load->model('extension/blogcategory');
                $blog_category = $this->model_extension_blogcategory->getblog_category($blog_category_id);

                if ($blog_category) {
                    $context['target_id'] = $blog_category_id;
                    $context['robots_entity_type'] = 'blog_category';
                    $context['name'] = $blog_category['name'] ?? $context['name'];
                    $context['manual_title'] = $blog_category['meta_title'] ?? '';
                    $context['manual_description'] = $blog_category['meta_description'] ?? '';
                    $context['data'] = $blog_category;
                    $context['data']['path'] = $parts;
                }
            }

            return $this->withLayout($context);
        }

        if (in_array($route, ['product/catalog', 'product/latest', 'product/special'], true)) {
            $names = [
                'product/catalog' => 'Каталог',
                'product/latest'  => 'Новинки',
                'product/special' => 'Акції',
            ];

            $context['type'] = 'general';
            $context['name'] = $this->normalizeDocumentValue($this->document->getTitle()) ?: $names[$route];

            return $this->withLayout($context);
        }

        if ($route === 'product/components') {
            $menu = $this->config->get('module_components_menu') ?: [];
            $language_id = (int)$this->config->get('config_language_id');

            $context['type'] = 'components';
            $context['name'] = $menu['name'][$language_id] ?? 'Компоненти';

            $components_content = $this->getComponentsContent();
            $context['manual_title'] = $components_content['meta_title'] ?? '';
            $context['manual_description'] = $components_content['meta_description'] ?? '';

            return $this->withLayout($context);
        }

        if ($route === 'product/search') {
            $context['type'] = 'search';
            $context['name'] = 'Пошук';
            $context['data']['search_query'] = $this->request->get['search'] ?? '';

            return $this->withLayout($context);
        }

        if ($route === 'extension/home') {
            $context['type'] = 'blog';
            $context['name'] = $this->normalizeDocumentValue($this->document->getTitle()) ?: 'Блог';

            return $this->withLayout($context);
        }

        if ($route === 'extension/search') {
            $context['type'] = 'search';
            $context['name'] = 'Пошук';
            $context['data']['search_query'] = $this->request->get['bsearch'] ?? ($this->request->get['tag'] ?? '');

            return $this->withLayout($context);
        }

        if (strpos($route, 'information/') === 0) {
            $context['type'] = 'general';

            if ($route === 'information/information' && !empty($this->request->get['information_id'])) {
                $this->load->model('catalog/information');
                $info = $this->model_catalog_information->getInformation((int)$this->request->get['information_id']);

                if (!$info) {
                    return null;
                }

                $context['type'] = 'information';
                $context['robots_entity_type'] = 'information';
                $context['target_id'] = (int)$this->request->get['information_id'];
                $context['name'] = $info['title'] ?? '';
                $context['manual_title'] = $info['meta_title'] ?? '';
                $context['manual_description'] = $info['meta_description'] ?? '';
                $context['data'] = $info;
            } else {
                $context['name'] = $this->document->getTitle() ?: $this->config->get('config_name');
            }

            return $this->withLayout($context);
        }

        $context['type'] = 'general';
        $context['name'] = $this->normalizeDocumentValue($this->document->getTitle());

        if ($context['name'] === '') {
            $context['name'] = $this->config->get('config_name');
        }

        return $this->withLayout($context);
    }

    private function getTemplateForContext(array $context): ?array {
        $type = (string)($context['type'] ?? '');

        if ($type !== 'general' && in_array($type, ['category', 'product', 'manufacturer', 'information', 'blog', 'ocfilter_page', 'components', 'search'], true)) {
            $template = $this->getTemplateByAssignmentType($type);

            if ($template) {
                return $template;
            }

            $template = $this->getTemplateByNameForType($type);

            if ($template) {
                return $template;
            }
        }

        if (!empty($context['layout_id'])) {
            $template = $this->getTemplateByAssignmentType('layout:' . (int)$context['layout_id']);

            if ($template) {
                return $template;
            }
        }

        $template = $this->getTemplateByAssignmentType('general');

        if ($template) {
            return $template;
        }

        return $this->getDefaultTemplate($type);
    }

    private function getTemplateByAssignmentType(string $type): ?array {
        $templates = $this->config->get('module_seo_meta_templates') ?: [];
        $assignments = $this->config->get('module_seo_meta_assignments') ?: [];

        foreach ($assignments as $assignment) {
            if (($assignment['type'] ?? '') === $type
                && isset($templates[(int)($assignment['template'] ?? -1)])) {
                return $templates[(int)$assignment['template']];
            }
        }

        return null;
    }

    private function getTemplateByNameForType(string $type): ?array {
        $templates = $this->config->get('module_seo_meta_templates') ?: [];
        $needles = [
            'category'     => ['категор', 'category'],
            'product'      => ['продукт', 'товар', 'product'],
            'manufacturer' => ['бренд', 'виробник', 'manufacturer', 'brand'],
            'information'  => ['інфо', 'инфо', 'information'],
            'blog'         => ['блог', 'blog', 'post'],
            'ocfilter_page' => ['ocfilter', 'фільтр', 'filter'],
            'components'   => ['компонент', 'component'],
            'search'       => ['пошук', 'поиск', 'search'],
        ];

        if (empty($needles[$type])) {
            return null;
        }

        foreach ((array)$templates as $template) {
            $name = $this->normalizeTemplateName((string)($template['name'] ?? ''));

            foreach ($needles[$type] as $needle) {
                if ($name !== '' && strpos($name, $needle) !== false) {
                    return $template;
                }
            }
        }

        return null;
    }

    private function normalizeTemplateName(string $name): string {
        $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
        $name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : utf8_strtolower($name);

        return trim($name);
    }

    private function shouldUseEntityMeta(array $context): bool {
        return !empty($context['data']['seo_meta_use_entity_meta']);
    }

    private function resolveRobots(array $context): string {
        $forced = $this->getForcedTechnicalRobots($context);

        if ($forced !== '') {
            return $forced;
        }

        $manual = $this->getManualRobots($context);

        if ($manual !== '') {
            // Ручне правило керує і HTTP-заголовком: OCFilter (system/library/ocfilter/seo.php)
            // ще на startup шле X-Robots-Tag: noindex, nofollow для незареєстрованих
            // фільтр-наборів — без заміни заголовок суперечить meta, і Google бере
            // суворіший сигнал. replace=true перекриває раніше надісланий заголовок.
            if (!headers_sent()) {
                header('X-Robots-Tag: ' . $manual, true);
            }

            return $manual;
        }

        return $this->getDefaultRobots($context);
    }

    private function resolveCanonical(array $context): string {
        $manual = $this->getManualCanonical($context);

        if ($manual !== '') {
            return $manual;
        }

        return $this->getDefaultCanonical($context);
    }

    private function getManualCanonical(array $context): string {
        $language_sql = $this->getLanguageRuleSql('seo_meta_canonical');

        // OCFilter-специфічний пошук — правило прив'язане до конкретного ФІЛЬТРА
        // (entity_id = filter_id), не до категорії — той самий фільтр+значення
        // дають однаковий canonical незалежно від того, з якої категорії на нього зайшли.
        if ($this->isOcfilterRequest($context) && $this->canonicalTableExists()) {
            $params = $this->getCurrentOcfilterParams();
            $filter_id = $this->getPrimaryOcfilterFilterId($params);

            if ($filter_id > 0 && $params !== '') {
                $query = $this->db->query("
                    SELECT canonical_url
                    FROM `" . DB_PREFIX . "seo_meta_canonical`
                    WHERE entity_type = 'ocfilter_filter_set'
                      AND entity_id = '" . $filter_id . "'
                      AND entity_key = '" . $this->db->escape($params) . "'
                      " . $language_sql['where'] . "
                      AND status = '1'
                    " . $language_sql['order'] . "
                    LIMIT 1
                ");

                if ($query->num_rows) {
                    return $this->normalizeCanonical((string)$query->row['canonical_url']);
                }
            }
        }

        $entity_type = (string)($context['robots_entity_type'] ?? $context['type'] ?? '');
        $entity_id = (int)($context['target_id'] ?? 0);

        if ($entity_type === '' || $entity_id <= 0 || !$this->canonicalTableExists()) {
            return '';
        }

        $query = $this->db->query("
            SELECT canonical_url
            FROM `" . DB_PREFIX . "seo_meta_canonical`
            WHERE entity_type = '" . $this->db->escape($entity_type) . "'
              AND entity_id = '" . $entity_id . "'
              AND entity_key = ''
              " . $language_sql['where'] . "
              AND status = '1'
            " . $language_sql['order'] . "
            LIMIT 1
        ");

        if (!$query->num_rows) {
            return '';
        }

        return $this->normalizeCanonical((string)$query->row['canonical_url']);
    }

    private function getDefaultCanonical(array $context): string {
        if (!$this->isOcfilterRequest($context)) {
            return '';
        }

        $category_id = $this->getContextCategoryId($context);

        if ($category_id <= 0) {
            return '';
        }

        return html_entity_decode($this->url->link('product/category', 'path=' . $category_id, true), ENT_QUOTES, 'UTF-8');
    }

    private function getManualRobots(array $context): string {
        $language_sql = $this->getLanguageRuleSql('seo_meta_robots');

        // OCFilter-специфічний пошук — правило прив'язане до конкретного ФІЛЬТРА
        // (entity_id = filter_id), не до категорії — той самий фільтр+значення
        // дають однаковий robots незалежно від того, з якої категорії на нього зайшли.
        if ($this->isOcfilterRequest($context) && $this->robotsTableExists()) {
            $params = $this->getCurrentOcfilterParams();
            $filter_id = $this->getPrimaryOcfilterFilterId($params);

            if ($filter_id > 0 && $params !== '') {
                $query = $this->db->query("
                    SELECT robots
                    FROM `" . DB_PREFIX . "seo_meta_robots`
                    WHERE entity_type = 'ocfilter_filter_set'
                      AND entity_id = '" . $filter_id . "'
                      AND entity_key = '" . $this->db->escape($params) . "'
                      " . $language_sql['where'] . "
                      AND status = '1'
                    " . $language_sql['order'] . "
                    LIMIT 1
                ");

                if ($query->num_rows) {
                    return $this->normalizeRobots((string)$query->row['robots']);
                }
            }
        }

        $entity_type = (string)($context['robots_entity_type'] ?? $context['type'] ?? '');
        $entity_id = (int)($context['target_id'] ?? 0);

        if ($entity_type === '' || $entity_id <= 0 || !$this->robotsTableExists()) {
            return '';
        }

        $query = $this->db->query("
            SELECT robots
            FROM `" . DB_PREFIX . "seo_meta_robots`
            WHERE entity_type = '" . $this->db->escape($entity_type) . "'
              AND entity_id = '" . $entity_id . "'
              AND entity_key = ''
              " . $language_sql['where'] . "
              AND status = '1'
            " . $language_sql['order'] . "
            LIMIT 1
        ");

        if (!$query->num_rows) {
            return '';
        }

        return $this->normalizeRobots((string)$query->row['robots']);
    }

    private function getDefaultRobots(array $context): string {
        if ($this->isOcfilterRequest($context)) {
            return 'noindex, follow';
        }

        $forced = $this->getForcedTechnicalRobots($context);

        if ($forced !== '') {
            return $forced;
        }

        $route = (string)($context['route'] ?? ($this->request->get['route'] ?? ''));

        if (in_array($route, ['product/category', 'product/search', 'information/reviews'], true)) {
            $current_page = $this->getPaginationPage();

            if ($current_page >= 2) {
                return 'noindex, follow';
            }
        }

        return '';
    }

    private function getForcedTechnicalRobots(array $context): string {
        $route = (string)($context['route'] ?? ($this->request->get['route'] ?? ''));

        $noindex_routes = [
            'product/compare',
            'product/search',
            'extension/search',
            'common/language/language',
            'common/currency/currency',
            'checkout/success',
            'checkout/failure',
            'checkout/payment_retry',
        ];
        $noindex_route_prefixes = [
            'account/',
            'checkout/',
            'affiliate/',
            'api/',
            'tool/',
            'payment/',
            'extension/payment/',
            'extension/credit_card/',
            'extension/recurring/',
        ];
        $noindex_uri_paths = [
            'account',
            'cart',
            'checkout',
            'orders',
            'pay',
            'pay-retry',
            'payment-retry',
            'checkout-success',
            'checkout-failure',
            'order-success',
        ];
        $noindex_uri_prefixes = [
            'account/',
            'checkout/',
            'orders/',
            'pay/',
            'pay-retry/',
            'payment-retry/',
            'order-success/',
        ];

        $noindex = in_array($route, $noindex_routes, true);

        foreach ($noindex_route_prefixes as $route_prefix) {
            if (strpos($route, $route_prefix) === 0) {
                $noindex = true;
                break;
            }
        }

        if (!$noindex && !empty($this->request->server['REQUEST_URI'])) {
            $request_path = trim((string)parse_url($this->request->server['REQUEST_URI'], PHP_URL_PATH), '/');

            if (in_array($request_path, $noindex_uri_paths, true)) {
                $noindex = true;
            } else {
                foreach ($noindex_uri_prefixes as $uri_prefix) {
                    if (strpos($request_path, $uri_prefix) === 0) {
                        $noindex = true;
                        break;
                    }
                }
            }
        }

        $catalog_sort_routes = [
            'product/category',
            'product/search',
            'product/manufacturer/info',
            'product/catalog',
            'product/latest',
            'product/special',
            'product/components',
            'extension/search',
        ];

        if (!$noindex && in_array($route, $catalog_sort_routes, true)) {
            if (!empty($this->request->get['sort']) || !empty($this->request->get['order']) || !empty($this->request->get['limit'])) {
                $noindex = true;
            }
        }

        return $noindex ? 'noindex, nofollow' : '';
    }

    private function normalizeRobots(string $robots): string {
        $robots = strtolower(trim($robots));
        $allowed = [
            'index, follow',
            'noindex, follow',
            'noindex, nofollow',
            'index, nofollow',
        ];

        return in_array($robots, $allowed, true) ? $robots : '';
    }

    private function robotsTableExists(): bool {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . "seo_meta_robots") . "'");
        $exists = (bool)$query->num_rows;

        if ($exists) {
            $column_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "seo_meta_robots` LIKE 'entity_key'");
            $exists = (bool)$column_query->num_rows;
        }

        return $exists;
    }

    private function canonicalTableExists(): bool {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . "seo_meta_canonical") . "'");
        $exists = (bool)$query->num_rows;

        if ($exists) {
            $column_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "seo_meta_canonical` LIKE 'canonical_url'");
            $exists = (bool)$column_query->num_rows;
        }

        return $exists;
    }

    private function getLanguageRuleSql(string $table): array {
        if (!$this->hasLanguageColumn($table)) {
            return [
                'where' => '',
                'order' => '',
            ];
        }

        $language_id = (int)$this->config->get('config_language_id');

        return [
            'where' => "AND language_id IN ('0', '" . $language_id . "')",
            'order' => "ORDER BY language_id DESC",
        ];
    }

    private function hasLanguageColumn(string $table): bool {
        static $cache = [];

        $table = preg_replace('/[^a-z0-9_]/i', '', $table);

        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . $table . "` LIKE 'language_id'");
        $cache[$table] = (bool)$query->num_rows;

        return $cache[$table];
    }

    private function normalizeCanonical(string $url): string {
        $url = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));

        if (!preg_match('~^https?://[^\s<>"\']+$~i', $url)) {
            return '';
        }

        return $url;
    }

    private function isOcfilterRequest(array $context): bool {
        if (($context['type'] ?? '') === 'ocfilter_page' || !empty($this->request->get['ocfilter_page_id'])) {
            return true;
        }

        foreach (['ocf', 'ocfilter', 'filter_ocfilter'] as $key) {
            if (!empty($this->request->get[$key])) {
                return true;
            }
        }

        if (!empty($this->request->server['REQUEST_URI']) && preg_match('~(?:^|[/?&])ocf(?:=|/)~', (string)$this->request->server['REQUEST_URI'])) {
            return true;
        }

        return false;
    }

    private function getContextCategoryId(array $context): int {
        if (($context['type'] ?? '') === 'category') {
            return (int)($context['target_id'] ?? 0);
        }

        if (($context['type'] ?? '') === 'ocfilter_page' && !empty($context['data']['category_id'])) {
            return (int)$context['data']['category_id'];
        }

        if (!empty($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            return (int)end($parts);
        }

        return 0;
    }

    private function getCurrentOcfilterParams(): string {
        $params = '';

        if ($this->registry->has('ocfilter')) {
            $index = $this->ocfilter->params->getIndex();

            if (!empty($this->request->get[$index])) {
                $params = (string)$this->request->get[$index];
            } elseif ($this->ocfilter->seo->getParams()) {
                $params = (string)$this->ocfilter->seo->getParams();
            }
        }

        if ($params === '') {
            foreach (['ocf', 'filter_ocfilter'] as $key) {
                if (!empty($this->request->get[$key])) {
                    $params = (string)$this->request->get[$key];
                    break;
                }
            }
        }

        return $this->normalizeOcfilterParams($params);
    }

    private function normalizeOcfilterParams(string $params): string {
        $params = html_entity_decode(rawurldecode(trim($params)), ENT_QUOTES, 'UTF-8');
        $params = preg_replace('~^[?&]*(?:ocf|filter_ocfilter)=~i', '', $params);
        $params = preg_replace('/\s+/', '', $params);

        return trim((string)$params);
    }

    // Бере filter_id першої групи F{id}S{source}V{...} з рядка параметрів —
    // seo_meta_robots/seo_meta_canonical для типу ocfilter_filter_set прив'язані
    // саме до filter_id (entity_id), а не до категорії.
    private function getPrimaryOcfilterFilterId(string $params): int {
        if ($params !== '' && preg_match('~^F(\d+)S(\d+)~', $params, $m)) {
            return (int)$m[1];
        }

        return 0;
    }

    private function applyPaginationMeta(array $meta, array $context): array {
        $page = $this->getPaginationPage();

        if ($page <= 1 || !$this->isPaginationMetaContext($context)) {
            return $meta;
        }

        $base_title = $this->stripTitleSiteSuffix($this->stripPaginationSuffix((string)($meta['title'] ?? '')));
        $base_description = $this->stripPaginationSuffix((string)($meta['description'] ?? ''));

        if ($base_title !== '') {
            $meta['title'] = $base_title . ' | ' . $this->getSiteName() . ' - Сторінка ' . $page;
        }

        if ($base_description !== '') {
            $meta['description'] = rtrim($base_description, " \t\n\r\0\x0B.") . ' — Сторінка ' . $page . '.';
        }

        return $meta;
    }

    private function getPaginationPage(): int {
        if (isset($this->request->get['page'])) {
            return max(1, (int)$this->request->get['page']);
        }

        $route = (string)($this->request->get['_route_'] ?? '');

        if ($route !== '' && preg_match('~(?:^|/)page-(\d+)(?:/|$)~', $route, $match)) {
            return max(1, (int)$match[1]);
        }

        $request_uri = (string)($_SERVER['REQUEST_URI'] ?? '');

        if ($request_uri !== '' && preg_match('~(?:^|/)page-(\d+)(?:[/?#]|$)~', $request_uri, $match)) {
            return max(1, (int)$match[1]);
        }

        return 1;
    }

    private function isPaginationMetaContext(array $context): bool {
        $route = (string)($context['route'] ?? '');

        if ($route === 'product/product' || strpos($route, '/review') !== false) {
            return false;
        }

        return in_array((string)($context['type'] ?? ''), ['category', 'manufacturer', 'blog', 'components', 'search', 'general'], true);
    }

    private function stripPaginationSuffix(string $value): string {
        $value = trim($value);
        $site = preg_quote($this->getSiteName(), '/');
        $value = preg_replace('/\s+[—-]\s+Сторінка\s+\d+(?:\s*(?:\||—|-)\s*' . $site . ')?\.?\s*$/iu', '', $value);
        $value = preg_replace('/\s+[—-]\s+Страница\s+\d+(?:\s*(?:\||—|-)\s*' . $site . ')?\.?\s*$/iu', '', $value);
        $value = preg_replace('/\s+[—-]\s+Page\s+\d+(?:\s*(?:\||—|-)\s*' . $site . ')?\.?\s*$/iu', '', $value);

        return trim($value);
    }

    private function stripTitleSiteSuffix(string $value): string {
        $value = trim($value);
        $site = preg_quote($this->getSiteName(), '/');

        return trim((string)preg_replace('/\s*(?:\||—|-)\s*' . $site . '\.?\s*$/iu', '', $value));
    }

    private function getSiteName(): string {
        $site_name = trim((string)$this->config->get('config_name'));

        return $site_name !== '' ? $site_name : 'Hydrophob';
    }

    private function withLayout(array $context): array {
        $context['layout_id'] = $this->getCurrentLayoutId($context);
        $context['layout_name'] = $this->getLayoutName((int)$context['layout_id']);

        return $context;
    }

    private function getLayoutName(int $layout_id): string {
        if (!$layout_id) {
            return '';
        }

        $query = $this->db->query("SELECT name FROM `" . DB_PREFIX . "layout` WHERE layout_id = '" . (int)$layout_id . "' LIMIT 1");

        return $query->num_rows ? $query->row['name'] : '';
    }

    private function getCurrentLayoutId(array $context): int {
        $route = $context['route'] ?? 'common/home';
        $layout_id = 0;

        if ($route === 'product/category' && !empty($context['target_id'])) {
            if (($context['type'] ?? '') === 'ocfilter_page') {
                $layout_id = $this->getOcfilterPageLayoutId((int)$context['target_id']);
            } else {
                $this->load->model('catalog/category');
                $layout_id = (int)$this->model_catalog_category->getCategoryLayoutId((int)$context['target_id']);
            }
        }

        if ($route === 'product/product' && !empty($context['target_id'])) {
            $this->load->model('catalog/product');
            $layout_id = (int)$this->model_catalog_product->getProductLayoutId((int)$context['target_id']);
        }

        if ($route === 'information/information' && !empty($context['target_id'])) {
            $this->load->model('catalog/information');
            $layout_id = (int)$this->model_catalog_information->getInformationLayoutId((int)$context['target_id']);
        }

        if ($route === 'extension/blogcategory' && !empty($context['target_id'])) {
            $this->load->model('extension/blogcategory');
            $layout_id = (int)$this->model_extension_blogcategory->getblog_categoryLayoutId((int)$context['target_id']);
        }

        if (!$layout_id) {
            $this->load->model('design/layout');
            $layout_id = (int)$this->model_design_layout->getLayout($route);
        }

        if (!$layout_id) {
            $layout_id = (int)$this->config->get('config_layout_id');
        }

        return $layout_id;
    }

    private function getDefaultTemplate(string $type): ?array {
        $templates = [
            'general' => [
                'content' => [
                    'page_title'  => '{name} | Hydrophob',
                    'description' => '{name} — гідрофобні покриття Hydrophob для авто, будматеріалів, одягу та взуття. Власне виробництво, доставка по Україні.',
                ],
            ],
            'category' => [
                'content' => [
                    'page_title'  => '{category_name} — купити в Україні: ціни, характеристики | Hydrophob',
                    'description' => '{category_name} від виробника Hydrophob ✅ нанокерамічні покриття та гідрофобізатори ✅ ефект до 24 місяців ✅ відправка протягом 1 робочого дня по всій Україні.',
                ],
            ],
            'product' => [
                'content' => [
                    'page_title'  => '{product_name} — купити за {price} грн | Hydrophob',
                    'description' => '{product_name} ✅ від виробника Hydrophob ✅ ціна {price} грн ✅ просте нанесення без обладнання ✅ безпечно для лаку, скла й пластику ✅ доставка по Україні.',
                ],
            ],
            'blog' => [
                'content' => [
                    'page_title'  => '{post_name} | Блог Hydrophob',
                    'description' => '{post_name} — практичні поради від виробника гідрофобних покриттів Hydrophob: догляд, захист поверхонь і технологія нанесення.',
                ],
            ],
            'information' => [
                'content' => [
                    'page_title'  => '{name} | Hydrophob',
                    'description' => '{name} — Hydrophob, виробник нанокерамічних покриттів і гідрофобізаторів. Доставка по Україні, консультація з підбору засобу.',
                ],
            ],
            'manufacturer' => [
                'content' => [
                    'page_title'  => '{manufacturer_name} — продукція в Україні | Hydrophob',
                    'description' => '{manufacturer_name} — асортимент і ціни на Hydrophob. Гідрофобні покриття власного виробництва, доставка по всій Україні.',
                ],
            ],
            'components' => [
                'content' => [
                    'page_title'  => '{name} | Hydrophob',
                    'description' => '{name} — засоби Hydrophob для захисту поверхонь від води, бруду й реагентів. Замовляйте на сайті виробника.',
                ],
            ],
        ];

        return $templates[$type] ?? null;
    }

    private function getTemplateContent(array $template): array {
        $language_id = (int)$this->config->get('config_language_id');

        if (!empty($template['content'][$language_id])) {
            return $template['content'][$language_id];
        }

        return $template['content'] ?? [];
    }

    private function getOcfilterPage(int $page_id): array {
        if (!$page_id) {
            return [];
        }

        $query = $this->db->query("
            SELECT op.*, opd.*
            FROM `" . DB_PREFIX . "ocfilter_page` op
            LEFT JOIN `" . DB_PREFIX . "ocfilter_page_description` opd ON opd.page_id = op.page_id
            WHERE op.page_id = '" . $page_id . "'
              AND opd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            LIMIT 1
        ");

        return $query->num_rows ? $query->row : [];
    }

    private function getOcfilterPageLayoutId(int $page_id): int {
        if (!$page_id) {
            return 0;
        }

        $query = $this->db->query("
            SELECT layout_id
            FROM `" . DB_PREFIX . "ocfilter_page_to_layout`
            WHERE page_id = '" . $page_id . "'
              AND store_id = '" . (int)$this->config->get('config_store_id') . "'
            LIMIT 1
        ");

        return $query->num_rows ? (int)$query->row['layout_id'] : 0;
    }

    private function getValues(array $context): array {
        $data = $context['data'];
        $price = '';

        if ($context['type'] === 'product' && isset($data['price'])) {
            $raw_price = !empty($data['special']) ? (float)$data['special'] : (float)$data['price'];
            $price = (string)(int)round($raw_price);
        }

        $type = '';

        if ($context['type'] === 'product') {
            $type = trim((string)($data['meta_title'] ?? ''));
        }

        $site_name         = $this->config->get('config_name');
        $is_account_route  = strpos($context['route'] ?? '', 'account/') === 0;
        $is_logged         = $this->customer->isLogged();
        $customer_firstname = $is_logged ? $this->customer->getFirstname() : '';

        if ($is_account_route) {
            $page_title = $is_logged
                ? $customer_firstname . ' | ' . $site_name
                : $site_name;
        } else {
            $page_title = $context['name'];
        }

        return [
            '{name}'              => $context['name'],
            '{page_name}'         => $context['name'],
            '{category_name}'     => $context['type'] === 'product' ? ($data['category_name'] ?? '') : $context['name'],
            '{product_name}'      => $context['name'],
            '{manufacturer_name}' => $context['name'],
            '{post_name}'         => $context['name'],
            '{h1}'                => $context['name'],
            '{type}'              => $type,
            '{product_type}'      => $type,
            '{price}'             => $price,
            '{brand}'             => is_array($data['manufacturer'] ?? null) ? ($data['manufacturer']['name'] ?? '') : ($data['manufacturer'] ?? ''),
            '{model}'             => $data['model'] ?? '',
            '{sku}'               => $data['sku'] ?? '',
            '{parent_name}'       => $this->getParentCategoryName($context),
            '{root_name}'         => $this->getRootCategoryName($context),
            '{search_query}'      => $data['search_query'] ?? '',
            '{layout_id}'         => (string)($context['layout_id'] ?? ''),
            '{layout_name}'       => $context['layout_name'] ?? '',
            '{site_name}'         => $site_name,
            '{phone}'             => $this->config->get('config_telephone'),
            '{page_title}'        => $page_title,
            '{customer_firstname}' => $customer_firstname,
        ];
    }

    private function renderTemplate(string $template, array $values): string {
        $result = strtr($template, $values);
        $result = preg_replace('/\s+/u', ' ', $result);

        return trim(html_entity_decode($result, ENT_QUOTES, 'UTF-8'));
    }

    private function normalizeDocumentValue($value): string {
        if (is_array($value)) {
            $language_id = (int)$this->config->get('config_language_id');

            if (isset($value[$language_id])) {
                $value = $value[$language_id];
            } else {
                $value = reset($value) ?: '';
            }
        }

        return trim((string)$value);
    }

    private function getLocalizedConfig(string $key): string {
        $value = $this->config->get($key);
        $language_id = (int)$this->config->get('config_language_id');

        if (is_array($value)) {
            return (string)($value[$language_id] ?? reset($value) ?: '');
        }

        return (string)$value;
    }

    private function getParentCategoryName(array $context): string {
        if ($context['type'] !== 'category' || empty($context['data']['parent_id'])) {
            return '';
        }

        $this->load->model('catalog/category');
        $category = $this->model_catalog_category->getCategory((int)$context['data']['parent_id']);

        return $category['name'] ?? '';
    }

    private function getRootCategoryName(array $context): string {
        if ($context['type'] !== 'category' || empty($context['data']['path'][0])) {
            return '';
        }

        $this->load->model('catalog/category');
        $category = $this->model_catalog_category->getCategory((int)$context['data']['path'][0]);

        return $category['name'] ?? '';
    }

    private function getProductCategoryName(int $product_id): string {
        $query = $this->db->query("
            SELECT cd.name
            FROM `" . DB_PREFIX . "product_to_category` p2c
            LEFT JOIN `" . DB_PREFIX . "category_description` cd
                ON cd.category_id = p2c.category_id
                AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            LEFT JOIN `" . DB_PREFIX . "category` c
                ON c.category_id = p2c.category_id
            WHERE p2c.product_id = '" . (int)$product_id . "'
            ORDER BY c.parent_id DESC, p2c.category_id DESC
            LIMIT 1
        ");

        return $query->num_rows ? (string)$query->row['name'] : '';
    }
}
