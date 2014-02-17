<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateMatchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('match', function ($table) {
			$table->increments('id');
			$table->integer('home_team_id');
			$table->integer('away_team_id');
			$table->integer('home_score')->default(0);
			$table->integer('away_score')->default(0);
			$table->boolean('finished')->default(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('match');
	}

}
