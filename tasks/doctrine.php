<?php

namespace Fuel\Tasks;

use \Doctrine_Fuel;

class Doctrine
{
    public static function run($speech = null)
    {
      $em = Doctrine_Fuel::manager();

      $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
          'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
          'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
      ));

      // lets remove oil refine from command line arguments - ugly as hell, but it works ];->
      $_SERVER['argv'] = explode ( ' ', str_replace ( 'oil refine ', '', implode ( ' ', $_SERVER['argv'] ) ) );

      \Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);
    }

    public static function migrate()
    {
      $em = Doctrine_Fuel::manager();

      $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array
      (
          'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
          'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
          'dialog' => new \Symfony\Component\Console\Helper\DialogHelper()
      ));

      $cli = new \Symfony\Component\Console\Application('Doctrine Migrations', \Doctrine\DBAL\Migrations\MigrationsVersion::VERSION);
      $cli->setCatchExceptions(true);
      $cli->setHelperSet($helperSet);
      $cli->addCommands(array(
          // Migrations Commands
          new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
          new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
          new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
          new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
          new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
      ));
      if ($helperSet->has('em')) {
          $cli->add(new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand());
      }

      $input = file_exists('migrations-input.php')
             ? include('migrations-input.php')
             : null;

      $output = file_exists('migrations-output.php')
              ? include('migrations-output.php')
              : null;

      // again... lets remove oil refine from command line arguments - ugly as hell, but it works ];->
      // for now i didn't check how to setup DBALs migrations from the code, so it's hardcoded with conf.yml
      $cmd = preg_replace ( '~^.*:migrate ?~i', '', implode ( ' ', $_SERVER['argv'] ) );
      $_SERVER['argv'] = explode( ' ', 'doctrine:migrate migrations' . ( $cmd ? ':' . $cmd . ' --configuration='.PKGPATH.'doctrine2/config/conf.yml' : '' ) );

      $cli->run($input, $output);
    }
}
