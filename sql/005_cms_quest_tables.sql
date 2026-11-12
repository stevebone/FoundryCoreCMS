-- db2 quest/map tables (AreaTable/QuestSort/QuestInfo/ContentTuning/Faction/FactionTemplate/QuestXP/UiMap/UiMapAssignment @12.1.7.70740)

DROP TABLE IF EXISTS `area_table`;
CREATE TABLE `area_table` (
  `ID` bigint NOT NULL,
  `ZoneName` text,
  `AreaName_lang` text,
  `ContinentID` bigint DEFAULT NULL,
  `ParentAreaID` bigint DEFAULT NULL,
  `AreaBit` bigint DEFAULT NULL,
  `SoundProviderPref` bigint DEFAULT NULL,
  `SoundProviderPrefUnderwater` bigint DEFAULT NULL,
  `AmbienceID` bigint DEFAULT NULL,
  `UwAmbience` bigint DEFAULT NULL,
  `ZoneMusic` bigint DEFAULT NULL,
  `UwZoneMusic` bigint DEFAULT NULL,
  `ExplorationLevel` bigint DEFAULT NULL,
  `IntroSound` bigint DEFAULT NULL,
  `UwIntroSound` bigint DEFAULT NULL,
  `FactionGroupMask` bigint DEFAULT NULL,
  `Ambient_multiplier` double DEFAULT NULL,
  `MountFlags` bigint DEFAULT NULL,
  `PvpCombatWorldStateID` bigint DEFAULT NULL,
  `WildBattlePetLevelMin` bigint DEFAULT NULL,
  `WildBattlePetLevelMax` bigint DEFAULT NULL,
  `WindSettingsID` bigint DEFAULT NULL,
  `ContentTuningID` bigint DEFAULT NULL,
  `Flags_0` bigint DEFAULT NULL,
  `Flags_1` bigint DEFAULT NULL,
  `LiquidTypeID_0` bigint DEFAULT NULL,
  `LiquidTypeID_1` bigint DEFAULT NULL,
  `LiquidTypeID_2` bigint DEFAULT NULL,
  `LiquidTypeID_3` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `quest_sort`;
CREATE TABLE `quest_sort` (
  `ID` bigint NOT NULL,
  `SortName_lang` text,
  `UiOrderIndex` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `quest_info`;
CREATE TABLE `quest_info` (
  `ID` bigint NOT NULL,
  `InfoName_lang` text,
  `Type` bigint DEFAULT NULL,
  `Modifiers` bigint DEFAULT NULL,
  `Profession` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `content_tuning`;
CREATE TABLE `content_tuning` (
  `ID` bigint NOT NULL,
  `Flags` bigint DEFAULT NULL,
  `ExpansionID` bigint DEFAULT NULL,
  `HPScalingCurveID` bigint DEFAULT NULL,
  `DMGScalingCurveID` bigint DEFAULT NULL,
  `HPPrimaryStatScalingCurveID` bigint DEFAULT NULL,
  `DMGPrimaryStatScalingCurveID` bigint DEFAULT NULL,
  `PrimaryStatScalingModPlayerDataElementCharacterID` bigint DEFAULT NULL,
  `PrimaryStatScalingModPlayerDataElementCharacterMultiplier` bigint DEFAULT NULL,
  `MinLevelSquish` bigint DEFAULT NULL,
  `MaxLevelSquish` bigint DEFAULT NULL,
  `MinLevelScalingOffset` bigint DEFAULT NULL,
  `MaxLevelScalingOffset` bigint DEFAULT NULL,
  `AllowedMinOffset` bigint DEFAULT NULL,
  `AllowedMaxOffset` bigint DEFAULT NULL,
  `LfgMinLevel` bigint DEFAULT NULL,
  `LfgMaxLevel` bigint DEFAULT NULL,
  `ILevel` bigint DEFAULT NULL,
  `XpMultQuest` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `faction`;
CREATE TABLE `faction` (
  `ID` bigint NOT NULL,
  `Name_lang` text,
  `Description_lang` text,
  `ReputationIndex` bigint DEFAULT NULL,
  `ParentFactionID` bigint DEFAULT NULL,
  `Expansion` bigint DEFAULT NULL,
  `FriendshipRepID` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  `ParagonFactionID` bigint DEFAULT NULL,
  `RenownFactionID` bigint DEFAULT NULL,
  `RenownCurrencyID` bigint DEFAULT NULL,
  `RenownThresholdCurveID` bigint DEFAULT NULL,
  `ReputationClassMask_0` bigint DEFAULT NULL,
  `ReputationClassMask_1` bigint DEFAULT NULL,
  `ReputationClassMask_2` bigint DEFAULT NULL,
  `ReputationClassMask_3` bigint DEFAULT NULL,
  `ReputationFlags_0` bigint DEFAULT NULL,
  `ReputationFlags_1` bigint DEFAULT NULL,
  `ReputationFlags_2` bigint DEFAULT NULL,
  `ReputationFlags_3` bigint DEFAULT NULL,
  `ReputationBase_0` bigint DEFAULT NULL,
  `ReputationBase_1` bigint DEFAULT NULL,
  `ReputationBase_2` bigint DEFAULT NULL,
  `ReputationBase_3` bigint DEFAULT NULL,
  `ReputationMax_0` bigint DEFAULT NULL,
  `ReputationMax_1` bigint DEFAULT NULL,
  `ReputationMax_2` bigint DEFAULT NULL,
  `ReputationMax_3` bigint DEFAULT NULL,
  `ParentFactionMod_0` double DEFAULT NULL,
  `ParentFactionMod_1` double DEFAULT NULL,
  `ParentFactionCap_0` bigint DEFAULT NULL,
  `ParentFactionCap_1` bigint DEFAULT NULL,
  `ReputationRaceMasks0_0` bigint DEFAULT NULL,
  `ReputationRaceMasks0_1` bigint DEFAULT NULL,
  `ReputationRaceMasks1_0` bigint DEFAULT NULL,
  `ReputationRaceMasks1_1` bigint DEFAULT NULL,
  `ReputationRaceMasks2_0` bigint DEFAULT NULL,
  `ReputationRaceMasks2_1` bigint DEFAULT NULL,
  `ReputationRaceMasks3_0` bigint DEFAULT NULL,
  `ReputationRaceMasks3_1` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `faction_template`;
CREATE TABLE `faction_template` (
  `ID` bigint NOT NULL,
  `Faction` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  `FactionGroup` bigint DEFAULT NULL,
  `FriendGroup` bigint DEFAULT NULL,
  `EnemyGroup` bigint DEFAULT NULL,
  `Enemies_0` bigint DEFAULT NULL,
  `Enemies_1` bigint DEFAULT NULL,
  `Enemies_2` bigint DEFAULT NULL,
  `Enemies_3` bigint DEFAULT NULL,
  `Enemies_4` bigint DEFAULT NULL,
  `Enemies_5` bigint DEFAULT NULL,
  `Enemies_6` bigint DEFAULT NULL,
  `Enemies_7` bigint DEFAULT NULL,
  `Friend_0` bigint DEFAULT NULL,
  `Friend_1` bigint DEFAULT NULL,
  `Friend_2` bigint DEFAULT NULL,
  `Friend_3` bigint DEFAULT NULL,
  `Friend_4` bigint DEFAULT NULL,
  `Friend_5` bigint DEFAULT NULL,
  `Friend_6` bigint DEFAULT NULL,
  `Friend_7` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `quest_xp`;
CREATE TABLE `quest_xp` (
  `ID` bigint NOT NULL,
  `Difficulty_0` bigint DEFAULT NULL,
  `Difficulty_1` bigint DEFAULT NULL,
  `Difficulty_2` bigint DEFAULT NULL,
  `Difficulty_3` bigint DEFAULT NULL,
  `Difficulty_4` bigint DEFAULT NULL,
  `Difficulty_5` bigint DEFAULT NULL,
  `Difficulty_6` bigint DEFAULT NULL,
  `Difficulty_7` bigint DEFAULT NULL,
  `Difficulty_8` bigint DEFAULT NULL,
  `Difficulty_9` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `ui_map`;
CREATE TABLE `ui_map` (
  `Name_lang` text,
  `ID` bigint NOT NULL,
  `ParentUiMapID` bigint DEFAULT NULL,
  `Flags` bigint DEFAULT NULL,
  `System` bigint DEFAULT NULL,
  `Type` bigint DEFAULT NULL,
  `BountySetID` bigint DEFAULT NULL,
  `BountyDisplayLocation` bigint DEFAULT NULL,
  `VisibilityPlayerConditionID` bigint DEFAULT NULL,
  `HelpTextPosition` bigint DEFAULT NULL,
  `BkgAtlasID` bigint DEFAULT NULL,
  `AlternateUiMapGroup` bigint DEFAULT NULL,
  `ContentTuningID` bigint DEFAULT NULL,
  `AdventureMapTextureKitID` bigint DEFAULT NULL,
  `MapArtZoneTextPosition` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `ui_map_assignment`;
CREATE TABLE `ui_map_assignment` (
  `UiMin_0` bigint DEFAULT NULL,
  `UiMin_1` bigint DEFAULT NULL,
  `UiMax_0` bigint DEFAULT NULL,
  `UiMax_1` bigint DEFAULT NULL,
  `Region_0` double DEFAULT NULL,
  `Region_1` double DEFAULT NULL,
  `Region_2` bigint DEFAULT NULL,
  `Region_3` double DEFAULT NULL,
  `Region_4` double DEFAULT NULL,
  `Region_5` bigint DEFAULT NULL,
  `ID` bigint NOT NULL,
  `UiMapID` bigint DEFAULT NULL,
  `OrderIndex` bigint DEFAULT NULL,
  `MapID` bigint DEFAULT NULL,
  `AreaID` bigint DEFAULT NULL,
  `WMODoodadPlacementID` bigint DEFAULT NULL,
  `WMOGroupID` bigint DEFAULT NULL,
  `Field_11_2_5_62687_010` bigint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

