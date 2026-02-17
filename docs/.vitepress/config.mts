import { defineConfig } from 'vitepress'

export default defineConfig({
    title: 'Laravel TypeScript',
    description: 'Generate TypeScript interfaces and types from your Eloquent models and enums',

    head: [
        ['meta', { name: 'theme-color', content: '#6366f1' }],
        ['meta', { name: 'og:type', content: 'website' }],
        ['meta', { name: 'og:title', content: 'Laravel TypeScript' }],
        ['meta', { name: 'og:description', content: 'Generate TypeScript interfaces and types from your Eloquent models and enums.' }],
    ],

    themeConfig: {
        logo: '/logo.svg',

        nav: [
            { text: 'Guide', link: '/guide/introduction' },
            { text: 'Reference', link: '/reference/configuration' },
        ],

        sidebar: {
            '/guide/': [
                {
                    text: 'Getting Started',
                    items: [
                        { text: 'Introduction', link: '/guide/introduction' },
                        { text: 'Installation', link: '/guide/installation' },
                        { text: 'Quick Start', link: '/guide/quick-start' },
                    ],
                },
                {
                    text: 'Core Concepts',
                    items: [
                        { text: 'Model Discovery', link: '/guide/model-discovery' },
                        { text: 'Type Mappings', link: '/guide/type-mappings' },
                        { text: 'Accessors & Mutators', link: '/guide/accessors-mutators' },
                        { text: 'Relations', link: '/guide/relations' },
                        { text: 'Enums', link: '/guide/enums' },
                    ],
                },
                {
                    text: 'Advanced',
                    items: [
                        { text: 'Standalone Types', link: '/guide/standalone-types' },
                        { text: 'API Resources', link: '/guide/api-resources' },
                        { text: 'Auto-generation', link: '/guide/auto-generation' },
                        { text: 'Caching', link: '/guide/caching' },
                        { text: 'Formatting', link: '/guide/formatting' },
                    ],
                },
            ],

            '/reference/': [
                {
                    text: 'Reference',
                    items: [
                        { text: 'Configuration', link: '/reference/configuration' },
                        { text: 'CLI Commands', link: '/reference/cli-commands' },
                    ],
                },
            ],
        },

        socialLinks: [
            { icon: 'github', link: 'https://github.com/frolaxhq/laravel-typescript' },
        ],

        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © 2024-present Frolax',
        },

        search: {
            provider: 'local',
        },
    },
})
