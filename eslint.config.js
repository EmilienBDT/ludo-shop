import js from "@eslint/js";

export default [
  js.configs.recommended,
  {
    languageOptions: {
      ecmaVersion: 2024,
      sourceType: "module",
      globals: {
        fetch: "readonly",
        URL: "readonly",
        FormData: "readonly",
        document: "readonly",
        localStorage: "readonly",
        matchMedia: "readonly",
        console: "readonly",
        requestAnimationFrame: "readonly",
        setTimeout: "readonly",
      },
    },
    rules: {
      "no-unused-vars": ["error", { argsIgnorePattern: "^_" }],
      "no-console": "warn",
    },
  },
  {
    ignores: ["vendor/", "node_modules/", "public/build/"],
  },
];
