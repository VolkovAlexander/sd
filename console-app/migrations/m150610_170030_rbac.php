<?php

class m150610_170030_rbac extends \cookyii\db\Migration
{

    /**
     * @throws yii\base\InvalidConfigException
     * @return yii\rbac\DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof \yii\rbac\DbManager) {
            throw new \yii\base\InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }

    public function up()
    {
        $authManager = $this->getAuthManager();

        $this->createTable($authManager->ruleTable, [
            'pkey' => ['name'],
            'schema' => [
                'name' => $this->string(64)->notNull(),
                'data' => $this->text(),
                'created_at' => $this->unixTimestamp(),
                'updated_at' => $this->unixTimestamp(),
            ],
        ]);

        $this->createTable($authManager->itemTable, [
            'pkey' => ['name'],
            'schema' => [
                'name' => $this->string(64)->notNull(),
                'type' => $this->integer()->notNull(),
                'description' => $this->text(),
                'rule_name' => $this->string(64),
                'data' => $this->text(),
                'created_at' => $this->unixTimestamp(),
                'updated_at' => $this->unixTimestamp(),
            ],
            'indexes' => [
                'idx_type' => ['type'],
                'idx_rule' => ['rule_name'],
            ],
            'fkeys' => [
                'fkey_rbac_item_rule' => [
                    'from' => 'rule_name',
                    'to' => [$authManager->ruleTable, 'name'],
                    'delete' => 'SET NULL',
                    'update' => 'CASCADE',
                ],
            ],
        ]);

        $this->createTable($authManager->itemChildTable, [
            'pkey' => ['parent', 'child'],
            'schema' => [
                'parent' => $this->string(64)->notNull(),
                'child' => $this->string(64)->notNull(),
            ],
            'indexes' => [
                'idx_parent' => ['parent'],
                'idx_child' => ['child'],
            ],
            'fkeys' => [
                'fkey_rbac_item_parent' => [
                    'from' => 'parent',
                    'to' => [$authManager->itemTable, 'name'],
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ],
                'fkey_rbac_item_child' => [
                    'from' => 'child',
                    'to' => [$authManager->itemTable, 'name'],
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ],
            ],
        ]);

        $this->createTable($authManager->assignmentTable, [
            'pkey' => ['item_name', 'user_id'],
            'schema' => [
                'item_name' => $this->string(64)->notNull(),
                'user_id' => $this->string(64)->notNull(),
                'created_at' => $this->unixTimestamp(),
            ],
        ]);
    }

    public function down()
    {
        $authManager = $this->getAuthManager();

        $this->dropTable($authManager->assignmentTable);

        $this->dropForeignKey('fkey_rbac_item_child', $authManager->itemChildTable);
        $this->dropForeignKey('fkey_rbac_item_parent', $authManager->itemChildTable);

        $this->dropTable($authManager->itemChildTable);

        $this->dropForeignKey('fkey_rbac_item_rule', $authManager->itemTable);

        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }
}
