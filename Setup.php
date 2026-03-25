<?php

namespace Sylphian\Verify;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1(): void
	{
		$this->schemaManager()->createTable('xf_sylphian_verify_account', function (Create $table)
		{
			$table->addColumn('account_id', 'int')->autoIncrement();
			$table->addColumn('user_id', 'int');
			$table->addColumn('provider', 'varchar', 50);
			$table->addColumn('provider_key', 'varchar', 100);
			$table->addColumn('username', 'varchar', 100);
			$table->addColumn('add_date', 'int')->unsigned();
			$table->addColumn('confirmed', 'tinyint')->setDefault(0);
			$table->addColumn('confirmed_date', 'int')->unsigned()->setDefault(0);

			$table->addPrimaryKey('account_id');
			$table->addKey(['user_id', 'provider']);
			$table->addUniqueKey(['provider', 'provider_key'], 'provider_key_unique');
		});
	}

	public function installStep2(): void
	{
		$this->schemaManager()->createTable('xf_sylphian_verify_server', function (Create $table)
		{
			$table->addColumn('server_id', 'int')->autoIncrement();
			$table->addColumn('title', 'varchar', 100);
			$table->addColumn('game', 'varchar', 50);
			$table->addColumn('host', 'varchar', 100);
			$table->addColumn('port', 'int')->unsigned()->setDefault(25565);
			$table->addColumn('show_port', 'tinyint')->setDefault(1);

			$table->addPrimaryKey('server_id');
		});
	}

	public function uninstallStep1(): void
	{
		$this->schemaManager()->dropTable('xf_sylphian_verify_account');
	}

	public function uninstallStep2(): void
	{
		$this->schemaManager()->dropTable('xf_sylphian_verify_server');
	}
}
