<?php

namespace Sylphian\Verify\Admin\Controller;

use Sylphian\Verify\Entity\GameServer;
use Sylphian\Verify\Repository\GameServerRepository;
use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;

class ServerManagement extends AbstractController
{
	/*
	protected function preDispatchController($action, ParameterBag $params): void
	{
		$this->assertAdminPermission('sylphianVerifyServer');
	}
	*/

	public function actionIndex(): View
	{
		$serverRepo = $this->repository(GameServerRepository::class);
		$servers = $serverRepo->findServersForList()->fetch();

		$viewParams = [
			'servers' => $servers,
		];

		return $this->view('Sylphian\Verify:ServerManagement\List', 'sylphian_verify_server_list', $viewParams);
	}

	public function serverAddEdit(GameServer $server): View
	{
		$viewParams = [
			'server' => $server,
		];

		return $this->view('Sylphian\Verify:ServerManagement\Edit', 'sylphian_verify_server_edit', $viewParams);
	}

	public function actionAdd(): View
	{
		$server = $this->em()->create('Sylphian\Verify:GameServer');
		return $this->serverAddEdit($server);
	}

	public function actionEdit(ParameterBag $params): View
	{
		$server = $this->assertServerExists($params->server_id);
		return $this->serverAddEdit($server);
	}

	public function actionSave(ParameterBag $params): Redirect
	{
		if ($params->server_id)
		{
			$server = $this->assertServerExists($params->server_id);
		}
		else
		{
			$server = $this->em()->create('Sylphian\Verify:GameServer');
		}

		$this->serverSaveProcess($server)->run();

		return $this->redirect($this->buildLink('sylphian-verify-manage-servers'));
	}

	protected function serverSaveProcess(GameServer $server): FormAction
	{
		$form = $this->formAction();

		$input = $this->filter([
			'title' => 'str',
			'game' => 'str',
			'host' => 'str',
			'port' => 'uint',
		]);

		$form->basicEntitySave($server, $input);

		return $form;
	}

	public function actionDelete(ParameterBag $params): Redirect|View
	{
		$server = $this->assertServerExists($params->server_id);

		if ($this->isPost())
		{
			$server->delete();

			return $this->redirect($this->buildLink('sylphian-verify-manage-servers'));
		}
		else
		{
			$viewParams = [
				'server' => $server,
			];

			return $this->view('Sylphian\Verify:ServerManagement\Delete', 'sylphian_verify_server_delete', $viewParams);
		}
	}

	/**
	 * @param int $id
	 * @param string|array $with
	 * @param string|null $phraseKey
	 *
	 * @return GameServer
	 */
	protected function assertServerExists($id, $with = null, $phraseKey = null): GameServer
	{
		return $this->assertRecordExists('Sylphian\Verify:GameServer', $id, $with, $phraseKey);
	}
}
