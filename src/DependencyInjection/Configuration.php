<?php

declare(strict_types=1);

/*
 * This file is part of the AI Development Assistant Bundle.
 *
 * (c) Aria Vahidi <aria.vahidi2020@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aria1991\AIDevAssistantBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * AI Development Assistant Bundle configuration.
 *
 * @author Aria Vahidi <aria.vahidi2020@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ai_dev_assistant');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enable or disable the AI Development Assistant Bundle')
                ->end()
                ->arrayNode('ai')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('providers')
                            ->addDefaultsIfNotSet()
                            ->info('Configuration for AI providers')
                            ->children()
                                ->arrayNode('openai')
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('api_key')
                                            ->defaultValue('%env(default::OPENAI_API_KEY)%')
                                            ->info('OpenAI API key')
                                        ->end()
                                        ->scalarNode('model')
                                            ->defaultValue('gpt-4')
                                            ->info('OpenAI model to use')
                                        ->end()
                                        ->integerNode('max_tokens')
                                            ->defaultValue(4000)
                                            ->info('Maximum tokens for OpenAI requests')
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('anthropic')
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('api_key')
                                            ->defaultValue('%env(default::ANTHROPIC_API_KEY)%')
                                            ->info('Anthropic API key')
                                        ->end()
                                        ->scalarNode('model')
                                            ->defaultValue('claude-3-sonnet-20240229')
                                            ->info('Anthropic model to use')
                                        ->end()
                                        ->integerNode('max_tokens')
                                            ->defaultValue(4000)
                                            ->info('Maximum tokens for Anthropic requests')
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('google')
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('api_key')
                                            ->defaultValue('%env(default::GOOGLE_AI_API_KEY)%')
                                            ->info('Google AI API key')
                                        ->end()
                                        ->scalarNode('model')
                                            ->defaultValue('gemini-pro')
                                            ->info('Google AI model to use')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('analysis')
                    ->addDefaultsIfNotSet()
                    ->info('Code analysis configuration')
                    ->children()
                        ->arrayNode('enabled_analyzers')
                            ->prototype('scalar')->end()
                            ->defaultValue(['security', 'performance', 'quality', 'documentation'])
                            ->info('List of enabled analyzers')
                        ->end()
                        ->integerNode('max_file_size')
                            ->defaultValue(1048576) // 1MB
                            ->info('Maximum file size to analyze in bytes')
                        ->end()
                        ->arrayNode('excluded_paths')
                            ->prototype('scalar')->end()
                            ->defaultValue(['vendor/', 'var/cache/', 'var/log/', 'node_modules/', 'public/build/'])
                            ->info('Paths to exclude from analysis')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable caching of analysis results')
                        ->end()
                        ->integerNode('ttl')
                            ->defaultValue(3600)
                            ->info('Cache TTL in seconds')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('rate_limiting')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('requests_per_minute')
                            ->defaultValue(60)
                            ->info('Maximum requests per minute per user')
                        ->end()
                        ->integerNode('requests_per_hour')
                            ->defaultValue(1000)
                            ->info('Maximum requests per hour per user')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
