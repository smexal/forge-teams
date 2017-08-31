<?php
namespace Forge\Modules\Teams;

require_once("config.php");

use Forge\Core\Abstracts\Module;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\Group;
use Forge\Core\Classes\Settings;
use Forge\Loader;


class Teams extends Module
{
    private $permission = 'manage.forge-teams';

    public function setup()
    {
        $this->version = '0.0.1';
        $this->id = "forge-teams";
        $this->name = i('Forge Teams', 'forge-teams');
        $this->description = i('Teams and organizations for the Forge CMS', 'forge-teams');
        $this->image = $this->url() . 'assets/images/module-image.svg';
    }

    public function start()
    {
        \Forge\SuperLoader::instance()->addIgnore('Spyc');

        $this->install();
        $this->load();
        $this->load_assets();
    }

    public function load()
    {
        //ActionHandler::instance();
    }

    public function load_assets()
    {
        // backend
        Loader::instance()->addStyle("modules/forge-teams/assets/css/forge-teams.less");
        Loader::instance()->addStyle("modules/forge-teams/assets/css/cardselect.less");
        Loader::instance()->addStyle("modules/forge-teams/assets/css/comparetree.less");

        Loader::instance()->addScript("modules/forge-teams/assets/scripts/comparetree.js");

        // frontend
        App::instance()->tm->theme->addScript($this->url() . "assets/scripts/forge-teams.js", true);
        App::instance()->tm->theme->addScript($this->url() . "assets/scripts/cardselect.js", true);
        App::instance()->tm->theme->addStyle(MOD_ROOT . "forge-teams/assets/css/forge-teams.less");
        App::instance()->tm->theme->addStyle(MOD_ROOT . "forge-teams/assets/css/cardselect.less");
    }

    public function install()
    {
        if (Settings::get($this->name . ".installed")) {
            return;
        }
        Auth::registerPermissions($this->permission);
        Auth::registerPermissions('api.collection.forge-teams.read');

        $admins = Group::getByName('Administratoren');
        $admins->grant(Auth::getPermissionID('api.collection.forge-teams.read'));

        Settings::set($this->name . ".installed", 1);
    }

}

?>
