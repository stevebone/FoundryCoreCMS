-- 004_world_loot_indexes.sql
-- Item-column indexes on the world loot tables so /db/item pages can look
-- up "who drops X / what does X disenchant into" without full scans.
ALTER TABLE `creature_loot_template`  ADD INDEX `idx_item` (`Item`);
ALTER TABLE `item_loot_template`      ADD INDEX `idx_item` (`Item`);
ALTER TABLE `gameobject_loot_template` ADD INDEX `idx_item` (`Item`);
ALTER TABLE `disenchant_loot_template` ADD INDEX `idx_item` (`Item`);
