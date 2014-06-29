<?php

namespace VivifyIdeas\Acl\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Custom Artisan command for installing ACL DB structure.
 */
class InstallCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'acl:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the ACL table structure.';

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new command instance.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();

		$this->files = $files;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('no-foreign', InputArgument::OPTIONAL, 'Do NOT create a migration for adding foreign keys to the database.'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
    if (!file_exists(app_path() . '/config/packages/vivify-ideas/acl/config.php')) {
			$this->createConfig();
		}
    
    $create_foreign_keys = !( (bool)$this->argument('no-foreign') );
    
		$fullPath = $this->createBaseMigration('create_acl_tables');
		$this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/database.stub'));
    
    if ( $create_foreign_keys ) {
  		$fullPath = $this->createBaseMigration('foreign_keys_add_acl_tables');
  		$this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/foreignKeys.stub'));
    }

		$this->info('Migration created successfully, registering new classes...');

    Artisan::call('dump-autoload');
    
    $this->info('...autoloader updated, remember to run `php artisan migrate` to create the new tables!');
	}

	private function createConfig()
	{
		return $this->call('config:publish', array('--path' => 'vendor/vivify-ideas/acl/src/config', 'package' => 'vivify-ideas/acl'));
	}
  
	/**
	 * Create a base migration file.
	 *
	 * @return string
	 */
	protected function createBaseMigration($migrationName)
	{
		$path = $this->laravel['path'].'/database/migrations';

		return $this->laravel['migration.creator']->create($migrationName, $path);
	}


}
