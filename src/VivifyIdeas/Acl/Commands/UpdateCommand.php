<?php

namespace VivifyIdeas\Acl\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Custom Artisan command for updating ACL permissions.
 */
class UpdateCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'acl:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update ACL user permissions from config file.';

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('reset', InputArgument::OPTIONAL, 'Reset all ACL permissions. Using this option will result in deleting both user and system permissions before importing from config file.'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
    $reset = (bool) $this->argument('reset');
    
    // First import Groups and Roles to avoid foreign checks failures
		\Acl::reloadGroups();
		\Acl::reloadRoles();
    
		\Acl::reloadPermissions($reset);

		$this->info('ACL permissions successfully updated!');
	}

}
