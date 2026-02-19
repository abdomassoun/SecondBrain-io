<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDDDModule extends Command
{
    protected $signature = 'make:ddd-module {name}';
    protected $description = 'Create a new DDD module structure with plural folders and singular files';

    protected Filesystem $fs;

    public function __construct()
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    public function handle()
    {
        $moduleName = ucfirst($this->argument('name')); // e.g., Test
        $moduleFolder = Str::plural($moduleName);       // e.g., Tests

        // Base paths for layers
        $baseAppPath = app_path("Application/{$moduleFolder}");
        $baseDomainPath = app_path("Domain/{$moduleFolder}");
        $baseInfraPath = app_path("Infrastructure");
        $basePresentationPath = app_path("Presentation/Http/{$moduleFolder}");
        // 1️⃣ Create folder structure
        $folders = [
            // Application
            "{$baseAppPath}/Commands",
            "{$baseAppPath}/Handlers",
            "{$baseAppPath}/Queries",
            "{$baseAppPath}/Rules",
            "{$baseAppPath}/Services",

            // Domain
            "{$baseDomainPath}/Entities",
            "{$baseDomainPath}/Events",
            "{$baseDomainPath}/Policies",
            "{$baseDomainPath}/Repositories",
            "{$baseDomainPath}/Rules",
            "{$baseDomainPath}/Services",
            "{$baseDomainPath}/ValueObjects",

            // Infrastructure
            // "{$baseInfraPath}/Persistence/Eloquent/Models",
            // "{$baseInfraPath}/Persistence/Eloquent/Repositories",
            // "{$baseInfraPath}/Persistence/Eloquent/Traits",

            // Presentation
            "{$basePresentationPath}/Controllers/API/V1",
            "{$basePresentationPath}/Requests/API/V1",
            "{$basePresentationPath}/Resources/API/V1",
            "{$basePresentationPath}/Middleware",
            "{$basePresentationPath}/Exceptions",
        ];

        foreach ($folders as $folder) {
                if (!$this->fs->exists($folder)) {
        $this->fs->makeDirectory($folder, 0755, true);
    }

            // $this->fs->makeDirectory($folder, 0755, true);
        }

        // 2️⃣ Create base files
        $this->createDomainEntity($baseDomainPath, $moduleName);
        $this->createDomainPolicy($baseDomainPath, $moduleName);
        $this->createDomainRepositoryInterface($baseDomainPath, $moduleName);
        $this->createApplicationCommands($baseAppPath, $moduleName);
        $this->createApplicationQueries($baseAppPath, $moduleName);
        $this->createHandler($baseAppPath, $moduleName);
        $this->createInfrastructureModel($baseInfraPath, $moduleName);
        $this->createInfrastructureRepository($baseInfraPath, $moduleName);
        $this->createPresentationController($basePresentationPath, $moduleName, $moduleFolder);
        $this->createPresentationRequests($basePresentationPath, $moduleName);
        // $this->createModuleTest($moduleName);

        $this->info("DDD Module '{$moduleName}' created successfully!");
    }

    protected function createDomainEntity($baseDomainPath, $moduleName)
    {
        $namespace = "App\Domain\\" . Str::plural($moduleName) . "\\Entities";
        $path = "{$baseDomainPath}/Entities/{$moduleName}.php";
        $content = <<<PHP
<?php

namespace {$namespace};

use App\Domain\Shared\Entity;

final class {$moduleName} extends Entity
{
    // TODO: Add properties and methods
}

PHP;
        $this->fs->put($path, $content);
    }

    protected function createDomainPolicy($baseDomainPath, $moduleName)
    {
        $namespace = "App\Domain\\" . Str::plural($moduleName) . "\\Policies";
        $className = "{$moduleName}Policy";
        $path = "{$baseDomainPath}/Policies/{$className}.php";

        $content = <<<PHP
<?php

namespace {$namespace};

use App\Infrastructure\Persistence\Eloquent\Models\User;

class {$className}
{
    public function create(User \$actionUser): bool
    {
        return true;
    }
}
PHP;
        $this->fs->put($path, $content);
    }

    protected function createDomainRepositoryInterface($baseDomainPath, $moduleName)
    {
        $namespace = "App\Domain\\" . Str::plural($moduleName) . "\\Repositories";
        $entityClass = "App\Domain\\" . Str::plural($moduleName) . "\\Entities\\{$moduleName} as {$moduleName}Entity";
        $interfaceName = "{$moduleName}RepositoryInterface";
        $path = "{$baseDomainPath}/Repositories/{$interfaceName}.php";
        $content = <<<PHP
<?php

namespace {$namespace};

use {$entityClass};

interface {$interfaceName}
{
    public function findById(string \$id): ?{$moduleName}Entity;

    public function save({$moduleName}Entity \${$moduleName}): void;

    public function delete({$moduleName}Entity \${$moduleName}): void;

    public function update({$moduleName}Entity \${$moduleName}): void;

    public function findAll(): array;

}
PHP;
        $this->fs->put($path, $content);
    }

    protected function createApplicationCommands($baseAppPath, $moduleName)
    {
        $modulePlural = Str::plural($moduleName);
        $namespace = "App\Application\\{$modulePlural}\\Commands";

        $commands = [
            "Create{$moduleName}Command",
            "Update{$moduleName}Command",
            "Delete{$moduleName}Command",
        ];

        foreach ($commands as $className) {
            $path = "{$baseAppPath}/Commands/{$className}.php";

            $content = <<<PHP
    <?php

    namespace {$namespace};

    class {$className}
    {
        // TODO: Add command properties
    }

    PHP;

            // Create directory if it does not exist
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $this->fs->put($path, $content);
            echo "Created: {$path}\n";
        }
    }

    protected function createApplicationQueries($baseAppPath, $moduleName)
    {
        $modulePlural = Str::plural($moduleName);
        $namespace = "App\Application\\{$modulePlural}\\Queries";

        $queries = [
            "Get{$modulePlural}Query",
        ];

        foreach ($queries as $className) {
            $path = "{$baseAppPath}/Queries/{$className}.php";

            $content = <<<PHP
<?php
namespace {$namespace};
final class {$className}
{
    public function __construct(
        // TODO: Add query properties
        public readonly int \$limit = 15,
        public readonly int \$offset = 0
    ) {}

}
PHP;

            // Create directory if it does not exist
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $this->fs->put($path, $content);
            echo "Created: {$path}\n";
        }
    }

    protected function createHandler($baseAppPath, $moduleName)
    {
        $namespace = "App\Application\\" . Str::plural($moduleName) . "\\Handlers";
        $className = "Create{$moduleName}Handler";
        $path = "{$baseAppPath}/Handlers/{$className}.php";
        $commandClass = "App\Application\\" . Str::plural($moduleName) . "\\Commands\\Create{$moduleName}Command";

        $content = <<<PHP
<?php

namespace {$namespace};

use {$commandClass};

class {$className}
{
    public function handle(Create{$moduleName}Command \$command)
    {
        // TODO: Implement handling logic
    }
}

PHP;
        $this->fs->put($path, $content);
    }

    protected function createInfrastructureModel($baseInfraPath, $moduleName)
    {
        $namespace = "App\Infrastructure\\Persistence\\Eloquent\\Models";
        $path = "{$baseInfraPath}/Persistence/Eloquent/Models/{$moduleName}.php";
        $table = Str::snake(Str::plural($moduleName));

        $content = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$moduleName} extends Model
{
    protected \$table = '{$table}';
    protected \$guarded = [];
}

PHP;
        $this->fs->put($path, $content);
    }

    protected function createInfrastructureRepository($baseInfraPath, $moduleName)
    {
        $namespace = "App\Infrastructure\\Persistence\\Eloquent\\Repositories";
        $className = "{$moduleName}Repository";
        $modelClass = "App\Infrastructure\\Persistence\\Eloquent\\Models\\{$moduleName}";
        $interfaceClass = "App\Domain\\" . Str::plural($moduleName) . "\\Repositories\\{$className}Interface";
        $path = "{$baseInfraPath}/Persistence/Eloquent/Repositories/{$className}.php";
        
        /* TODO Dependency Injection
         *  __construct(private Model $model)
         */

        $content = <<<PHP
<?php

namespace {$namespace};

use {$modelClass};
use {$interfaceClass};

class {$className} implements {$className}Interface
{
}
PHP;
        $this->fs->put($path, $content);
    }

    protected function createPresentationController($basePresentationPath, $moduleName, $moduleFolder)
    {
        $namespace = "App\Presentation\Http\\" . Str::plural($moduleName) . "\Controllers\API\V1";
        $className = "{$moduleName}Controller";
        $path = "{$basePresentationPath}/Controllers/API/V1/{$className}.php";

        $content = <<<PHP
<?php

namespace {$namespace};

use App\Presentation\Http\Controller;
use Illuminate\Http\Request;

class {$className} extends Controller
{
    public function index()
    {
        // TODO: return list
    }
}

PHP;
        $this->fs->put($path, $content);
    }
    
    protected function createPresentationRequests($basePresentationPath, $moduleName)
    {
        $modulePlural = Str::plural($moduleName);
        $namespace = "App\Presentation\Http\\{$modulePlural}\\Requests\API\V1";

        $requests = [
            "Create{$moduleName}Request",
            "Update{$moduleName}Request",
        ];

        foreach ($requests as $className) {
            $path = "{$basePresentationPath}/Requests/API/V1/{$className}.php";

            $content = <<<PHP
    <?php

    namespace {$namespace};

    use Illuminate\Foundation\Http\FormRequest;

    class {$className} extends FormRequest
    {
        public function rules(): array
        {
            return [
                // TODO: Add validation rules
            ];
        }
    }
    PHP;

            // Make sure the directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $this->fs->put($path, $content);
            echo "Created request: {$path}\n";
        }
    }

    protected function createModuleTest($moduleName)
    {
        $this->call('pest:test', [
            'name' => "Application/" . Str::plural($moduleName) . "/Handlers/Create{$moduleName}HandlerTest",
            '--unit' => true,
        ]);

        $this->call('pest:test', [
            'name' => "Application/" . Str::plural($moduleName) . "/Commands/Create{$moduleName}CommandTest",
            '--unit' => true,
        ]);

        $this->call('pest:test', [
            'name' => "Domain/" . Str::plural($moduleName) . "/Entities/{$moduleName}Test",
            '--unit' => true,
        ]);

        $this->call('pest:test', [
            'name' => "Domain/" . Str::plural($moduleName) . "/Policies/{$moduleName}PolicyTest",
            '--unit' => true,
        ]);

        $this->call('pest:test', [
            'name' => "Presentation/Http/" . Str::plural($moduleName) . "/Controllers/API/v1/{$moduleName}ControllerTest",
        ]);
    }
}