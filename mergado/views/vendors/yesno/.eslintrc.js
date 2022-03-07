module.exports = {
  env: {
    browser: true,
    es2020: true,
    jest: true,
  },
  extends: "eslint:recommended",
  parserOptions: {
    ecmaVersion: 11,
    sourceType: "module",
  },
  plugins: ["jest"],
  rules: {},
};
