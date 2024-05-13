<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_gerals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('balanca_valor_peso', ['peso', 'valor']);
            $table->integer('balanca_digito_verificador')->nullable();
            $table->boolean('confirmar_itens_prevenda')->default(0);
            $table->text('notificacoes');

            // alter table config_gerals add column confirmar_itens_prevenda boolean default 0;
            // alter table config_gerals modify column balanca_digito_verificador integer default null;
            // alter table config_gerals add column notificacoes text;


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_gerals');
    }
};
