<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Logstash;

use Doctrine\Persistence\ManagerRegistry;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\Indexer\MappingHelperInterface;
use LoyaltyCorp\Search\Interfaces\Logstash\MigratorInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class Migrator implements MigratorInterface
{
    private $columnMapping;

    /**
     * @var string
     */
    private $curatorConfig;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var bool
     */
    private $hasProvider;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface
     */
    private $indexNameTransformer;

    /**
     * @var null|string
     */
    private $logstashPath;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Indexer\MappingHelperInterface
     */
    private $mappingHelper;

    private $noSqlMappings = [];

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $pipelineConfig;

    /**
     * @var string
     */
    private $providerClass;

    /**
     * @var array
     */
    private $searchHandlers;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Filesystem $filesystem,
        MappingHelperInterface $mappingHelper,
        IndexNameTransformerInterface $indexNameTransformer,
        RegisteredSearchHandlersInterface $handlers,
        ?string $logstashPath = null
    ) {
        $this->entityManager = $managerRegistry->getManager();
        $this->filesystem = $filesystem;
        $this->mappingHelper = $mappingHelper;
        $this->indexNameTransformer = $indexNameTransformer;
        $this->searchHandlers = $handlers->getAll();
        $this->logstashPath = $logstashPath ?? './docker/logstash';
    }

    public function migrate(OutputInterface $output, string $providerClass): void
    {
        if (\class_exists($providerClass) === false) {
            $output->writeln(\sprintf('%s class does not exist', $providerClass));

            return;
        }
        $this->providerClass = $providerClass;
        $this->output = $output;
        $this->hasProvider = false;
        $this->curatorConfig = '';
        $this->pipelineConfig = '';

        // Copy base files
        $this->filesystem->mirror(__DIR__ . '/../../templates/logstash', $this->logstashPath);
        $output->writeln(\sprintf('Base files copied to %s...', $this->logstashPath));

        foreach ($this->searchHandlers as $searchHandler) {
            $this->doMigrate($searchHandler);
        }

        // Write pipeline configurations.
        $this->writeFile(
            './docker/logstash/config/pipelines.yml',
            "# Input \n" . $this->pipelineConfig . $this->templatePipeline()
        );

        // Write entry point with curator configurations.
        $this->writeFile(
            './docker/logstash/docker-entrypoint.sh',
            \str_replace('#[ADD CURATOR CONFIG]', $this->curatorConfig, $this->templateEntryPoint())
        );
    }

    private function buildInputPipeline(array $esProperties, string $indexName, string $inputTemplate): void
    {
        $sqlToEsMapping = '';
        if ($this->hasProvider === true) {
            $sqlToEsMapping .= 'merge => { "_access_tokens" => "provider_id" }' . "\n\n    ";
        }

        foreach ($esProperties as $propertyName => $definition) {
            if (\in_array($propertyName, ['_access_tokens', '@timestamp', '@version'])) {
                continue;
            }
            $sqlColumn = $this->columnMapping[$propertyName] ?? 'NO_SQL_MAPPING_FOUND';
            if ($sqlColumn === 'NO_SQL_MAPPING_FOUND') {
                $this->noSqlMappings[$indexName][] = $propertyName;
            }
            if ($sqlColumn === $propertyName) {
                // No need to rename.
                continue;
            }
            $sqlToEsMapping .= \sprintf(
                'rename => { "%s" => "%s" }%s',
                $sqlColumn,
                $propertyName, "\n    "
            );
        }

        // Create input pipeline
        $inputPipeline = \str_replace(
            ['[SQL_FILENAME]', '[INDEX_NAME]', '[SQL_FIELDS_TO_ES_PROPS]'],
            [$this->toSnake($indexName), $indexName, $sqlToEsMapping],
            $inputTemplate
        );

        $this->writeFile(
            \sprintf('./docker/logstash/pipeline/input/%s.conf', $this->toSnake($indexName)),
            $inputPipeline
        );
    }

    private function buildSelectSql(SearchHandlerInterface $handler, array $properties, string $indexName)
    {
        $meta = $this->entityManager->getClassMetadata($this->getEntityClass($handler));
        $tableName = $meta->getTableName();
        $columns = $meta->getColumnNames();
        \sort($columns);

        $this->hasDeletedAt = \in_array('deleted_at', $columns, true);
        $hasUpdatedAt = \in_array('updated_at', $columns, true);
        $this->hasProvider = false;
        $providerColumn = null;
        $associations = $meta->getAssociationMappings();
        foreach ($associations as $association) {
            if ($association['isOwningSide'] === true && $association['targetEntity'] === $this->providerClass) {
                $this->hasProvider = true;
                break;
            }
        }

        $select = [];
        $tableAlias = $this->getTableAlias($tableName);
        foreach ($properties as $property) {
            $column = $this->findMatch($property, $columns);

            // First char should at least be the same.
            if ($property[0] !== $column[0]) {
                continue;
            }

            $select[] = "$tableAlias.$column";
            $this->columnMapping[$property] = $column;
        }

        if ($hasUpdatedAt === true) {
            $select[] = "$tableAlias.updated_at AS tracking_column\n";
        }

        $query = "SELECT " . \implode(",\n       ", $select) .
            "FROM $tableName AS $tableAlias \n" .
            "WHERE $tableAlias.updated_at > :sql_last_value\n" .
            "AND $tableAlias.updated_at < NOW()\n";

        if ($this->hasDeletedAt === true) {
            $query .= "AND $tableAlias.deleted_at IS NULL";
        }

        $this->writeFile(
            \sprintf('./docker/logstash/pipeline/input/sql-queries/%s.sql', $this->toSnake($indexName)),
            $query
        );

        if ($this->hasDeletedAt === true) {
            $this->writeFile(
                \sprintf('./docker/logstash/pipeline/input/sql-queries/%s_deleted.sql', $this->toSnake($indexName)),
                "SELECT $tableAlias.id,\n$tableAlias.deleted_at AS tracking_column \n" .
                "FROM $tableName AS $tableAlias \n" .
                "WHERE $tableAlias.deleted_at IS NOT NULL \n" .
                "AND $tableAlias.deleted_at > :sql_last_value \n" .
                "AND $tableAlias.deleted_at < NOW()\n"
            );
        }
    }

    private function doMigrate(SearchHandlerInterface $searchHandler): void
    {
        $indexNames = $this->indexNameTransformer->transformIndexNames($searchHandler);
        $inputTemplate = $this->templateInputConfig();
        $inputDeleteTemplate = $this->templateInputDeleteConfig();

        foreach ($indexNames as $indexName) {
            $this->columnMapping = [];
            $this->hasDeletedAt = false;

            $mapping = $this->mappingHelper->buildIndexMappings($searchHandler);
            $this->migrateMappings($searchHandler, $mapping['doc'], $indexName);

            // Build SQL queries
            $esProperties = $mapping['doc']['properties'];

            $this->buildSelectSql($searchHandler, array_keys($esProperties), $indexName);

            $this->buildInputPipeline($esProperties, $indexName, $inputTemplate);

            $this->pipelineConfig .= "\n- path.config: \"/usr/share/logstash/pipeline/input/{$this->toSnake($indexName)}.conf\"\n" .
                "  pipeline.id: \"input_{$this->toSnake($indexName)}\"\n" .
                "  pipeline.workers: 1\n";

            if ($this->hasDeletedAt === true) {
                $inputDeletePipeline = \str_replace(
                    ['[SQL_FILENAME]', '[INDEX_NAME]'],
                    [$this->toSnake($indexName . '_deleted'), $indexName],
                    $inputDeleteTemplate
                );
                $this->writeFile(
                    \sprintf('./docker/logstash/pipeline/input/%s_deleted.conf', $this->toSnake($indexName)),
                    $inputDeletePipeline
                );

                $this->pipelineConfig .= "\n- path.config: \"/usr/share/logstash/pipeline/input/{$this->toSnake($indexName)}_deleted.conf\"\n" .
                    "  pipeline.id: \"input_{$this->toSnake($indexName)}_deleted\"\n" .
                    "  pipeline.workers: 1\n";
            }
        }
    }

    private function findMatch(string $input, array $propertyNames): ?string
    {
        $closest = null;

        // No shortest distance found, yet
        $shortest = -1;

        // loop through words to find the closest
        foreach ($propertyNames as $propertyName) {
            // Calculate the distance between the input word,
            // and the current word
            $lev = \levenshtein($input, $propertyName);

            // Check for an exact match
            if ($lev === 0) {
                // Closest word is this one (exact match)
                $closest = (string)$propertyName;

                // Break out of the loop; we've found an exact match
                break;
            }

            // If this distance is less than the next found shortest
            // Distance, OR if a next shortest word has not yet been found
            if ($lev <= $shortest || $shortest < 0) {
                // Set the closest match, and shortest distance
                $closest = (string)$propertyName;
                $shortest = $lev;
            }
        }

        return $closest;
    }

    private function getEntityClass(SearchHandlerInterface $handler): string
    {
        if (\method_exists('getEntityClass', \get_class($handler)) === true) {
            return $handler->getEntityClass();
        }

        $prop = (new \ReflectionClass(\get_class($handler)))->getParentClass()->getProperty('entityClass');
        $prop->setAccessible(true);

        return $prop->getValue($handler);
    }

    private function getTableAlias(string $tableName): string
    {
        return \implode('', \array_map(function (string $item) {
            return $item[0];
        }, \explode('_', $tableName)));
    }

    /**
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     * @param array $doc
     * @param string $indexName
     */
    private function migrateMappings(SearchHandlerInterface $searchHandler, array $doc, string $indexName): void
    {
        $settings = [
            'mappings' => [
                'doc' => $doc
            ],
            'settings' => $searchHandler::getSettings()
        ];

        $indexCreate = [
            'actions' => [
                "1" => [
                    'action' => 'create_index',
                    'description' => "Create index named $indexName",
                    'options' => [
                        'name' => $indexName,
                        'extra_settings' => $settings
                    ]
                ]
            ]
        ];

        // Save mapping
        $this->writeFile(
            "./docker/logstash/curator/actions/$indexName.yml",
            Yaml::dump($indexCreate, 100)
        );

        $this->curatorConfig .= "curator --config /usr/share/curator/curator.yml /usr/share/curator/actions/$indexName.yml\n";
    }

    private function templateEntryPoint()
    {
        return '#!/usr/bin/env bash

# Load environment variables
# If an SSM path was supplied, retrieve all parameters from it and set as environment variables
if [ -n "${SSM_PATH+x}" ]; then
  echo "Loading Environment Variables..."

  # Display the SSM parameter names
  aws ssm get-parameters-by-path \
    --path "${SSM_PATH}" \
    --with-decryption \
    --region "${AWS_REGION}" | jq -r \'.Parameters[] | "\(.Name|split("/")|.[-1])"\';
    echo

  # Export each ones value as environment variable
  # shellcheck disable=SC2046
  eval $(aws ssm get-parameters-by-path \
    --path "${SSM_PATH}" \
    --with-decryption \
    --region "${AWS_REGION}" | jq -r \'.Parameters[] | "export \(.Name|split("/")|.[-1])=\(.Value | @sh);"\')

else
  echo "WARNING: No \'SSM_PATH\' environment variable was specified, skipping loading of environment variables from SSM parameter store"
fi

# Map environment variables to entries in logstash.yml
echo "Mapping environment variables..."
env2yaml /usr/share/logstash/config/logstash.yml
cat /usr/share/logstash/config/logstash.yml

export LS_JAVA_OPTS="-Dls.cgroup.cpuacct.path.override=/ -Dls.cgroup.cpu.path.override=/ $LS_JAVA_OPTS"
export LS_SETTINGS_DIR=/usr/share/logstash/config

echo "Run curator"
#[ADD CURATOR CONFIG]

echo "Starting service..."
logstash

echo "Exiting"
';
    }

    private function templateInputConfig()
    {
        return '
input {
  jdbc {
    jdbc_driver_library => "/usr/share/java/mysql-connector-java.jar"
    jdbc_driver_class => "com.mysql.jdbc.Driver"
    jdbc_connection_string => "jdbc:${DB_CONNECTION:mysql}://${DB_HOST}:${DB_PORT:3306}/${DB_DATABASE:estore}"
    jdbc_user => "${DB_USERNAME}"
    jdbc_password => "${DB_PASSWORD}"
    jdbc_paging_enabled => true
    jdbc_page_size => 100
    tracking_column => "tracking_column"
    tracking_column_type => "timestamp"
    use_column_value => true
    schedule => "*/30 * * * * *"
    statement_filepath => "/usr/share/logstash/pipeline/input/sql-queries/[SQL_FILENAME].sql"
  }
}

filter {
  mutate {
    remove_field => [ "deleted_at", "tracking_column" ]
    add_field => { "[@metadata][es][_index]" => "[INDEX_NAME]" "[@metadata][es][_action]" => "index" }
    add_field => { "_access_tokens" => ["${ELASTICSEARCH_ADMIN_ACCESS:admin}"] }
    copy => { "id" => "[@metadata][es][_id]" }

    [SQL_FIELDS_TO_ES_PROPS]
  }
}

output {
  pipeline {
    send_to => [router]
  }
}
';
    }

    private function templateInputDeleteConfig()
    {
        return '
input {
  jdbc {
    jdbc_driver_library => "/usr/share/java/mysql-connector-java.jar"
    jdbc_driver_class => "com.mysql.jdbc.Driver"
    jdbc_connection_string => "jdbc:${DB_CONNECTION:mysql}://${DB_HOST}:${DB_PORT:3306}/${DB_DATABASE:estore}"
    jdbc_user => "${DB_USERNAME}"
    jdbc_password => "${DB_PASSWORD}"
    jdbc_paging_enabled => true
    jdbc_page_size => 100
    tracking_column => "tracking_column"
    tracking_column_type => "timestamp"
    use_column_value => true
    schedule => "*/30 * * * * *"
    statement_filepath => "/usr/share/logstash/pipeline/input/sql-queries/[SQL_FILENAME].sql"
  }
}

filter {
  mutate {
    remove_field => [ "tracking_column" ]
    add_field => { "[@metadata][es][_index]" => "[INDEX_NAME]" "[@metadata][es][_action]" => "delete" }
    rename => { "id" => "[@metadata][es][_id]" }
  }
}

output {
  pipeline {
    send_to => [router]
  }
}
';
    }

    private function templatePipeline()
    {
        return '
# Output
- path.config: "/usr/share/logstash/pipeline/output/elasticsearch.conf"
  pipeline.id: "output_es"
  pipeline.workers: 1

- path.config: "/usr/share/logstash/pipeline/output/stdout.conf"
  pipeline.id: "output_stdout"
  pipeline.workers: 1

# Util
- path.config: "/usr/share/logstash/pipeline/util/router.conf"
  pipeline.id: "util_router"
  pipeline.workers: 1
';
    }

    private function toSnake($input): string
    {
        \preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match === \strtoupper($match) ? \strtolower($match) : \lcfirst($match);
        }

        return \implode('_', $ret);
    }

    private function writeFile(string $filename, string $contents, ?bool $logSuccess = null): void
    {
        $logSuccess = $logSuccess ?? true;
        if (\file_exists($filename) === true) {
            $originalHash = \md5(\file_get_contents($filename));
            $newHash = \md5($contents);
            if ($originalHash !== $newHash) {
                $this->output->writeln(\sprintf('Skipped - file already exist and updated `%s`', $filename));
            }
            if ($originalHash === $newHash) {
                $this->output->writeln(\sprintf('Skipped - file already exist with same content `%s`', $filename));
            }

            return;
        }

        \file_put_contents($filename, $contents);
        if ($logSuccess === true) {
            $this->output->writeln(\sprintf('Created `%s`', $filename));
        }
    }
}
