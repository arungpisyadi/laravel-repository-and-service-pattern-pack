<?php

namespace LaravelEasyRepository\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LaravelEasyRepository\AssistCommand;
use LaravelEasyRepository\CreateFile;
use File;

class MakeService extends Command
{
    use AssistCommand;

    public $signature = 'make:service
        {name : The name of the service }';

    public $description = 'Create a new service class';

    public function handle()
    {
        $name = str_replace(config("easy-repository.service_suffix"), "", $this->argument("name"));
        $className = Str::studly($name);

        $this->checkIfRequiredDirectoriesExist();

        $this->createService($className);
    }

    /**
     * Create the service
     *
     * @param string $className
     * @return void
     */
    public function createService(string $className)
    {
        $nameOfService = $this->getServiceName($className);
        $serviceName = $nameOfService . config("easy-repository.service_suffix");
        $namespace = $this->getNameSpace($className);
        $stubProperties = [
            "{namespace}" => $namespace,
            "{serviceName}" => $serviceName,
            "{repositoryInterface}" => $this->getRepositoryInterfaceName($nameOfService),
            "{repositoryInterfaceNamespace}" => $this->getRepositoryInterfaceNamespace($nameOfService),
        ];
        // check folder exist
        $folder = str_replace('\\','/', $namespace);
        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        // create file
        new CreateFile(
            $stubProperties,
            $this->getServicePath($className),
            __DIR__ . "/stubs/service.stub"
        );
        $this->line("<info>Created service:</info> {$serviceName}");
    }

    /**
     * Get service path
     *
     * @return string
     */
    private function getServicePath($className)
    {
        return $this->appPath() . "/" .
            config("easy-repository.service_directory") .
            "/$className" . "Service.php";
    }

    /**
     * Get repository interface namespace
     *
     * @return string
     */
    private function getRepositoryInterfaceNamespace(string $className)
    {
        return config("easy-repository.repository_namespace") . "\Interfaces";
    }

    /**
     * Get repository interface name
     *
     * @return string
     */
    private function getRepositoryInterfaceName(string $className)
    {
        return $className . "RepositoryInterface";
    }

    /**
     * Check to make sure if all required directories are available
     *
     * @return void
     */
    private function checkIfRequiredDirectoriesExist()
    {
        $this->ensureDirectoryExists(config("easy-repository.service_directory"));
    }

    /**
     * get service name
     * @param $className
     * @return string
     */
    private function getServiceName($className):string {
        $explode = explode('/', $className);
        return $explode[array_key_last($explode)];
    }

    /**
     * get namespace
     * @param $className
     * @return string
     */
    private function getNameSpace($className):string {
        $explode = explode('/', $className);
        if (count($explode) > 1) {
            $namespace = '';
            for($i=0; $i < count($explode)-1; $i++) {
                $namespace .= '\\'.$explode[$i];
            }
            return config("easy-repository.service_namespace").$namespace;
        } else {
            return config("easy-repository.service_namespace");
        }
    }
}
