<?php

namespace Sylphian\Verify\Admin\Controller;

use Sylphian\Verify\Entity\GameServer;
use Sylphian\Verify\Repository\CategoryRepository;
use Sylphian\Verify\Repository\GameServerRepository;
use XF\Admin\Controller\AbstractController;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\PrintableException;

class ServerManagement extends AbstractController
{
	/**
	 * @throws Exception
	 */
	protected function preDispatchController($action, ParameterBag $params): void
	{
		$this->assertAdminPermission('syl_verify_manageServers');
	}

	public function actionIndex(): View
	{
		$serverRepo = $this->repository(GameServerRepository::class);
		$categoryRepo = $this->repository(CategoryRepository::class);

		$categories = $categoryRepo->findCategoriesForList()->fetch();
		$servers = $serverRepo->findServersForList()->fetch();

		$viewParams = [
			'categories' => $categories,
			'servers' => $servers,
		];

		return $this->view('Sylphian\Verify:ServerManagement\List', 'sylphian_verify_server_list', $viewParams);
	}

	public function serverAddEdit(GameServer $server): View
	{
		$categoryRepo = $this->repository(CategoryRepository::class);
		$categories = $categoryRepo->findCategoriesForList()->fetch();

		$viewParams = [
			'server' => $server,
			'categories' => $categories,
		];

		return $this->view('Sylphian\Verify:ServerManagement\Edit', 'sylphian_verify_server_edit', $viewParams);
	}

	public function actionAdd(): View
	{
		$server = $this->em()->create(GameServer::class);
		return $this->serverAddEdit($server);
	}

	/**
	 * @throws Exception
	 */
	public function actionEdit(ParameterBag $params): View
	{
		$server = $this->assertServerExists($params->server_id);
		return $this->serverAddEdit($server);
	}

	/**
	 * @throws Exception
	 * @throws PrintableException
	 */
	public function actionSave(ParameterBag $params): Redirect
	{
		if ($params->server_id)
		{
			$server = $this->assertServerExists($params->server_id);
		}
		else
		{
			$server = $this->em()->create(GameServer::class);
		}

		$this->serverSaveProcess($server)->run();

		return $this->redirect($this->buildLink('sylphian-verify-manage-servers'));
	}

	protected function serverSaveProcess(GameServer $server): FormAction
	{
		$form = $this->formAction();

		$input = $this->filter([
			'title' => 'str',
			'category_id' => 'uint',
			'display_order' => 'uint',
			'game' => 'str',
			'host' => 'str',
			'port' => 'uint',
			'show_port' => 'bool',
		]);

		$form->basicEntitySave($server, $input);

		return $form;
	}

	/**
	 * @throws Exception
	 * @throws PrintableException
	 */
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
	 * @param array|string|null $with
	 * @param string|null $phraseKey
	 *
	 * @return GameServer
	 * @throws Exception
	 */
	protected function assertServerExists(int $id, array|string|null $with = null, ?string $phraseKey = null): GameServer
	{
		return $this->assertRecordExists(GameServer::class, $id, $with, $phraseKey);
	}
}
