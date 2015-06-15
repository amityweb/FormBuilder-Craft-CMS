<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150614_193116_formbuilder_AddUploadSourceColumn extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
    $this->addColumn('formbuilder_forms', 'uploadSource', ColumnType::Varchar);
		return true;
	}
}
