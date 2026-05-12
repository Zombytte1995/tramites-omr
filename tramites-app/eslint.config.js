import pluginVue from 'eslint-plugin-vue'
import tsParser from '@typescript-eslint/parser'
import tsPlugin from '@typescript-eslint/eslint-plugin'
import configPrettier from 'eslint-config-prettier'

export default [
  // Vue 3 recommended (incluye js.recommended + vue/recommended)
  ...pluginVue.configs['flat/recommended'],

  // TypeScript dentro de .ts y .vue
  {
    files: ['**/*.{ts,vue}'],
    plugins: { '@typescript-eslint': tsPlugin },
    languageOptions: {
      parserOptions: {
        parser: tsParser,           // parser para <script lang="ts">
        extraFileExtensions: ['.vue'],
        sourceType: 'module',
      },
    },
    rules: {
      ...tsPlugin.configs.recommended.rules,
      'vue/multi-word-component-names': 'off', // App.vue, etc.
      '@typescript-eslint/no-explicit-any': 'warn',
    },
  },

  // Prettier desactiva reglas que conflictúan con el formateado
  configPrettier,

  {
    ignores: ['dist/**', 'node_modules/**'],
  },
]
