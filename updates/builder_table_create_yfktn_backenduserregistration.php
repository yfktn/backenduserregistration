<?php namespace Yfktn\BackendUserRegistration\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateYfktnBackenduserregistration extends Migration
{
    public function up()
    {
        Schema::create('yfktn_backenduserregistration_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->smallInteger('is_approved')->unsigned()->default(0);
            $table->string('ip_addr', 50)->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('yfktn_backenduserregistration_');
    }
}