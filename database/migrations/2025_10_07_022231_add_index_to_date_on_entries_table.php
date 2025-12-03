<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Statamic\Eloquent\Database\BaseMigration as Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasIndex($this->prefix('entries'), ['date'])) {
            return;
        }

        Schema::table($this->prefix('entries'), function (Blueprint $table) {
            $table->index(['date']);
        });
    }

    public function down()
    {
        if (! Schema::hasIndex($this->prefix('entries'), ['date'])) {
            return;
        }

        Schema::table($this->prefix('entries'), function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }
};
