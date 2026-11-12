<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->default(1);
            $table->text('description')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        // El primer usuario registrado queda como administrador.
        if ($first = DB::table('users')->orderBy('id')->first()) {
            DB::table('users')->where('id', $first->id)->update(['is_admin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('rooms', fn (Blueprint $t) => $t->dropColumn(['capacity', 'description']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_admin'));
    }
};
