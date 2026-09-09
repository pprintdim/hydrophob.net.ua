-- hydrophob.net.ua — уніфікація СЕО зі спільним стандартом Hydrophob (2026-09-09).
-- Ідемпотентний: можна виконувати повторно.

-- 1. Гейт індексації. Ключа не було в oc_setting взагалі, тому перемикач
--    у Дизайн → СЕО-мета (він робить UPDATE) нічого не зберігав, а catalog
--    його не читав. Тепер ключ є, читається в common/header і редагується
--    з двох місць: Дизайн → СЕО-мета і Налаштування → Сервер.
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`)
SELECT 0, 'config', 'config_noindex', '0', 0
WHERE NOT EXISTS (
  SELECT 1 FROM (SELECT * FROM `oc_setting`) s
  WHERE s.`store_id` = 0 AND s.`key` = 'config_noindex'
);

-- 2. Таблиці ручних правил robots/canonical для модуля seo_meta.
--    (у цій БД вони вже є — залишено для повторюваності на інших магазинах)
CREATE TABLE IF NOT EXISTS `oc_seo_meta_robots` (
  `seo_meta_robots_id` int NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(64) NOT NULL DEFAULT '',
  `entity_id` int NOT NULL DEFAULT '0',
  `entity_key` varchar(255) NOT NULL DEFAULT '',
  `language_id` int NOT NULL DEFAULT '0',
  `robots` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`seo_meta_robots_id`),
  KEY `entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_seo_meta_canonical` (
  `seo_meta_canonical_id` int NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(64) NOT NULL DEFAULT '',
  `entity_id` int NOT NULL DEFAULT '0',
  `entity_key` varchar(255) NOT NULL DEFAULT '',
  `language_id` int NOT NULL DEFAULT '0',
  `canonical_url` varchar(500) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`seo_meta_canonical_id`),
  KEY `entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Право на сторінку шаблонів СЕО-мета у групі адміністраторів (вже є — про запас).
UPDATE `oc_user_group`
SET `permission` = JSON_SET(`permission`, '$.access', JSON_ARRAY_APPEND(JSON_EXTRACT(`permission`, '$.access'), '$', 'design/seo_meta'))
WHERE `user_group_id` = 1 AND JSON_CONTAINS(JSON_EXTRACT(`permission`, '$.access'), '"design/seo_meta"') = 0;

UPDATE `oc_user_group`
SET `permission` = JSON_SET(`permission`, '$.modify', JSON_ARRAY_APPEND(JSON_EXTRACT(`permission`, '$.modify'), '$', 'design/seo_meta'))
WHERE `user_group_id` = 1 AND JSON_CONTAINS(JSON_EXTRACT(`permission`, '$.modify'), '"design/seo_meta"') = 0;
