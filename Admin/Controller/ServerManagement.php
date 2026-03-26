<?php

namespace Sylphian\Verify\Admin\Controller;

use Sylphian\Verify\Entity\Category;
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
			'serversGrouped' => $servers->groupBy('category_id'),
			'totalServers' => $servers->count(),
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

	public function actionCategoryAdd(): View
	{
		$category = $this->em()->create(Category::class);
		return $this->categoryAddEdit($category);
	}

	public function actionCategoryEdit(ParameterBag $params): View
	{
		$id = $params->id ?: $this->filter('category_id', 'uint');
		$category = $this->assertCategoryExists($id);
		return $this->categoryAddEdit($category);
	}

	public function categoryAddEdit(Category $category): View
	{
		$viewParams = [
			'category' => $category,
		];

		return $this->view('Sylphian\Verify:Category\Edit', 'sylphian_verify_category_edit', $viewParams);
	}

	public function actionCategorySave(ParameterBag $params): Redirect
	{
		$id = $params->id ?: $this->filter('category_id', 'uint');
		if ($id)
		{
			$category = $this->assertCategoryExists($id);
		}
		else
		{
			$category = $this->em()->create(Category::class);
		}

		$this->categorySaveProcess($category)->run();

		return $this->redirect($this->buildLink('sylphian-verify-manage-servers'));
	}

	protected function categorySaveProcess(Category $category): FormAction
	{
		$form = $this->formAction();

		$input = $this->filter([
			'title' => 'str',
			'description' => 'str',
			'display_order' => 'uint',
		]);

		$form->basicEntitySave($category, $input);

		return $form;
	}

	public function actionCategoryDelete(ParameterBag $params): Redirect|View
	{
		$id = $params->id ?: $this->filter('category_id', 'uint');
		$category = $this->assertCategoryExists($id);

		if ($this->isPost())
		{
			$category->delete();

			return $this->redirect($this->buildLink('sylphian-verify-manage-servers'));
		}
		else
		{
			$viewParams = [
				'category' => $category,
			];

			return $this->view('Sylphian\Verify:Category\Delete', 'sylphian_verify_category_delete', $viewParams);
		}
	}

	/**
	 * @throws Exception
	 */
	public function actionEdit(ParameterBag $params): View
	{
		$server = $this->assertServerExists($params->id);
		return $this->serverAddEdit($server);
	}

	/**
	 * @throws Exception
	 * @throws PrintableException
	 */
	public function actionSave(ParameterBag $params): Redirect
	{
		$id = $params->id ?: $this->filter('server_id', 'uint');
		if ($id)
		{
			$server = $this->assertServerExists($params->id);
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
		$server = $this->assertServerExists($params->id);

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

	protected function assertCategoryExists(int $id, array|string|null $with = null, ?string $phraseKey = null): Category
	{
		return $this->assertRecordExists(Category::class, $id, $with, $phraseKey);
	}
}
