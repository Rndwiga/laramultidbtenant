<?php

namespace gamerwalt\LaraMultiDbTenant;

use Exception;
use gamerwalt\LaraMultiDbTenant\Contracts\IDatabaseProvisioner;
use Illuminate\Contracts\Console\Kernel;
use DB;
use PDO;
use PDOException;

class MysqlDatabaseProvisioner implements IDatabaseProvisioner
{
    /**
     * @type \Illuminate\Contracts\Console\Kernel
     */
    private $kernel;

    /**
     * @type LaraMultiDbTenant
     */
    protected $multiDbTenant;

    /**
     * Constructs the MysqlDatabaseProvisioner
     *
     * @param \Illuminate\Contracts\Console\Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->multiDbTenant = app()->make('laramultitenantdb');
    }

    /**
     * Provisions a database
     *
     * @param      $host
     * @param      $databaseName
     * @param      $username
     * @param      $password
     * @param null $appHost
     *
     * @return mixed
     */
    public function provisionDatabase($host, $databaseName, $username, $password, $isDemo = false, $appHost = null)
    {
        if( !$appHost) {
            $appHost = $this->multiDbTenant->getApplicationHost();
        }

        $this->connectToHost($host);
        $this->createDatabase($databaseName);
        $this->createUser($appHost, $databaseName, $username, $password);
        $this->migrateDatabase($databaseName, $host);
        if ($isDemo) {
            $this->seedDatabase();
        }
        //$this->createDefaultUser($databaseName, $defaultUserEmail, $defaultUserPassowrd);
        $this->disconnectFromHost();
    }

    /**
     * Syncs Database with new migrations created
     *
     * @param $host
     * @param $databaseName
     *
     * @return mixed
     */
    public function syncTenantDatabases($host, $databaseName)
    {
        $this->connectToHost($host, $databaseName);

        $this->migrateDatabase();

        $this->disconnectFromHost();
    }

    /**
     * Connects to the database host
     *
     * @param string $host
     */
    private function connectToHost($host)
    {
        config(['database.connections.tenant_database.database' => $this->multiDbTenant->getDefaultDatabaseName()]);
        config(['database.connections.tenant_database.host' => $host]);

        DB::setDefaultConnection('tenant_database');
        DB::connection()->setFetchMode(PDO::FETCH_ASSOC);
        DB::reconnect('tenant_database');
    }

    /**
     * Creates a database with the current connection
     *
     * @param string $databaseName
     */
    private function createDatabase($databaseName)
    {
        $charSet = config('database.connections.tenant_database.charset');
        $collation = config('database.connections.tenant_database.collation');

        $query = "CREATE SCHEMA $databaseName CHARACTER SET $charSet COLLATE $collation" ;

        $this->execute($query);

        //once the statement has been executed
        //we can now successfully connect to the database
        config(['database.connections.tenant_database.database' => $databaseName]);
        DB::setDefaultConnection('tenant_database');
        DB::connection()->setFetchMode(PDO::FETCH_ASSOC);
        DB::reconnect('tenant_database');
    }

    /**
     * Executes the passed statement
     *
     * @param string $query
     */
    private function execute($query)
    {
        DB::statement($query);
    }

    /**
     * Creates a user for the specific database
     *
     * @param $appHost
     * @param $databaseName
     * @param $username
     * @param $password
     */
    private function createUser($appHost, $databaseName, $username, $password)
    {
        $createUserQuery = "CREATE USER '$username'@'$appHost' IDENTIFIED BY '$password'";

        $this->execute($createUserQuery);

        $grantUserQuery = "GRANT SELECT, INSERT, UPDATE, EXECUTE, DELETE ON $databaseName.* TO '$username'@'$appHost' IDENTIFIED BY '$password'";

        $this->execute($grantUserQuery);
    }

    /**
     * Creates a default database user
     *
     * @param $email
     * @param $password
     */
    private function createDefaultUser($databaseName, $email, $password)
    {
        //once the statement has been executed
        //we can now successfully connect to the database
        config(['database.connections.tenant_database.database' => $databaseName]);
        DB::setDefaultConnection('tenant_database');
        DB::connection()->setFetchMode(PDO::FETCH_ASSOC);
        DB::reconnect('tenant_database');

        $passwordHash = $password;

        $createDefaultUserQuery = "INSERT INTO Users (email, password, remember_token, VALIDATED, user_type, created_at, updated_at) VALUES (" . $email . "', '" . $passwordHash . "', '0', '1', '0', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);";

        $this->execute();
    }

    /**
     * Migrates the database
     */
    private function migrateDatabase()
    {
        // Setup the database migration tables
        // ** Disabled; throwing error for 'migration' table already exists!
        //$this->kernel->call('migrate:install', ['--database' => 'tenant_database']);

        // Migrate the data tables
        $this->kernel->call('migrate', ['--path' => '/database/migrations/tenant', '--database' => 'tenant_database']);
    }

    /**
     * Seeds demo data to the database
     */
    private function seedDatabase()
    {
        $this->kernel->call('db:seed', ['--database' => 'tenant_database']);
    }

    /**
     * Disconnects from the host database
     */
    private function disconnectFromHost()
    {
        DB::disconnect('tenant_database');
    }
}
