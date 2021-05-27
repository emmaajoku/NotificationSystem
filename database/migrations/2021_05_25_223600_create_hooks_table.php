<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identifier');
            $table->string('title');
            $table->string('notification_message');
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
        Schema::table('hooks', function (Blueprint $table) {
            Schema::dropIfExists('hooks');
        });
    }

    /**
     * @param $webhook
     * @return mixed|null
     */
    public static function getIdentifier($webhook)
    {
        return  self::where('identifier', '=', $webhook)->get();
    }
}
