<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetShaCommand extends Command
{
    protected static $defaultName = 'getsha';

    protected function configure()
    {
        $this->setName('getsha');
        $this->setDescription('Prints the SHA1 of the last commit of the chosen repository to the screen. Supported websites: GitHub. Future support will include Bitbucket and GitLab');
        $this->addArgument('repo', null, InputOption::VALUE_REQUIRED);
        $this->addArgument('branch', null, InputOption::VALUE_REQUIRED);
        $this->addOption('service', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validate($input);

        $output->write($this->getSha($input->getArgument('repo'), $input->getArgument('branch')));
    }

    /**
     * @param string $repo
     * @param string $branch
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    private function getSha(string $repo, string $branch)
    {
        $client = new \GuzzleHttp\Client();

        try {
            /** @var string $uri */
            $uri = 'https://api.github.com/repos/' . $repo . '/branches/' . $branch;

            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $client->request('GET', $uri);

            /** @var \stdClass $contents */
            $contents = \GuzzleHttp\json_decode($response->getBody()->getContents());

            return $contents->commit->sha;
        } catch(\Exception $exception) {
            throw new \Exception('Error has occured while connecting to the website');
        }
    }

    /**
     * @param InputInterface $input
     * @return bool
     * @throws \Exception
     */
    private function validateArguments(InputInterface $input)
    {
        if (!$input->getArgument('repo')) {
            throw new \Exception('The repository has not been defined');
        }

        if (!$input->getArgument('branch')) {
            throw new \Exception('The branch has not been defined');
        }

        return true;
    }

    /**
     * @param InputInterface $input
     * @return bool
     * @throws \Exception
     */
    private function validateOptions(InputInterface $input)
    {
        if ($input->getOption('service')) {
            throw new \Exception('Sorry, only GitHub is supported at this time.');
        }

        return true;
    }

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    private function validate(InputInterface $input)
    {
        $this->validateOptions($input);

        $this->validateArguments($input);
    }
}
