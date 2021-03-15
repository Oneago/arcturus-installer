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
            ->setDescription('Create a new Arcturus php application')
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
                                                                                                             
                                                                                                             

                                                                                                                                                                       
                                                                                                                                                                       
               AAA                                                               tttt                                                                                  
              A:::A                                                           ttt:::t                                                                                  
             A:::::A                                                          t:::::t                                                                                  
            A:::::::A                                                         t:::::t                                                                                  
           A:::::::::A          rrrrr   rrrrrrrrr       ccccccccccccccccttttttt:::::ttttttt    uuuuuu    uuuuuu rrrrr   rrrrrrrrr   uuuuuu    uuuuuu      ssssssssss   
          A:::::A:::::A         r::::rrr:::::::::r    cc:::::::::::::::ct:::::::::::::::::t    u::::u    u::::u r::::rrr:::::::::r  u::::u    u::::u    ss::::::::::s  
         A:::::A A:::::A        r:::::::::::::::::r  c:::::::::::::::::ct:::::::::::::::::t    u::::u    u::::u r:::::::::::::::::r u::::u    u::::u  ss:::::::::::::s 
        A:::::A   A:::::A       rr::::::rrrrr::::::rc:::::::cccccc:::::ctttttt:::::::tttttt    u::::u    u::::u rr::::::rrrrr::::::ru::::u    u::::u  s::::::ssss:::::s
       A:::::A     A:::::A       r:::::r     r:::::rc::::::c     ccccccc      t:::::t          u::::u    u::::u  r:::::r     r:::::ru::::u    u::::u   s:::::s  ssssss 
      A:::::AAAAAAAAA:::::A      r:::::r     rrrrrrrc:::::c                   t:::::t          u::::u    u::::u  r:::::r     rrrrrrru::::u    u::::u     s::::::s      
     A:::::::::::::::::::::A     r:::::r            c:::::c                   t:::::t          u::::u    u::::u  r:::::r            u::::u    u::::u        s::::::s   
    A:::::AAAAAAAAAAAAA:::::A    r:::::r            c::::::c     ccccccc      t:::::t    ttttttu:::::uuuu:::::u  r:::::r            u:::::uuuu:::::u  ssssss   s:::::s 
   A:::::A             A:::::A   r:::::r            c:::::::cccccc:::::c      t::::::tttt:::::tu:::::::::::::::uur:::::r            u:::::::::::::::uus:::::ssss::::::s
  A:::::A               A:::::A  r:::::r             c:::::::::::::::::c      tt::::::::::::::t u:::::::::::::::ur:::::r             u:::::::::::::::us::::::::::::::s 
 A:::::A                 A:::::A r:::::r              cc:::::::::::::::c        tt:::::::::::tt  uu::::::::uu:::ur:::::r              uu::::::::uu:::u s:::::::::::ss  
AAAAAAA                   AAAAAAArrrrrrr                cccccccccccccccc          ttttttttttt      uuuuuuuu  uuuurrrrrrr                uuuuuuuu  uuuu  sssssssssss    
                                                                                                                                                                       
                                                                                                                                                                       
                                                                                                                                                                       
                                                                                                                                                                       
                                                                                                                                                                       
                                                                                                                                                                       
                                                                                                                                                                                               
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
            $composer . " composer create-project oneago/arcturus-project \"$directory\" --remove-vcs --prefer-dist",
        ];

        if ($directory != '.' && $input->getOption('force')) {
            if (PHP_OS_FAMILY == 'Windows') {
                array_unshift($commands, "rd /s /q \"$directory\"");
            } else {
                array_unshift($commands, "rm -rf \"$directory\"");
            }
        }

        if (PHP_OS_FAMILY != 'Windows') {
            $commands[] = "chmod 755 \"$directory/ada\"";
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
