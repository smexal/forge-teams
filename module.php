<?php
namespace Forge\Modules\Teams;

require_once("config.php");

use Forge\Core\Abstracts\Module;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\App\ModifyHandler;
use Forge\Core\Classes\Settings;
use Forge\Core\Classes\Utils;


class ForgeTeams extends Module
{
    public $permission = 'manage.teams';

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
        $this->install();
        ModifyHandler::instance()->add('modify_manage_navigation', [$this, 'modifyManageNavigation']);
    }

    private function install()
    {
        if (Settings::get($this->name . ".installed")) {
            return;
        }

        Auth::registerPermissions("manage.collection.teams");
        Auth::registerPermissions("manage.collection.organizations");

        App::instance()->db->rawQuery('CREATE TABLE IF NOT EXISTS `forge_teams_members` (' .
            '`id` int(11) NOT NULL,' .
            '`team_id` int(11) NOT NULL,' .
            '`user_id` int(11) NOT NULL,' .
            '`role` varchar(50) NOT NULL,' .
            '`join_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP' .
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        App::instance()->db->rawQuery('ALTER TABLE `forge_teams_members` ADD PRIMARY KEY (`id`), ADD KEY `team_id` (`team_id`), ADD KEY `user_id` (`user_id`);');
        App::instance()->db->rawQuery('ALTER TABLE `forge_teams_members` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

        App::instance()->db->rawQuery('CREATE TABLE IF NOT EXISTS `forge_organizations_teams` (' .
            '`id` int(11) NOT NULL,' .
            '`organization_id` int(11) NOT NULL,' .
            '`team_id` int(11) NOT NULL,' .
            '`join_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP' .
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        App::instance()->db->rawQuery('ALTER TABLE `forge_organizations_teams` ADD PRIMARY KEY (`id`), ADD KEY `organization_id` (`organization_id`), ADD KEY `team_id` (`team_id`);');
        App::instance()->db->rawQuery('ALTER TABLE `forge_organizations_teams` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

        Settings::set($this->name . ".installed", 1);
    }

    public function modifyManageNavigation($navigation)
    {
        if (Auth::allowed('manage.collection.teams')) {
            $navigation->removeFromCollections('forge-teams');
        }
        if (Auth::allowed('manage.collection.organizations')) {
            $navigation->removeFromCollections('forge-organizations');
        }
        if (Auth::allowed($this->permission)) {
            $navigation->add('esports_container', i('E-Sports'), false, 'leftPanel', 'videogame_asset');
            $navigation->add('organizations', i('Organizations'), Utils::getUrl(array('manage', 'collections', 'forge-organizations')), 'leftPanel', false, 'esports_container');
            $navigation->add('teams', i('Teams'), Utils::getUrl(array('manage', 'collections', 'forge-teams')), 'leftPanel', false, 'esports_container');
        }
        return $navigation;
    }

    /**
     * Removed a user from a team
     * @param $team_id
     * @param $user_id
     */
    public static function leaveTeam($team_id)
    {
        $db = App::instance()->db;
        $db->where('team_id', $team_id);
        $db->where('user_id', Auth::getSessionUserID());
        $db->delete('forge_teams_members');
    }

    /**
     * Deletes a team
     * @param $team_id
     */
    public function deleteTeam($team_id)
    {
        $db = App::instance()->db;
        $db->where('organization_id', $this->organizationId);
        $db->where('team_id', $team_id);
    }
}

?>
