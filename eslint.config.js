import js from '@eslint/js';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import prettierPlugin from 'eslint-plugin-prettier';
import prettierConfig from 'eslint-config-prettier';
import importPlugin from 'eslint-plugin-import';
import reactPlugin from 'eslint-plugin-react';
import globals from 'globals';

export default [
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/build/**',
            'addons/ghijk/wild-at-heart/resources/dist/**',
            'resources/js/cp.js'
        ]
    },
    js.configs.recommended,
    {
        files: ['**/*.{js,jsx,ts,tsx}'],
        languageOptions: {
            parser: tsParser,
            ecmaVersion: 2020,
            sourceType: 'module',
            parserOptions: {
                ecmaFeatures: {
                    jsx: true
                }
            },
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.es2020,
                App: 'readonly',
                route: 'readonly',
                Ziggy: 'readonly',
                _: 'readonly'
            }
        },
        plugins: {
            '@typescript-eslint': tsPlugin,
            'prettier': prettierPlugin,
            'import': importPlugin,
            'react': reactPlugin
        },
        settings: {
            react: {
                version: 'detect'
            }
        },
        rules: {
            ...tsPlugin.configs.recommended.rules,
            ...prettierConfig.rules,
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/no-unused-vars': 'warn',
            '@typescript-eslint/ban-ts-comment': 'warn',
            'prefer-const': 'warn',
            'no-case-declarations': 'off',
            'prettier/prettier': 'error',
            'import/order': ['error', {
                'groups': ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'],
                'pathGroups': [
                    {
                        'pattern': 'react',
                        'group': 'external',
                        'position': 'before'
                    }
                ],
                'pathGroupsExcludedImportTypes': ['react'],
                'newlines-between': 'always',
                'alphabetize': {
                    'order': 'asc',
                    'caseInsensitive': true
                }
            }]
        }
    }
];