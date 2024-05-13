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
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            $table->string('nome', 60);
            $table->decimal('valor', 10,2);
            $table->string('unidade_cobranca', 5);
            $table->integer('tempo_servico');

            $table->integer('tempo_adicional')->default(0);
            $table->integer('tempo_tolerancia')->default(0);
            $table->decimal('valor_adicional', 10,2)->default(0);

            $table->decimal('comissao', 6, 2)->default(0);
            
            $table->foreignId('categoria_id')->constrained('categoria_servicos')->onDelete('cascade');;

            $table->string('codigo_servico', 10)->nullable();
            $table->decimal('aliquota_iss', 6, 2)->nullable();
            $table->decimal('aliquota_pis', 6, 2)->nullable();
            $table->decimal('aliquota_cofins', 6, 2)->nullable();
            $table->decimal('aliquota_inss', 6, 2)->nullable();
            $table->string('imagem', 25)->nullable();
            $table->boolean('status')->default(1);
            $table->string('codigo_tributacao_municipio', 30)->nullable();
            
            // alter table servicos add column imagem varchar(25) default null;
            // alter table servicos add column status boolean default 1;
            // alter table servicos add column codigo_tributacao_municipio varchar(30) default null;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
