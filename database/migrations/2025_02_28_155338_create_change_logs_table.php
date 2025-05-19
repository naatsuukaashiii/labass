<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateChangeLogsTable extends Migration
{
    public function up()
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('change_logs');
    }
}
