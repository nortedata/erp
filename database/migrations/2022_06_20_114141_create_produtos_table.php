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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->foreignId('categoria_id')->nullable()->constrained('categoria_produtos');
            $table->foreignId('padrao_id')->nullable()->constrained('padrao_tributacao_produtos');
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            $table->foreignId('variacao_modelo_id')->nullable();

            $table->string('nome', 80);
            $table->string('codigo_barras', 20)->nullable();
            $table->string('referencia', 20)->nullable();
            $table->string('ncm', 10);
            $table->string('unidade', 20);
            $table->string('imagem', 25)->nullable();

            $table->decimal('perc_icms', 10,2)->default(0);
            $table->decimal('perc_pis', 10,2)->default(0);
            $table->decimal('perc_cofins', 10,2)->default(0);
            $table->decimal('perc_ipi', 10,2)->default(0);
            $table->string('cest', 10)->nullable();
            $table->integer('origem')->default(0);

            $table->string('cst_csosn', 3)->nullable();
            $table->string('cst_pis', 3)->nullable();
            $table->string('cst_cofins', 3)->nullable();
            $table->string('cst_ipi', 3)->nullable();
            
            $table->decimal('perc_red_bc', 5,2)->nullable();
            $table->decimal('pST', 5,2)->nullable();

            $table->decimal('valor_unitario', 12,4);
            $table->decimal('valor_compra', 12,4);

            $table->string('cfop_estadual', 4);
            $table->string('cfop_outro_estado', 4);

            $table->string('cfop_entrada_estadual', 4)->nullable();
            $table->string('cfop_entrada_outro_estado', 4)->nullable();
            $table->string('codigo_beneficio_fiscal', 15)->nullable();
            $table->string('cEnq', 3)->nullable();

            $table->boolean('gerenciar_estoque')->default(0);

            $table->decimal('adRemICMSRet', 10, 4)->default(0);
            $table->decimal('pBio', 10, 4)->default(0);
            $table->boolean('tipo_servico')->default(0);
            $table->integer('indImport')->default(0);
            $table->string('cUFOrig', 2)->nullable();
            $table->decimal('pOrig', 5, 2)->default(0);

            $table->string('codigo_anp', 10)->nullable();
            $table->decimal('perc_glp', 5,2)->default(0);
            $table->decimal('perc_gnn', 5,2)->default(0);
            $table->decimal('perc_gni', 5,2)->default(0);
            $table->decimal('valor_partida', 10, 2)->default(0);
            $table->string('unidade_tributavel', 4)->default('');
            $table->decimal('quantidade_tributavel', 10, 2)->default(0);

            $table->boolean('status')->default(1);
            $table->boolean('cardapio')->default(0);
            $table->boolean('delivery')->default(0);
            $table->boolean('ecommerce')->default(0);
            $table->string('nome_en', 80)->nullable();
            $table->string('nome_es', 80)->nullable();
            $table->string('descricao', 255)->nullable();
            $table->string('descricao_en', 255)->nullable();
            $table->string('descricao_es', 255)->nullable();
            $table->decimal('valor_cardapio', 12, 4)->nullable();
            $table->decimal('valor_delivery', 12, 4)->nullable();

            $table->boolean('destaque_delivery')->nullable();
            
            $table->integer('tempo_preparo')->nullable();
            $table->boolean('tipo_carne')->default(0);

            $table->boolean('composto')->default(0);

            $table->decimal('estoque_minimo', 5,2)->default(0);
            $table->integer('alerta_validade')->nullable();

            $table->integer('referencia_balanca')->nullable();

            //variaveis para ecommerce

            $table->decimal('valor_ecommerce', 12, 4)->nullable();
            $table->boolean('destaque_ecommerce')->nullable();
            $table->integer('percentual_desconto')->nullable();
            $table->string('descricao_ecommerce', 255)->nullable();
            $table->text('texto_ecommerce');
            $table->decimal('largura', 8, 2)->nullable();
            $table->decimal('comprimento', 8, 2)->nullable();
            $table->decimal('altura', 8, 2)->nullable();
            $table->decimal('peso', 12, 3)->nullable();
            $table->string('hash_ecommerce', 50)->nullable();
            $table->string('hash_delivery', 50)->nullable();
            $table->text('texto_delivery');

            $table->string('mercado_livre_id', 20)->nullable();
            $table->string('mercado_livre_link', 255)->nullable();
            $table->decimal('mercado_livre_valor', 12, 4)->nullable();
            $table->string('mercado_livre_categoria', 20)->nullable();
            $table->string('condicao_mercado_livre', 20)->nullable();
            $table->integer('quantidade_mercado_livre')->nullable();
            $table->string('mercado_livre_tipo_publicacao', 20)->nullable();
            $table->string('mercado_livre_youtube', 100)->nullable();
            $table->text('mercado_livre_descricao');
            $table->string('mercado_livre_status', 20);

            // alter table produtos add column valor_compra decimal(12,4);
            // alter table produtos add column valor_delivery decimal(12,4);
            // alter table produtos add column tempo_preparo integer default null;
            // alter table produtos add column referencia varchar(20) default null;

            // alter table produtos add column adRemICMSRet decimal(10,4) default 0;
            // alter table produtos add column pBio decimal(10,4) default 0;
            // alter table produtos add column tipo_servico boolean default 0;
            // alter table produtos add column delivery boolean default 0;
            // alter table produtos add column cUFOrig varchar(2) default null;
            // alter table produtos add column pOrig decimal(5,2) default 0;
            // alter table produtos add column indImport integer default 0;

            // alter table produtos add column codigo_anp varchar(10) default null;
            // alter table produtos add column perc_glp decimal(5,2) default 0;
            // alter table produtos add column perc_gnn decimal(5,2) default 0;
            // alter table produtos add column perc_gni decimal(5,2) default 0;
            // alter table produtos add column valor_partida decimal(10, 2) default 0;
            // alter table produtos add column unidade_tributavel varchar(4) default '';
            // alter table produtos add column quantidade_tributavel decimal(10, 2) default 0;

            // alter table produtos add column composto boolean default 0;

            // alter table produtos add column estoque_minimo decimal(5, 2) default 0;
            // alter table produtos add column alerta_validade integer default 0;

            // alter table produtos add column referencia_balanca integer default null;
            // alter table produtos add column variacao_modelo_id integer default null;

            // alter table produtos add column cfop_entrada_estadual varchar(4) default null;
            // alter table produtos add column cfop_entrada_outro_estado varchar(4) default null;


            // alter table produtos add column ecommerce boolean default 0;
            // alter table produtos add column valor_ecommerce decimal(12,4) default null;
            // alter table produtos add column percentual_desconto integer default null;
            // alter table produtos add column descricao_ecommerce varchar(255) default null;
            // alter table produtos add column largura decimal(8, 2) default null;
            // alter table produtos add column comprimento decimal(8, 2) default null;
            // alter table produtos add column altura decimal(8, 2) default null;
            // alter table produtos add column peso decimal(12, 3) default null;
            // alter table produtos add column destaque_ecommerce boolean default 0;
            // alter table produtos add column destaque_delivery boolean default 0;
            // alter table produtos add column hash_ecommerce varchar(50) default null;
            // alter table produtos add column hash_delivery varchar(50) default null;
            // alter table produtos add column texto_ecommerce text;
            // alter table produtos add column texto_delivery text;
            // alter table produtos add column mercado_livre_id varchar(20) default null;
            // alter table produtos add column mercado_livre_link varchar(255) default null;
            // alter table produtos add column mercado_livre_valor decimal(12, 4) default null;

            // alter table produtos add column mercado_livre_categoria varchar(20) default null;
            // alter table produtos add column condicao_mercado_livre varchar(20) default null;
            // alter table produtos add column quantidade_mercado_livre integer default null;
            // alter table produtos add column mercado_livre_tipo_publicacao varchar(20) default null;
            // alter table produtos add column mercado_livre_youtube varchar(100) default null;
            // alter table produtos add column mercado_livre_descricao text;
            // alter table produtos add column mercado_livre_status varchar(20) default null;
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
