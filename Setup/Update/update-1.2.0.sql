-- create self-delivery table
CREATE TABLE `ost_shipping_costs_selfdelivery` (
  `id` int(11) NOT NULL,
  `zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `ost_shipping_costs_selfdelivery`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ost_shipping_costs_selfdelivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- test data
INSERT INTO `ost_shipping_costs_selfdelivery` (`id`, `zip`) VALUES
(1, '58452'),
(2, '58453'),
(3, '58454'),
(4, '58455'),
(5, '58456');