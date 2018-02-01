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
use Symfony\Component\Yaml\Parser;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\PersisterInterface as Persister;

/**
 * Seed a Fixture from the given YAML
 */
class SeedYamlCommand extends Command
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
     * @var Parser
     */
    private $parser;

    /**
     *
     */
    public function __construct(
        $name,
        Loader $loader,
        Persister $persister,
        Parser $parser
    ) {
        parent::__construct($name);

        $this->loader = $loader;
        $this->persister = $persister;
        $this->parser = $parser;
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
            ->setDescription('Seed an Alice fixture from piped YAML.')
            ->addArgument(
                'FIXTURE_YAML',
                InputArgument::REQUIRED,
                'String containing fixture in YAML format.'
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
        $data = $this->parser->parse($input->getArgument('FIXTURE_YAML'));

        $objects = $this->loader->load($data);
        $this->persister->persist($objects);
    }
}
