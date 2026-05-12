import pluginVue from 'eslint-plugin-vue'
import tsParser from '@typescript-eslint/parser'
import tsPlugin from '@typescript-eslint/eslint-plugin'
import configPrettier from 'eslint-config-prettier'

export default [
  // Vue 3 recommended (incluye js.recommended + vue/recommended)
  ...pluginVue.configs['flat/recommended'],

  // TypeScript en archivos .ts — el parser se aplica a nivel raíz
  {
    files: ['**/*.ts'],
    plugins: { '@typescript-eslint': tsPlugin },
    languageOptions: {
      parser: tsParser,               // parser directo para .ts
      parserOptions: { sourceType: 'module' },
    },
    rules: {
      ...tsPlugin.configs.recommended.rules,
      '@typescript-eslint/no-explicit-any': 'warn',
    },
  },

  // TypeScript dentro de <script lang="ts"> en archivos .vue
  // vue-eslint-parser (registrado por pluginVue) usa parserOptions.parser
  // para delegar el bloque <script> al parser de TS.
  {
    files: ['**/*.vue'],
    plugins: { '@typescript-eslint': tsPlugin },
    languageOptions: {
      parserOptions: {
        parser: tsParser,             // parser para <script lang="ts">
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
