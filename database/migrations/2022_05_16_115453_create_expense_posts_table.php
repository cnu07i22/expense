<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('added');
            $table->decimal('amount', 10, 2);
            $table->integer('number_users');
            $table->string('split_type')->nullable();;
            $table->json('split_users')->nullable();;
            $table->json('split_values')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_posts');
    }
}
