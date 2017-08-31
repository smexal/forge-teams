<?php

define("FORGE_TEAMS_DIR", MOD_ROOT . basename(dirname(__FILE__)) . '/');
define("FORGE_TEAMS_NS", 'Forge\\Modules\\News');
define("FORGE_TEAMS_CONFIG_FILES", serialize([
    'Defaults' => FORGE_TEAMS_DIR . '/config/defaults.yaml',
    'Module' => FORGE_TEAMS_DIR . '/config/module.yaml',
    'Actions' => FORGE_TEAMS_DIR . '/config/actions.yaml',
    'Comparisons' => FORGE_TEAMS_DIR . '/config/comparisons.yaml'
]));