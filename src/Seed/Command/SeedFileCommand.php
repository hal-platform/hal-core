<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Seed\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\PersisterInterface as Persister;

/**
 * Seed a Fixture from the given file location
 */
class SeedFileCommand extends Command
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Persister
     */
    private $persister;

    /**
     *
     */
    public function __construct(
        $name,
        Loader $loader,
        Persister $persister
    ) {
        parent::__construct($name);

        $this->loader = $loader;
        $this->persister = $persister;
    }

    /**
     * In case of error or critical failure, ensure that we clean up the build artifacts.
     *
     * Note that this is only called for exceptions and non-fatal errors.
     * Fatal errors WILL NOT trigger this.
     *
     * @return null
     */
    public function __destruct()
    {
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setDescription('Seed an Alice fixture from a file.')
            ->addArgument(
                'FIXTURE_LOCATION',
                InputArgument::REQUIRED,
                'File path to the fixture.'
            );
    }

    /**
     * Run the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // There's a class that handles both of these with one method call, and it works fine for files, but since
        // we're already creating both a loader and a persister to handle the yaml scenario, using those is a little
        // DRYer and results in much cleaner DI YAML
        $objects = $this->loader->load($input->getArgument('FIXTURE_LOCATION'));
        $this->persister->persist($objects);
    }
}
