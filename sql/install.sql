CREATE TABLE IF NOT EXISTS `PREFIX_dibs_payment` (
  `id_dibs_payment` INT(11) UNSIGNED AUTO_INCREMENT,
  `id_cart` INT(11) UNSIGNED NOT NULL,
  `id_order` INT(11) UNSIGNED NOT NULL,
  `id_payment` VARCHAR(255) NOT NULL,
  `id_charge` VARCHAR(255) NOT NULL DEFAULT '',
  `is_canceled` TINYINT(1) NOT NULL DEFAULT 0,
  `is_charged` TINYINT(1) NOT NULL DEFAULT 0,
  `is_refunded` TINYINT(1) NOT NULL DEFAULT 0,
  `is_partially_refunded` TINYINT(1) NOT NULL DEFAULT 0,
  `is_reserved` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_dibs_payment`),
  UNIQUE KEY (`id_cart`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;