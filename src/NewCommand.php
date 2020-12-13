<?php

namespace Oneago\Installer\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class NewCommand extends Command
{
    protected static $defaultName = "new";

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Create a new Oneago php application')
            ->addArgument('name', InputArgument::OPTIONAL)
            ->addOption('dev', 'd', InputOption::VALUE_NONE, 'Installs the latest "development" release')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(PHP_EOL . '<fg=blue>
                                                                                                             
                                                                                                             
     OOOOOOOOO                                                                                               
   OO:::::::::OO                                                                                             
 OO:::::::::::::OO                                                                                           
O:::::::OOO:::::::O                                                                                          
O::::::O   O::::::Onnnn  nnnnnnnn        eeeeeeeeeeee    aaaaaaaaaaaaa     ggggggggg   ggggg   ooooooooooo   
O:::::O     O:::::On:::nn::::::::nn    ee::::::::::::ee  a::::::::::::a   g:::::::::ggg::::g oo:::::::::::oo 
O:::::O     O:::::On::::::::::::::nn  e::::::eeeee:::::eeaaaaaaaaa:::::a g:::::::::::::::::go:::::::::::::::o
O:::::O     O:::::Onn:::::::::::::::ne::::::e     e:::::e         a::::ag::::::ggggg::::::ggo:::::ooooo:::::o
O:::::O     O:::::O  n:::::nnnn:::::ne:::::::eeeee::::::e  aaaaaaa:::::ag:::::g     g:::::g o::::o     o::::o
O:::::O     O:::::O  n::::n    n::::ne:::::::::::::::::e aa::::::::::::ag:::::g     g:::::g o::::o     o::::o
O:::::O     O:::::O  n::::n    n::::ne::::::eeeeeeeeeee a::::aaaa::::::ag:::::g     g:::::g o::::o     o::::o
O::::::O   O::::::O  n::::n    n::::ne:::::::e         a::::a    a:::::ag::::::g    g:::::g o::::o     o::::o
O:::::::OOO:::::::O  n::::n    n::::ne::::::::e        a::::a    a:::::ag:::::::ggggg:::::g o:::::ooooo:::::o
 OO:::::::::::::OO   n::::n    n::::n e::::::::eeeeeeeea:::::aaaa::::::a g::::::::::::::::g o:::::::::::::::o
   OO:::::::::OO     n::::n    n::::n  ee:::::::::::::e a::::::::::aa:::a gg::::::::::::::g  oo:::::::::::oo 
     OOOOOOOOO       nnnnnn    nnnnnn    eeeeeeeeeeeeee  aaaaaaaaaa  aaaa   gggggggg::::::g    ooooooooooo   
                                                                                    g:::::g                  
                                                                        gggggg      g:::::g                  
                                                                        g:::::gg   gg:::::g                  
                                                                         g::::::ggg:::::::g                  
                                                                          gg:::::::::::::g                   
                                                                            ggg::::::ggg                     
                                                                               gggggg                        
</>' . PHP_EOL . PHP_EOL);

        sleep(1);
        $name = $input->getArgument('name');

        $directory = $name && $name !== '.' ? getcwd() . '/' . $name : '.';

        if (!$input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        if ($input->getOption('force') && $directory === '.') {
            throw new RuntimeException('Cannot use --force option when using current directory for installation!');
        }

        $composer = $this->findComposer();

        $commands = [
            $composer . " create-project oneago/oneago-php-template \"$directory\" --remove-vcs --prefer-dist",
        ];

        if ($directory != '.' && $input->getOption('force')) {
            if (PHP_OS_FAMILY == 'Windows') {
                array_unshift($commands, "rd /s /q \"$directory\"");
            } else {
                array_unshift($commands, "rm -rf \"$directory\"");
            }
        }

        if (PHP_OS_FAMILY != 'Windows') {
            $commands[] = "chmod 755 \"$directory/oneago\"";
        }

        if (($process = $this->runCommands($commands, $output))) {
            $output->writeln(PHP_EOL . '<comment>Application ready! Create something cool.</comment>');
        }

        return $process->getExitCode();
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist(string $directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Get the version that should be downloaded.
     *
     * @param InputInterface $input
     * @return string
     */
    protected function getVersion(InputInterface $input)
    {
        if ($input->getOption('dev')) {
            return 'dev-develop';
        }
        return '';
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }

    /**
     * Run the given commands.
     *
     * @param array $commands
     * @param OutputInterface $output
     * @return Process
     */
    protected function runCommands(array $commands, OutputInterface $output)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('Warning: ' . $e->getMessage());
            }
        }

        $process->run(function ($line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }
}
