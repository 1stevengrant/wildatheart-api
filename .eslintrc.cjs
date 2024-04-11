module.exports = {
    parser: "@typescript-eslint/parser",
    extends: [
        "eslint:recommended",
        "plugin:@typescript-eslint/recommended",
        "plugin:prettier/recommended"
    ],
    plugins: ["import"],
    parserOptions: {
        ecmaVersion: 2020,
        sourceType: "module",
        ecmaFeatures: {
            jsx: true
        }
    },
    settings: {
        react: {
            version: "detect"
        }
    },
    rules: {
        "@typescript-eslint/no-explicit-any": "off",
        "@typescript-eslint/no-unused-vars": "warn",
        "@typescript-eslint/ban-ts-comment": "warn",
        "prefer-const": "warn",
        "import/order": ["error", {
            "groups": ["builtin", "external", "internal", "parent", "sibling", "index"],
            "pathGroups": [
                {
                    "pattern": "react",
                    "group": "external",
                    "position": "before"
                }
            ],
            "pathGroupsExcludedImportTypes": ["react"],
            "newlines-between": "always",
            "alphabetize": {
                "order": "asc",
                "caseInsensitive": true
            }
        }]
    },
    env: {
        browser: true,
        node: true,
        es6: true
    }
};
