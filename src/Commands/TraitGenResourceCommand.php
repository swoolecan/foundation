<?php

declare(strict_types=1);

namespace Swoolecan\Foundation\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

trait TraitGenResourceCommand
{
    public function configure()
    {
        parent::configure();
        $this->setDescription('create elems of a resource such as model request response collection repository service');
        $this->addOption('service', 's', InputOption::VALUE_NONE, 'Create elems include service');
        $this->addOption('rename', null, InputOption::VALUE_OPTIONAL, 'Resouce rename');
        $this->addOption('rpc-client', null, InputOption::VALUE_NONE, 'Create elems include rpc-client');
        $this->addOption('rpc-server', null, InputOption::VALUE_NONE, 'Create elems include rpc-server');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $name = $this->getNameInput();
        $name = ucfirst($name);
        $rpcClient = $this->input->getOption('rpc-client');
        $rpcService = $this->input->getOption('rpc-server');
        $service = $this->input->getOption('service');
        $force = $input->getOption('force');
        $newname = $input->getOption('rename');
        $newname = empty($newname) ? '' : ucfirst($newname);
        $elems = ['controller', 'model', 'repository', 'resource', 'collection', 'request'];
        if ($rpcClient) {
            $elems[] = 'rpc-client';
        }
        if ($rpcService) {
            $elems[] = 'rpc-service';
        }
        if ($service) {
            $elems[] = 'service';
        }
        foreach ($elems as $elem) {

            $path = $this->getPath($elem);
            echo $name . "\n";
            $className = $this->getClassName($name, $elem);
            $file = $path . '/' . $className . '.php';
            $file = str_replace('\\', '/', $file);
            echo $file . "\n";
            if ($newname) {
                $newClass = $this->getClassName($newname, $elem);
                $newFile = $path . '/' . $newClass . '.php';
                $newFile = str_replace('\\', '/', $newFile);
                if (!empty($newname) && file_exists($file) && !file_exists($newFile)) {
                    $content = file_get_contents($file);
                    $newContent = str_replace("class $className", "class $newClass", $content);
                    file_put_contents($newFile, $newContent);
                    unlink($file);
                    $output->writeln(sprintf('<info>%s</info>', $name . ' rename to ' . $newClass . ' success'));
                } else {
                    $output->writeln(sprintf('<info>%s</info>', $name . ' rename to ' . $newClass . ' failed'));
                }
                continue;
            }
    
            // code is untouched. Otherwise, we will continue generating this class' files.
            if (empty($force) && file_exists($file)) {
                $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
                continue;
            }
    
            $this->makeDirectory($path);
    
            file_put_contents($file, $this->createClass($className, $elem));
    
            $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));
        }

        return 0;
    }

    /**
     * @param string $elem
     * @return string
     */
    protected function getClassName($name, $elem)
    {
        $suffix = ucfirst($elem);
        switch ($elem) {
        case 'resource':
        case 'model':
            $suffix = '';
            break;
        case 'rpc-cliet':
        case 'rpc-server':
            $suffix = 'Service';
            break;
        }
        return $name . $suffix;
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($elem)
    {
        $path = ucfirst($elem);
        switch ($elem) {
        case 'repository':
            $path = 'Repositories';
            break;
        case 'rpc-cliet':
            $path = 'JsonRpcClient';
            break;
        case 'rpc-server':
            $path = 'JsonRpcServer';
            break;
        case 'collection':
            $path = 'Resources';
            break;
        default:
            $path = ucfirst($elem) . 's';
        }
        return BASE_PATH . '/app/' . $path;
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Resource';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function createClass($className, $elem)
    {
        $stub = __DIR__ . '/stubs/' . $elem . '.stub';
        $content = file_get_contents($stub);

        return $this->replaceNamespace($content, $className)->replaceClass($content, $className);
    }

    protected function getStub(): string
    {
        return '';
    }
}
