<?php

namespace ProcessWire\Migrations\Command;

use ProcessWire\Migrations;

trait MigrationsModule
{
	/**
	 * @var Migrations|null
	 */
	protected $migrations;

	/**
	 * @param $migrations
	 */
	public function setMigrations ($migrations)
	{
		$this->migrations = $migrations;
	}

	/**
	 * @return bool
	 */
	public function isEnabled ()
	{
		return $this->migrations instanceof Migrations;
	}
}