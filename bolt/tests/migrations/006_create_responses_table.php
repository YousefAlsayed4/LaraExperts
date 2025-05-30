<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('form-bolt.table-prefix') . 'responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained(config('form-bolt.table-prefix') . 'forms');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('status')->default('NEW');
            $table->text('notes')->nullable();
            $table->integer('extension_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('form-bolt.table-prefix') . 'responses');
    }
};
