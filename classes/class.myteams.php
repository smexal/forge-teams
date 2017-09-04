<?php

namespace Forge\Modules\Teams;

use Forge\Core\App\App;
use Forge\Core\Classes\CollectionItem;
use Forge\Core\Classes\TableBar;
use Forge\Core\Classes\Utils;

class MyTeams
{

    private $organizationId = false;
    private $searchTerm = false;
    public $isAdmin = false;

    public function __construct()
    {
    }

    public function setOrganization($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * Draws the table for frontend
     * @return string
     */
    public function renderTable()
    {
        $bar = new TableBar(Utils::url(['api', 'forge-teams', 'teams', $this->organizationId]), 'teamsTable');

        return $bar->render() . App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "table", array(
                'id' => 'teamsTable',
                'th' => $this->getThs(),
                'td' => $this->getTeamsByUser()
            ));
    }

    public function handleQuery($action)
    {
        switch ($action) {
            case 'search':
                $this->searchTerm = $_GET['t'];
                return json_encode([
                    'newTable' => App::instance()->render(
                        CORE_TEMPLATE_DIR . 'assets/',
                        'table-rows',
                        ['td' => $this->getTeams()]
                    )
                ]);
            default:
                break;
        }
    }

    public function getThs()
    {
        $ths = [];
        $ths[] = Utils::tableCell(i('Teamname', 'forge-teams'));
        $ths[] = Utils::tableCell(i('Organization', 'forge-teams'));
        $ths[] = Utils::tableCell(i('Your role', 'forge-teams'));
        $ths[] = Utils::tableCell(i('Created on', 'forge-teams'));
        $ths[] = Utils::tableCell(i('Actions', 'forge-teams'));
        return $ths;
    }

    public function getTeamsByUser()
    {
        $db = App::instance()->db;
        $db->join('forge_teams_members tm', 't.id = tm.team_id', 'LEFT');
        $db->join('forge_organizations_teams ot', 't.id = ot.team_id', 'LEFT');
        $db->where('tm.user_id', 54);
        $db->where('t.type', 'forge-teams');

        $teams = $db->get('collections t');
        $tds = [];
        foreach ($teams as $item) {
            $organization = new CollectionItem($item['organization_id']);
            $tds[] = $this->getTeamByUserTd($organization, $item);
        }
        return $tds;
    }

    private function getTeamByUserTd($organization, $team)
    {
        $td = [];
        $td[] = Utils::tableCell($team['name']);
        $td[] = Utils::tableCell($organization->getName(), false, false, false, Utils::url(["manage", "collections", 'edit', $team['id']])); // TODO
        $td[] = Utils::tableCell($team['role']);
        $td[] = Utils::tableCell($team['created']);
        $td[] = Utils::tableCell($this->actions($team['id']));
        return $td;
    }


    private function actions($id)
    {
        return App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(Utils::getUriComponents(), true, ['leaveTeam' => $id]),
                    "icon" => "leave",
                    "name" => i('Leave team', 'forge-teams'),
                    "ajax" => true,
                    "confirm" => false
                ),
                array(
                    "url" => Utils::getUrl(Utils::getUriComponents(), true, ['deleteTeam' => $id]),
                    "icon" => "delete",
                    "name" => i('Delete team', 'forge-teams'),
                    "ajax" => true,
                    "confirm" => false
                )
            )
        ));
    }

    public function delete($team_id)
    {
        $db = App::instance()->db;
        $db->where('organization_id', $this->organizationId);
        $db->where('team_id', $team_id);
    }

}