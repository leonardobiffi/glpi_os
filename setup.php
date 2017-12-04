<?php
// define glpi_os version
define('PLUGIN_OS_VERSION', '0.2');

class PluginOsConfig extends CommonDBTM {
  static protected $notable = true;
  static function getMenuName() {
    return __('Ordem de Serviço');
  }
  
  static function getMenuContent() {
    global $CFG_GLPI;
    $menu = array();
    $menu['title']   = __('Ordem de Serviço','OS');
    $menu['page']    = "/plugins/os/front/os.php";
   	return $menu;
  }
}

function plugin_init_os() {
  global $PLUGIN_HOOKS, $LANG;
  $PLUGIN_HOOKS['csrf_compliant']['os'] = true;
  $PLUGIN_HOOKS["menu_toadd"]['os'] = array('plugins'  => 'PluginOsConfig');
  $PLUGIN_HOOKS['config_page']['os'] = 'front/os.php';
}


function plugin_version_os() {
  global $DB, $LANG;
  return [
    'name'      => 'OS Remix',
    'version' 	=> PLUGIN_OS_VERSION ,
    'author'		=> '<a href="mailto:leonardobiffi@outlook.com"> Leonardo Biffi </b> </a>',
    'license'		=> 'GPLv2+',
    'homepage'	=> '',
    'requirements'   => [
      'glpi'   => [
        'min' => '9.2'
      ],
      'php'    => [
        'min' => '7.0'
      ]
    ]
  ];
}

function plugin_os_check_config($verbose = false) {
if (true) { // Your configuration check
  return true;
}

if ($verbose) {
  echo "Installed, but not configured";
}
return false;
}