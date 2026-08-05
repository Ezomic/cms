import js from '@eslint/js';
import ts from 'typescript-eslint';
import vue from 'eslint-plugin-vue';
import prettier from 'eslint-config-prettier';
import globals from 'globals';

export default ts.config(
    { ignores: ['public/**', 'vendor/**', 'node_modules/**', 'storage/**'] },
    js.configs.recommended,
    ...ts.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        languageOptions: {
            globals: globals.browser,
        },
    },
    {
        files: ['**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: ts.parser,
            },
        },
    },
    {
        rules: {
            // Single-word page components are the Inertia convention here
            // (pages/Dashboard.vue resolves the "Dashboard" page name).
            'vue/multi-word-component-names': 'off',
        },
    },
    {
        // shadcn-vue primitives: optional props are resolved by cva variants
        // rather than Vue defaults, and these files are not customised by hand.
        files: ['resources/js/components/ui/**'],
        rules: {
            'vue/require-default-prop': 'off',
        },
    },
    prettier,
);
