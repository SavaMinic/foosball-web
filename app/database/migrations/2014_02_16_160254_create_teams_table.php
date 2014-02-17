<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('team', function ($table) {
			$table->increments('id');
			$table->string('name')->unique();
			$table->integer('games_won')->default(0);
			$table->integer('games_lost')->default(0);
			$table->integer('goals_scored')->default(0);
			$table->integer('goals_conceded')->default(0);
			$table->text('unique_key')->nullable()->unique();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('team');
	}

}
