<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLogsRequestsTable extends Migration
{
    public function up(): void
    {
        Schema::create('logs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->text('url');
            $table->string('controller')->nullable();
            $table->string('controller_method')->nullable();
            $table->json('request_body')->nullable();
            $table->json('request_headers')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->integer('response_status');
            $table->json('response_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->timestamps();
        });
        DB::unprepared('SET GLOBAL event_scheduler = ON');
        DB::unprepared("
            CREATE EVENT IF NOT EXISTS delete_old_logs
            ON SCHEDULE EVERY 1 HOUR
            DO
            DELETE FROM logs_requests WHERE created_at < NOW() - INTERVAL 73 HOUR;
        ");
    }
    public function down(): void
    {
        DB::unprepared('DROP EVENT IF EXISTS delete_old_logs');
        Schema::dropIfExists('logs_requests');
    }
}