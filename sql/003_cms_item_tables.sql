-- 003_cms_item_tables.sql
-- db2 CSV-import tables for the armory + db browser.
-- Recreated+populated by: php tools/import_db2_csv.php <csv-dir>

DROP TABLE IF EXISTS `char_titles`;
CREATE TABLE `char_titles` (
  `ID` bigint NOT NULL,
  `Name_lang` text,
  `Name1_lang` text,
  `Mask_ID` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `chr_classes`;
CREATE TABLE `chr_classes` (
  `Name_lang` text,
  `Filename` text,
  `Name_male_lang` text,
  `Name_female_lang` text,
  `PetNameToken` text,
  `Description_lang` text,
  `RoleInfoString_lang` text,
  `DisabledString_lang` text,
  `Hyphenated_name_male_lang` text,
  `Hyphenated_name_female_lang` text,
  `CreateScreenFileDataID` bigint DEFAULT NULL,
  `SelectScreenFileDataID` bigint DEFAULT NULL,
  `IconFileDataID` bigint DEFAULT NULL,
  `LowResScreenFileDataID` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  `StartingLevel` bigint DEFAULT NULL,
  `SpellTextureBlobFileDataID` bigint DEFAULT NULL,
  `ArmorTypeMask` bigint DEFAULT NULL,
  `Field_9_0_1_34490_018` bigint DEFAULT NULL,
  `MaleCharacterCreationVisualFallback` bigint DEFAULT NULL,
  `MaleCharacterCreationIdleVisualFallback` bigint DEFAULT NULL,
  `FemaleCharacterCreationVisualFallback` bigint DEFAULT NULL,
  `FemaleCharacterCreationIdleVisualFallback` bigint DEFAULT NULL,
  `CharacterCreationIdleGroundVisualFallback` bigint DEFAULT NULL,
  `CharacterCreationGroundVisualFallback` bigint DEFAULT NULL,
  `AlteredFormCharacterCreationIdleVisualFallback` bigint DEFAULT NULL,
  `CharacterCreationAnimLoopWaitTimeMsFallback` bigint DEFAULT NULL,
  `CinematicSequenceID` bigint DEFAULT NULL,
  `DefaultSpec` bigint DEFAULT NULL,
  `ID` bigint NOT NULL,
  `HasStrengthAttackBonus` bigint DEFAULT NULL,
  `PrimaryStatPriority` bigint DEFAULT NULL,
  `DisplayPower` bigint DEFAULT NULL,
  `RangedAttackPowerPerAgility` bigint DEFAULT NULL,
  `AttackPowerPerAgility` bigint DEFAULT NULL,
  `AttackPowerPerStrength` bigint DEFAULT NULL,
  `SpellClassSet` bigint DEFAULT NULL,
  `ClassColorR` bigint DEFAULT NULL,
  `ClassColorG` bigint DEFAULT NULL,
  `ClassColorB` bigint DEFAULT NULL,
  `RolesMask` bigint DEFAULT NULL,
  `DamageBonusStat` bigint DEFAULT NULL,
  `HasRelicSlot` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `chr_races`;
CREATE TABLE `chr_races` (
  `ID` bigint NOT NULL,
  `ClientPrefix` text,
  `ClientFileString` text,
  `Name_lang` text,
  `Name_female_lang` text,
  `Name_lowercase_lang` text,
  `Name_female_lowercase_lang` text,
  `Lore_name_lang` text,
  `Lore_name_female_lang` text,
  `Lore_name_lower_lang` text,
  `Lore_name_lower_female_lang` text,
  `LoreDescription_lang` text,
  `Short_name_lang` text,
  `Short_name_female_lang` text,
  `Short_name_lower_lang` text,
  `Short_name_lower_female_lang` text,
  `Flags` bigint DEFAULT NULL,
  `FactionID` bigint DEFAULT NULL,
  `CinematicSequenceID` bigint DEFAULT NULL,
  `ResSicknessSpellID` bigint DEFAULT NULL,
  `SplashSoundID` bigint DEFAULT NULL,
  `CreateScreenFileDataID` bigint DEFAULT NULL,
  `SelectScreenFileDataID` bigint DEFAULT NULL,
  `LowResScreenFileDataID` bigint DEFAULT NULL,
  `AlteredFormStartVisualKitID_0` bigint DEFAULT NULL,
  `AlteredFormStartVisualKitID_1` bigint DEFAULT NULL,
  `AlteredFormStartVisualKitID_2` bigint DEFAULT NULL,
  `AlteredFormFinishVisualKitID_0` bigint DEFAULT NULL,
  `AlteredFormFinishVisualKitID_1` bigint DEFAULT NULL,
  `AlteredFormFinishVisualKitID_2` bigint DEFAULT NULL,
  `HeritageArmorAchievementID` bigint DEFAULT NULL,
  `StartingLevel` bigint DEFAULT NULL,
  `UiDisplayOrder` bigint DEFAULT NULL,
  `PlayableRaceBit` bigint DEFAULT NULL,
  `TransmogrifyDisabledSlotMask` bigint DEFAULT NULL,
  `AlteredFormCustomizeOffsetFallback_0` double DEFAULT NULL,
  `AlteredFormCustomizeOffsetFallback_1` double DEFAULT NULL,
  `AlteredFormCustomizeOffsetFallback_2` double DEFAULT NULL,
  `AlteredFormCustomizeRotationFallback` double DEFAULT NULL,
  `Field_9_1_0_38312_030_0` bigint DEFAULT NULL,
  `Field_9_1_0_38312_030_1` double DEFAULT NULL,
  `Field_9_1_0_38312_030_2` bigint DEFAULT NULL,
  `Field_9_1_0_38312_031_0` bigint DEFAULT NULL,
  `Field_9_1_0_38312_031_1` double DEFAULT NULL,
  `Field_9_1_0_38312_031_2` bigint DEFAULT NULL,
  `BaseLanguage` bigint DEFAULT NULL,
  `CreatureType` bigint DEFAULT NULL,
  `Alliance` bigint DEFAULT NULL,
  `Race_related` bigint DEFAULT NULL,
  `UnalteredVisualRaceID` bigint DEFAULT NULL,
  `DefaultClassID` bigint DEFAULT NULL,
  `NeutralRaceID` bigint DEFAULT NULL,
  `MaleModelFallbackRaceID` bigint DEFAULT NULL,
  `MaleModelFallbackSex` bigint DEFAULT NULL,
  `FemaleModelFallbackRaceID` bigint DEFAULT NULL,
  `FemaleModelFallbackSex` bigint DEFAULT NULL,
  `MaleTextureFallbackRaceID` bigint DEFAULT NULL,
  `MaleTextureFallbackSex` bigint DEFAULT NULL,
  `FemaleTextureFallbackRaceID` bigint DEFAULT NULL,
  `FemaleTextureFallbackSex` bigint DEFAULT NULL,
  `HelmetAnimScalingRaceID` bigint DEFAULT NULL,
  `UnalteredVisualCustomizationRaceID` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `gem_properties`;
CREATE TABLE `gem_properties` (
  `ID` bigint NOT NULL,
  `Enchant_ID` bigint DEFAULT NULL,
  `Type` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `ID` bigint NOT NULL,
  `ClassID` bigint DEFAULT NULL,
  `SubclassID` bigint DEFAULT NULL,
  `Material` bigint DEFAULT NULL,
  `InventoryType` bigint DEFAULT NULL,
  `SheatheType` bigint DEFAULT NULL,
  `ItemPetFoodID` bigint DEFAULT NULL,
  `Sound_override_subclassID` bigint DEFAULT NULL,
  `IconFileDataID` bigint DEFAULT NULL,
  `ItemGroupSoundsID` bigint DEFAULT NULL,
  `ContentTuningID` bigint DEFAULT NULL,
  `ModifiedCraftingReagentItemID` bigint DEFAULT NULL,
  `AmmunitionType` bigint DEFAULT NULL,
  `CraftingQualityID` bigint DEFAULT NULL,
  `ItemSquishEraID` bigint DEFAULT NULL,
  `RecraftReagentCountPercentage` bigint DEFAULT NULL,
  `OrderSource` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_appearance`;
CREATE TABLE `item_appearance` (
  `ID` bigint NOT NULL,
  `DisplayType` bigint DEFAULT NULL,
  `ItemDisplayInfoID` bigint DEFAULT NULL,
  `DefaultIconFileDataID` bigint DEFAULT NULL,
  `UiOrder` bigint DEFAULT NULL,
  `TransmogPlayerConditionID` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_armor_quality`;
CREATE TABLE `item_armor_quality` (
  `ID` bigint NOT NULL,
  `Qualitymod_0` double DEFAULT NULL,
  `Qualitymod_1` double DEFAULT NULL,
  `Qualitymod_2` bigint DEFAULT NULL,
  `Qualitymod_3` bigint DEFAULT NULL,
  `Qualitymod_4` bigint DEFAULT NULL,
  `Qualitymod_5` bigint DEFAULT NULL,
  `Qualitymod_6` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_armor_shield`;
CREATE TABLE `item_armor_shield` (
  `ID` bigint NOT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_armor_total`;
CREATE TABLE `item_armor_total` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Cloth` double DEFAULT NULL,
  `Leather` double DEFAULT NULL,
  `Mail` double DEFAULT NULL,
  `Plate` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_class`;
CREATE TABLE `item_class` (
  `ID` bigint NOT NULL,
  `ClassName_lang` text,
  `ClassID` bigint DEFAULT NULL,
  `PriceModifier` double DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_ic_class` (`ClassID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_one_hand`;
CREATE TABLE `item_damage_one_hand` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_one_hand_caster`;
CREATE TABLE `item_damage_one_hand_caster` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_ranged`;
CREATE TABLE `item_damage_ranged` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_thrown`;
CREATE TABLE `item_damage_thrown` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_two_hand`;
CREATE TABLE `item_damage_two_hand` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_two_hand_caster`;
CREATE TABLE `item_damage_two_hand_caster` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_damage_wand`;
CREATE TABLE `item_damage_wand` (
  `ID` bigint NOT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Quality_0` double DEFAULT NULL,
  `Quality_1` double DEFAULT NULL,
  `Quality_2` double DEFAULT NULL,
  `Quality_3` double DEFAULT NULL,
  `Quality_4` double DEFAULT NULL,
  `Quality_5` double DEFAULT NULL,
  `Quality_6` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_effect`;
CREATE TABLE `item_effect` (
  `ID` bigint NOT NULL,
  `LegacySlotIndex` bigint DEFAULT NULL,
  `TriggerType` bigint DEFAULT NULL,
  `Charges` bigint DEFAULT NULL,
  `CoolDownMSec` bigint DEFAULT NULL,
  `CategoryCoolDownMSec` bigint DEFAULT NULL,
  `SpellCategoryID` bigint DEFAULT NULL,
  `SpellID` bigint DEFAULT NULL,
  `ChrSpecializationID` bigint DEFAULT NULL,
  `PlayerConditionID` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_modified_appearance`;
CREATE TABLE `item_modified_appearance` (
  `ID` bigint NOT NULL,
  `ItemID` bigint DEFAULT NULL,
  `ItemAppearanceModifierID` bigint DEFAULT NULL,
  `ItemAppearanceID` bigint DEFAULT NULL,
  `OrderIndex` bigint DEFAULT NULL,
  `TransmogSourceTypeEnum` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_ima_item` (`ItemID`,`ItemAppearanceModifierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_name_description`;
CREATE TABLE `item_name_description` (
  `ID` bigint NOT NULL,
  `Description_lang` text,
  `Color` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_search_name`;
CREATE TABLE `item_search_name` (
  `ID` bigint NOT NULL,
  `Display_lang` text,
  `OverallQualityID` bigint DEFAULT NULL,
  `ExpansionID` bigint DEFAULT NULL,
  `MinFactionID` bigint DEFAULT NULL,
  `MinReputation` bigint DEFAULT NULL,
  `AllowableClass` bigint DEFAULT NULL,
  `RequiredLevel` bigint DEFAULT NULL,
  `RequiredSkill` bigint DEFAULT NULL,
  `RequiredSkillRank` bigint DEFAULT NULL,
  `RequiredAbility` bigint DEFAULT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `Flags_0` bigint DEFAULT NULL,
  `Flags_1` bigint DEFAULT NULL,
  `Flags_2` bigint DEFAULT NULL,
  `Flags_3` bigint DEFAULT NULL,
  `Flags_4` bigint DEFAULT NULL,
  `AllowableRaces_0` bigint DEFAULT NULL,
  `AllowableRaces_1` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_set`;
CREATE TABLE `item_set` (
  `ID` bigint NOT NULL,
  `Name_lang` text,
  `SetFlags` bigint DEFAULT NULL,
  `RequiredSkill` bigint DEFAULT NULL,
  `RequiredSkillRank` bigint DEFAULT NULL,
  `ItemID_0` bigint DEFAULT NULL,
  `ItemID_1` bigint DEFAULT NULL,
  `ItemID_2` bigint DEFAULT NULL,
  `ItemID_3` bigint DEFAULT NULL,
  `ItemID_4` bigint DEFAULT NULL,
  `ItemID_5` bigint DEFAULT NULL,
  `ItemID_6` bigint DEFAULT NULL,
  `ItemID_7` bigint DEFAULT NULL,
  `ItemID_8` bigint DEFAULT NULL,
  `ItemID_9` bigint DEFAULT NULL,
  `ItemID_10` bigint DEFAULT NULL,
  `ItemID_11` bigint DEFAULT NULL,
  `ItemID_12` bigint DEFAULT NULL,
  `ItemID_13` bigint DEFAULT NULL,
  `ItemID_14` bigint DEFAULT NULL,
  `ItemID_15` bigint DEFAULT NULL,
  `ItemID_16` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_set_spell`;
CREATE TABLE `item_set_spell` (
  `ID` bigint NOT NULL,
  `ChrSpecID` bigint DEFAULT NULL,
  `SpellID` bigint DEFAULT NULL,
  `TraitSubTreeID` bigint DEFAULT NULL,
  `Threshold` bigint DEFAULT NULL,
  `ItemSetID` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_sparse`;
CREATE TABLE `item_sparse` (
  `ID` bigint NOT NULL,
  `Description_lang` text,
  `Display3_lang` text,
  `Display2_lang` text,
  `Display1_lang` text,
  `Display_lang` text,
  `ExpansionID` bigint DEFAULT NULL,
  `DmgVariance` double DEFAULT NULL,
  `LimitCategory` bigint DEFAULT NULL,
  `DurationInInventory` bigint DEFAULT NULL,
  `QualityModifier` bigint DEFAULT NULL,
  `BagFamily` bigint DEFAULT NULL,
  `StartQuestID` bigint DEFAULT NULL,
  `LanguageID` bigint DEFAULT NULL,
  `ItemRange` bigint DEFAULT NULL,
  `StatPercentageOfSocket_0` bigint DEFAULT NULL,
  `StatPercentageOfSocket_1` bigint DEFAULT NULL,
  `StatPercentageOfSocket_2` bigint DEFAULT NULL,
  `StatPercentageOfSocket_3` bigint DEFAULT NULL,
  `StatPercentageOfSocket_4` bigint DEFAULT NULL,
  `StatPercentageOfSocket_5` bigint DEFAULT NULL,
  `StatPercentageOfSocket_6` bigint DEFAULT NULL,
  `StatPercentageOfSocket_7` bigint DEFAULT NULL,
  `StatPercentageOfSocket_8` bigint DEFAULT NULL,
  `StatPercentageOfSocket_9` bigint DEFAULT NULL,
  `StatPercentEditor_0` bigint DEFAULT NULL,
  `StatPercentEditor_1` bigint DEFAULT NULL,
  `StatPercentEditor_2` bigint DEFAULT NULL,
  `StatPercentEditor_3` bigint DEFAULT NULL,
  `StatPercentEditor_4` bigint DEFAULT NULL,
  `StatPercentEditor_5` bigint DEFAULT NULL,
  `StatPercentEditor_6` bigint DEFAULT NULL,
  `StatPercentEditor_7` bigint DEFAULT NULL,
  `StatPercentEditor_8` bigint DEFAULT NULL,
  `StatPercentEditor_9` bigint DEFAULT NULL,
  `StatModifier_bonusStat_0` bigint DEFAULT NULL,
  `StatModifier_bonusStat_1` bigint DEFAULT NULL,
  `StatModifier_bonusStat_2` bigint DEFAULT NULL,
  `StatModifier_bonusStat_3` bigint DEFAULT NULL,
  `StatModifier_bonusStat_4` bigint DEFAULT NULL,
  `StatModifier_bonusStat_5` bigint DEFAULT NULL,
  `StatModifier_bonusStat_6` bigint DEFAULT NULL,
  `StatModifier_bonusStat_7` bigint DEFAULT NULL,
  `StatModifier_bonusStat_8` bigint DEFAULT NULL,
  `StatModifier_bonusStat_9` bigint DEFAULT NULL,
  `Stackable` bigint DEFAULT NULL,
  `MaxCount` bigint DEFAULT NULL,
  `MinReputation` bigint DEFAULT NULL,
  `RequiredAbility` bigint DEFAULT NULL,
  `AllowableRace_0` bigint DEFAULT NULL,
  `AllowableRace_1` bigint DEFAULT NULL,
  `SellPrice` bigint DEFAULT NULL,
  `BuyPrice` bigint DEFAULT NULL,
  `VendorStackCount` bigint DEFAULT NULL,
  `PriceVariance` bigint DEFAULT NULL,
  `PriceRandomValue` double DEFAULT NULL,
  `Flags_0` bigint DEFAULT NULL,
  `Flags_1` bigint DEFAULT NULL,
  `Flags_2` bigint DEFAULT NULL,
  `Flags_3` bigint DEFAULT NULL,
  `Flags_4` bigint DEFAULT NULL,
  `OppositeFactionItemID` bigint DEFAULT NULL,
  `ModifiedCraftingReagentItemID` bigint DEFAULT NULL,
  `ContentTuningID` bigint DEFAULT NULL,
  `PlayerLevelToItemLevelCurveID` bigint DEFAULT NULL,
  `ItemLevelOffsetCurveID` bigint DEFAULT NULL,
  `ItemLevelOffsetItemLevel` bigint DEFAULT NULL,
  `ItemSquishEraID` bigint DEFAULT NULL,
  `ItemNameDescriptionID` bigint DEFAULT NULL,
  `RequiredTransmogHoliday` bigint DEFAULT NULL,
  `RequiredHoliday` bigint DEFAULT NULL,
  `Gem_properties` bigint DEFAULT NULL,
  `Socket_match_enchantment_ID` bigint DEFAULT NULL,
  `TotemCategoryID` bigint DEFAULT NULL,
  `InstanceBound` bigint DEFAULT NULL,
  `ZoneBound_0` bigint DEFAULT NULL,
  `ZoneBound_1` bigint DEFAULT NULL,
  `ItemSet` bigint DEFAULT NULL,
  `LockID` bigint DEFAULT NULL,
  `PageID` bigint DEFAULT NULL,
  `ItemDelay` bigint DEFAULT NULL,
  `MinFactionID` bigint DEFAULT NULL,
  `RequiredSkillRank` bigint DEFAULT NULL,
  `RequiredSkill` bigint DEFAULT NULL,
  `ItemLevel` bigint DEFAULT NULL,
  `AllowableClass` bigint DEFAULT NULL,
  `ArtifactID` bigint DEFAULT NULL,
  `SpellWeight` bigint DEFAULT NULL,
  `SpellWeightCategory` bigint DEFAULT NULL,
  `SocketType_0` bigint DEFAULT NULL,
  `SocketType_1` bigint DEFAULT NULL,
  `SocketType_2` bigint DEFAULT NULL,
  `SheatheType` bigint DEFAULT NULL,
  `Material` bigint DEFAULT NULL,
  `PageMaterialID` bigint DEFAULT NULL,
  `Bonding` bigint DEFAULT NULL,
  `DamageType` bigint DEFAULT NULL,
  `ContainerSlots` bigint DEFAULT NULL,
  `RequiredPVPMedal` bigint DEFAULT NULL,
  `RequiredPVPRank` bigint DEFAULT NULL,
  `RequiredLevel` bigint DEFAULT NULL,
  `InventoryType` bigint DEFAULT NULL,
  `OverallQualityID` bigint DEFAULT NULL,
  `AmmunitionType` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_sub_class`;
CREATE TABLE `item_sub_class` (
  `DisplayName_lang` text,
  `VerboseName_lang` text,
  `ID` bigint NOT NULL,
  `ClassID` bigint DEFAULT NULL,
  `SubClassID` bigint DEFAULT NULL,
  `AuctionHouseSortOrder` bigint DEFAULT NULL,
  `PrerequisiteProficiency` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  `DisplayFlags` bigint DEFAULT NULL,
  `WeaponSwingSize` bigint DEFAULT NULL,
  `PostrequisiteProficiency` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_isc_lookup` (`ClassID`,`SubClassID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `item_x_item_effect`;
CREATE TABLE `item_x_item_effect` (
  `ID` bigint NOT NULL,
  `ItemEffectID` bigint DEFAULT NULL,
  `ItemID` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `spell`;
CREATE TABLE `spell` (
  `ID` bigint NOT NULL,
  `NameSubtext_lang` text,
  `Description_lang` text,
  `AuraDescription_lang` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `spell_name`;
CREATE TABLE `spell_name` (
  `ID` bigint NOT NULL,
  `Name_lang` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

