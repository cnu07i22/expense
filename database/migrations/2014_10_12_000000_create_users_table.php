<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile_number')->unique();;
            $table->rememberToken();
            $table->timestamps();
        });
         // Insert some stuff
        DB::table('users')->insert(
           array( 
                array(
                    'name'=>'user1',
                    'email' => 'user1@domain.com',
                    'mobile_number' => '9000000001'
                ),
                array(
                    'name'=>'user2',
                    'email' => 'user2@domain.com',
                    'mobile_number' => '9000000002'
                ),
                array(
                    'name'=>'user3',
                    'email' => 'user3@domain.com',
                    'mobile_number' => '9000000003'
                ),
                array(
                    'name'=>'user4',
                    'email' => 'user4@domain.com',
                    'mobile_number' => '9000000004'
                )
           )  
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
