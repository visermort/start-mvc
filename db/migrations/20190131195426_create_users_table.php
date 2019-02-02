<?php


use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('xx_users');
        $table//addColumn('id', 'integer')
            ->addColumn('email', 'string')
            ->addColumn('password', 'string')
            ->addColumn('permissions', 'text', ['null' => true])
            ->addColumn('last_login', 'datetime', ['null' => true])
            ->addColumn('first_name', 'string', ['null' => true])
            ->addColumn('last_name', 'string', ['null' => true])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp')
            ->addIndex(['email'], ['unique' => true])
            ->create();

//        $table->increments('id');
//        $table->string('email');
//        $table->string('password');
//        $table->text('permissions')->nullable();
//        $table->timestamp('last_login')->nullable();
//        $table->string('first_name')->nullable();
//        $table->string('last_name')->nullable();
//        $table->timestamps();
    }
}
