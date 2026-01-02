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

			$table->addPrimaryKey('account_id');
			$table->addKey(['user_id', 'provider']);
			$table->addUniqueKey(['provider', 'provider_key'], 'provider_key_unique');
		});
	}

	public function uninstallStep1(): void
	{
		$this->schemaManager()->dropTable('xf_sylphian_verify_account');
	}
}
