<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('grumphp');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()->scalarNode('hooks_dir')->defaultNull();
        $rootNode->children()->enumNode('hooks_preset')->cannotBeEmpty()->defaultValue('local')->values([
            'local',
            'vagrant'
         ]);

        // Git hook variables
        $gitHookVariables = $rootNode->children()->arrayNode('git_hook_variables');
        $gitHookVariables->ignoreExtraKeys(false);
        $gitHookVariables->addDefaultsIfNotSet();
        $gitHookVariables->children()->scalarNode('VAGRANT_HOST_DIR')->defaultValue('.');
        $gitHookVariables->children()->scalarNode('VAGRANT_PROJECT_DIR')->defaultValue('/var/www');
        $gitHookVariables->children()->variableNode('EXEC_GRUMPHP_COMMAND')->defaultValue('exec');
        $gitHookVariables->children()->arrayNode('ENV')->scalarPrototype();

        $rootNode->children()->scalarNode('additional_info')->defaultNull();

        // Tasks
        $tasks = $rootNode->children()->arrayNode('tasks');
        $tasks->normalizeKeys(false);
        $tasks->variablePrototype();

        // Testsuites
        $testSuites = $rootNode->children()->arrayNode('testsuites');
        $testSuites->normalizeKeys(false);
        $testSuite = $testSuites->arrayPrototype();
        $testSuite->children()->arrayNode('tasks')->scalarPrototype();

        $rootNode->children()->booleanNode('stop_on_failure')->defaultValue(false);
        $rootNode->children()->booleanNode('ignore_unstaged_changes')->defaultValue(false);
        $rootNode->children()->booleanNode('hide_circumvention_tip')->defaultValue(false);

        // Process timeout (null or float)
        $processTimeout = $rootNode->children()->scalarNode('process_timeout')->defaultValue(60.0);
        $processTimeout->beforeNormalization()->always(static function (?float $value): ?float {
            if (null === $value) {
                return null;
            }

            return (float) ($value > 1 ? $value : 1);
        });

        // extensions
        $rootNode->children()->arrayNode('extensions')->scalarPrototype();

        // ascii
        $ascii = $rootNode->children()->arrayNode('ascii')->addDefaultsIfNotSet();
        $ascii->children()->variableNode('failed')->defaultValue('grumphp-grumpy.txt');
        $ascii->children()->variableNode('succeeded')->defaultValue('grumphp-happy.txt');

        // parallel
        $parallel = $rootNode->children()->arrayNode('parallel');
        $parallel->canBeDisabled();
        $parallel->children()->integerNode('max_workers')->min(1)->defaultValue(32);

        // Fixer:
        $parallel = $rootNode->children()->arrayNode('fixer');
        $parallel->canBeDisabled();
        $parallel->children()->booleanNode('fix_by_default')->defaultFalse();

        // Dotenv
        $env = $rootNode->children()->arrayNode('environment');
        $env->addDefaultsIfNotSet();
        $env->children()->arrayNode('files')->scalarPrototype();
        $env->children()->arrayNode('variables')->scalarPrototype();
        $env->children()->arrayNode('paths')->scalarPrototype();

        return $treeBuilder;
    }
}
